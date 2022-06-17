<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Request\ArgumentResolver;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use LogicException;
use Pfilsx\DtoParamConverter\Annotation\Dto;
use Pfilsx\DtoParamConverter\Configuration\Configuration;
use Pfilsx\DtoParamConverter\Contract\ValidationExceptionInterface;
use Pfilsx\DtoParamConverter\Factory\DtoMapperFactory;
use Pfilsx\DtoParamConverter\Provider\DtoMetadataProvider;
use Pfilsx\DtoParamConverter\Provider\RouteMetadataProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DtoArgumentResolver implements ArgumentValueResolverInterface
{
    public const OPTION_SERIALIZER_CONTEXT = 'serializerContext';

    public const OPTION_VALIDATOR_GROUPS = 'validatorGroups';

    public const OPTION_PRELOAD_ENTITY = 'preloadEntity';

    public const OPTION_STRICT_PRELOAD_ENTITY = 'strictPreloadEntity';

    public const OPTION_ENTITY_ID_ATTRIBUTE = 'entityIdAttribute';

    public const OPTION_ENTITY_MANAGER = 'entityManager';

    public const OPTION_ENTITY_MAPPING = 'entityMapping';

    public const OPTION_ENTITY_EXPR = 'entityExpr';

    public const OPTION_VALIDATE = 'validate';

    private Configuration $configuration;

    private SerializerInterface $serializer;

    private DtoMetadataProvider $dtoMetadataProvider;

    private RouteMetadataProvider $routeMetadataProvider;

    private DtoMapperFactory $mapperFactory;

    private ?ValidatorInterface $validator;

    private ?ManagerRegistry $registry;

    private ?ExpressionLanguage $language;

    private ?TokenStorageInterface $tokenStorage;

    private array $options;

    public function __construct(
        Configuration $configuration,
        SerializerInterface $serializer,
        DtoMetadataProvider $dtoMetadataProvider,
        RouteMetadataProvider $routeMetadataProvider,
        DtoMapperFactory $mapperFactory,
        ?ValidatorInterface $validator = null,
        ?ManagerRegistry $registry = null,
        ?ExpressionLanguage $expressionLanguage = null,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->configuration = $configuration;
        $this->serializer = $serializer;
        $this->dtoMetadataProvider = $dtoMetadataProvider;
        $this->mapperFactory = $mapperFactory;
        $this->validator = $validator;
        $this->registry = $registry;
        $this->language = $expressionLanguage;
        $this->tokenStorage = $tokenStorage;
        $this->routeMetadataProvider = $routeMetadataProvider;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!\is_string($type = $argument->getType())) {
            return false;
        }

        return $this->getClassDtoAnnotation($type) instanceof Dto;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $name = $argument->getName();
        $className = $argument->getType();

        $this->options = array_replace([
            self::OPTION_ENTITY_MANAGER => $this->configuration->getPreloadConfiguration()->getManagerName(),
            self::OPTION_STRICT_PRELOAD_ENTITY => !$this->configuration->getPreloadConfiguration()->isOptional(),
        ], $this->getRouteOptions($request->attributes->get('_route'), $name));

        $content = $this->getRequestContent($request);

        try {
            if (empty($content)) {
                $object = $this->isPreloadDtoRequired($className, $request)
                    ? $this->createPreloadedDto($name, $className, $request)
                    : new $className();
            } elseif (\is_string($content)) {
                $object = $this->serializer->deserialize(
                    $content,
                    $className,
                    $request->getContentType() ?? $request->getFormat('application/json'),
                    $this->getSerializerContext($name, $className, $request)
                );
            } else {
                $object = $this->serializer->denormalize(
                    $content,
                    $className,
                    null,
                    $this->getSerializerContext($name, $className, $request)
                );
            }
        } catch (PartialDenormalizationException $e) {
            $violations = new ConstraintViolationList();

            foreach ($e->getErrors() as $exception) {
                $message = sprintf('The type must be one of "%s" ("%s" given).', implode(', ', $exception->getExpectedTypes()), $exception->getCurrentType());
                $parameters = [];
                if ($exception->canUseMessageForUser()) {
                    $parameters['hint'] = $exception->getMessage();
                }
                $violations->add(new ConstraintViolation($message, '', $parameters, null, $exception->getPath(), null));
            }

            throw $this->generateValidationException($violations);
        } catch (NotNormalizableValueException $exception) {
            $exceptionClass = $this->configuration->getSerializerConfiguration()->getNormalizerExceptionClass();

            throw new $exceptionClass($exception->getMessage(), 400, $exception);
        }

        if ($this->isValidationRequired($className, $request)) {
            $violations = $this->validator->validate(
                $object,
                null,
                $this->getOption(self::OPTION_VALIDATOR_GROUPS, ['Default', $request->attributes->get('_route')])
            );

            if ($violations->count() !== 0) {
                throw $this->generateValidationException($violations);
            }
        }

        yield $object;
    }

    /**
     * @param Request $request
     *
     * @return null|array|false|resource|string
     */
    private function getRequestContent(Request $request)
    {
        switch ($request->getMethod()) {
            case Request::METHOD_PUT:
            case Request::METHOD_POST:
            case Request::METHOD_DELETE:
            case Request::METHOD_PATCH:
                return $request->getContent();
            case Request::METHOD_GET:
                return array_merge($request->query->all(), $request->attributes->all());
            default:
                return [];
        }
    }

    private function getSerializerContext(string $name, string $className, Request $request): array
    {
        $strictTypesConfiguration = $this->configuration->getSerializerConfiguration()->getStrictTypesConfiguration();

        $context = [
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => !$strictTypesConfiguration->isEnabled()
                || \in_array($request->getMethod(), $strictTypesConfiguration->getExcludedMethods(), true),
        ];

        $context = array_replace($context, $this->getOption(self::OPTION_SERIALIZER_CONTEXT, []));
        if ($this->isPreloadDtoRequired($className, $request)) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $this->createPreloadedDto($name, $className, $request);
        }
        if (\defined(DenormalizerInterface::class . '::COLLECT_DENORMALIZATION_ERRORS')) {
            $context[DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS] = true;
        }

        return $context;
    }

    private function isPreloadDtoRequired(string $className, Request $request): bool
    {
        if ($this->registry === null) {
            return false;
        }

        $routeOption = $this->getOption(self::OPTION_PRELOAD_ENTITY);

        if ($routeOption === false) {
            return false;
        }

        $annotation = $this->getClassDtoAnnotation($className);

        if (!$annotation instanceof Dto || empty($annotation->getLinkedEntity())) {
            return false;
        }

        if (\is_bool($routeOption)) {
            return $routeOption;
        }

        if ($annotation->isPreload() !== null) {
            return $annotation->isPreload();
        }

        $preloadConfiguration = $this->configuration->getPreloadConfiguration();

        return $preloadConfiguration->isEnabled()
            && \in_array($request->getMethod(), $preloadConfiguration->getMethods(), true);
    }

    private function createPreloadedDto(string $name, string $className, Request $request): object
    {
        $dto = new $className();

        $entity = $this->findEntity($name, $className, $request);

        if ($entity === null && $this->getOption(self::OPTION_STRICT_PRELOAD_ENTITY, true)) {
            throw new NotFoundHttpException("Entity for preloading \${$name} not found by the DtoParamConverter.");
        } elseif ($entity !== null) {
            $mapper = $this->mapperFactory->getMapper($className);

            $mapper->mapToDto($entity, $dto);
        }

        return $dto;
    }

    private function findEntity(string $name, string $className, Request $request): ?object
    {
        if (!empty($expr = $this->getOption(self::OPTION_ENTITY_EXPR))) {
            return $this->findEntityViaExpression($className, $request, $expr);
        } elseif (!empty($mapping = $this->getOption(self::OPTION_ENTITY_MAPPING))) {
            return $this->findEntityByMapping($className, $request, $mapping);
        } else {
            $identifierValue = $this->getIdentifierValue($className, $name, $request);

            if ($identifierValue !== false) {
                $repository = $this->getManager($className)
                    ->getRepository($this->getEntityClassForDto($className));

                return $repository->find($identifierValue);
            }
            $keys = $request->attributes->keys();
            $mapping = $keys ? array_combine($keys, $keys) : [];

            return $this->findEntityByMapping($className, $request, $mapping);
        }
    }

    private function findEntityViaExpression(string $className, Request $request, string $expression): ?object
    {
        if ($this->language === null) {
            throw new LogicException('To use the @DtoResolver tag with the "entityExpr" option, you need to install the ExpressionLanguage component.');
        }
        $variables = array_merge($request->attributes->all(), [
            'repository' => $this->getManager($className)
                ->getRepository($this->getEntityClassForDto($className)),
            'user' => $this->getUser(),
        ]);

        try {
            return $this->language->evaluate($expression, $variables);
        } catch (NoResultException $e) {
            return null;
        } catch (ConversionException $e) {
            return null;
        } catch (SyntaxError $e) {
            throw new LogicException(sprintf('Error parsing expression -- "%s" -- (%s).', $expression, $e->getMessage()), 0, $e);
        }
    }

    private function findEntityByMapping(string $className, Request $request, array $mapping): ?object
    {
        $criteria = [];
        $em = $this->getManager($className);
        $entityClassName = $this->getEntityClassForDto($className);
        $metadata = $em->getClassMetadata($entityClassName);

        foreach ($mapping as $attribute => $field) {
            if (
                $metadata->hasField($field)
                || ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field))
            ) {
                $criteria[$field] = $request->attributes->get($attribute);
            }
        }
        if (empty($criteria)) {
            return null;
        }

        return $em->getRepository($entityClassName)->findOneBy($criteria);
    }

    private function getEntityClassForDto(string $dtoClassName): string
    {
        $annotation = $this->getClassDtoAnnotation($dtoClassName);

        if (
            $annotation instanceof Dto
            && !empty(($entityClass = $annotation->getLinkedEntity()))
            && class_exists($entityClass)) {
            return $entityClass;
        }

        throw new LogicException("Unable to find entity class for {$dtoClassName}");
    }

    /**
     * @param string  $className
     * @param string  $name
     * @param Request $request
     *
     * @return false|mixed
     */
    private function getIdentifierValue(string $className, string $name, Request $request)
    {
        $routeAttributes = $request->attributes->get('_route_params', []);

        if ($this->getOption(self::OPTION_ENTITY_ID_ATTRIBUTE) !== null) {
            $attributeName = $this->getOption(self::OPTION_ENTITY_ID_ATTRIBUTE);
        } elseif (\count($routeAttributes) === 1) {
            $attributeName = array_key_first($routeAttributes);

            $em = $this->getManager($className);
            $entityClassName = $this->getEntityClassForDto($className);
            $metadata = $em->getClassMetadata($entityClassName);
            if (
                (!$metadata->isIdentifier($attributeName) && $metadata->hasField($attributeName))
                || ($metadata->hasAssociation($attributeName) && $metadata->isSingleValuedAssociation($attributeName))
            ) {
                return false;
            }
        } else {
            $attributeName = $name;
            if (mb_strtolower(mb_substr($attributeName, -3)) === 'dto') {
                $attributeName = mb_substr($attributeName, 0, -3);
            }
        }
        if (\array_key_exists($attributeName, $routeAttributes)) {
            return $routeAttributes[$attributeName];
        }
        if ($request->attributes->has('id') && !$this->getOption(self::OPTION_ENTITY_ID_ATTRIBUTE)) {
            return $request->attributes->get('id');
        }

        return false;
    }

    private function getManager(string $className): ?ObjectManager
    {
        $name = $this->getOption(self::OPTION_ENTITY_MANAGER);
        if ($name === null) {
            return $this->registry->getManagerForClass($this->getEntityClassForDto($className));
        }

        return $this->registry->getManager($name);
    }

    private function getClassDtoAnnotation(string $className): ?Dto
    {
        return $this->dtoMetadataProvider->getDtoMetadata($className);
    }

    private function getUser(): ?UserInterface
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface) {
            return null;
        }

        if (($token = $this->tokenStorage->getToken()) === null) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    private function isValidationRequired(string $className, Request $request): bool
    {
        if (!$this->validator instanceof ValidatorInterface) {
            return false;
        }

        $routeOption = $this->getOption(self::OPTION_VALIDATE);

        if ($routeOption === false) {
            return false;
        }

        $annotation = $this->getClassDtoAnnotation($className);

        if (!$annotation instanceof Dto) {
            return false;
        }

        if (\is_bool($routeOption)) {
            return $routeOption;
        }

        if ($annotation->isValidate() !== null) {
            return $annotation->isValidate();
        }

        $validationConfiguration = $this->configuration->getValidationConfiguration();

        return $validationConfiguration->isEnabled() && !\in_array($request->getMethod(), $validationConfiguration->getExcludedMethods(), true);
    }

    private function generateValidationException(ConstraintViolationList $violations): ValidationExceptionInterface
    {
        $exceptionClass = $this->configuration->getValidationConfiguration()->getExceptionClass();
        $exception = new $exceptionClass();
        $exception->setViolations($violations);

        throw $exception;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        return \array_key_exists($key, $this->options)
            ? $this->options[$key]
            : $default
            ;
    }

    private function getRouteOptions(?string $routeName, string $dtoName): array
    {
        $routeMetadata = $this->routeMetadataProvider->getMetadata((string) $routeName);

        return $routeMetadata[$dtoName] ?? $routeMetadata['_default'] ?? [];
    }
}
