<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Request\ParamConverter;


use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use LogicException;
use Pfilsx\DtoParamConverter\Annotation\Dto;
use Pfilsx\DtoParamConverter\Exception\ConverterValidationException;
use Pfilsx\DtoParamConverter\Factory\DtoMapperFactory;
use ReflectionClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DtoParamConverter implements ParamConverterInterface
{
    public const OPTION_SERIALIZER_CONTEXT = 'serializerContext';

    public const OPTION_VALIDATOR_GROUPS = 'validatorGroups';

    public const OPTION_PRELOAD_ENTITY = 'preloadEntity';

    public const OPTION_STRICT_PRELOAD_ENTITY = 'strictPreloadEntity';

    public const OPTION_ENTITY_ATTRIBUTE = 'entityAttribute';

    public const OPTION_ENTITY_MANAGER = 'entityManager';

    public const OPTION_ENTITY_MAPPING = 'entityMapping';

    public const OPTION_ENTITY_EXPR = 'entityExpr';

    public const OPTION_FORCE_VALIDATE = 'forceValidate';

    private SerializerInterface $serializer;

    private Reader $reader;

    private DtoMapperFactory $mapperFactory;

    private ?ValidatorInterface $validator;

    private ?ManagerRegistry $registry;

    private ?ExpressionLanguage $language;

    private ?TokenStorageInterface $tokenStorage;

    private array $defaultOptions = [
        self::OPTION_SERIALIZER_CONTEXT => [],
        self::OPTION_VALIDATOR_GROUPS => null,
        self::OPTION_PRELOAD_ENTITY => true,
        self::OPTION_STRICT_PRELOAD_ENTITY => true,
        self::OPTION_ENTITY_ATTRIBUTE => null,
        self::OPTION_ENTITY_MANAGER => null,
        self::OPTION_ENTITY_MAPPING => [],
        self::OPTION_ENTITY_EXPR => null,
        self::OPTION_FORCE_VALIDATE => false,
    ];

    public function __construct(
        SerializerInterface $serializer,
        Reader $reader,
        DtoMapperFactory $mapperFactory,
        ?ValidatorInterface $validator = null,
        ?ManagerRegistry $registry = null,
        ?ExpressionLanguage $expressionLanguage = null,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->serializer = $serializer;
        $this->reader = $reader;
        $this->mapperFactory = $mapperFactory;
        $this->validator = $validator;
        $this->registry = $registry;
        $this->language = $expressionLanguage;
        $this->tokenStorage = $tokenStorage;
    }

    public function supports(ParamConverter $configuration): bool
    {
        if (!is_string($configuration->getClass())) {
            return false;
        }

        return $this->getClassDtoAnnotation($configuration->getClass()) instanceof Dto;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();
        $className = $configuration->getClass();
        $options = array_replace($this->defaultOptions, $configuration->getOptions());

        $content = $this->getRequestContent($request);

        if (empty($content)) {
            $object = new $className();
        } elseif (is_string($content)) {
            $object = $this->serializer->deserialize(
                $content,
                $className,
                $request->getContentType() ?? $request->getFormat('application/json'),
                $this->getSerializerContext($name, $className, $options, $request)
            );
        } else {
            $object = $this->serializer->denormalize(
                $content,
                $className,
                null,
                $this->getSerializerContext($name, $className, $options, $request)
            );
        }

        if (
            $this->validator instanceof ValidatorInterface
            && ($options[self::OPTION_FORCE_VALIDATE] || $request->getMethod() !== Request::METHOD_GET)
        ) {
            $errors = $this->validator->validate($object, null, $options[self::OPTION_VALIDATOR_GROUPS] ?? null);

            if ($errors->count() !== 0) {
                throw new ConverterValidationException($errors);
            }
        }

        $request->attributes->set($name, $object);

        return true;
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

    private function getSerializerContext(string $name, string $className, array $options, Request $request): array
    {
        $context = $options[self::OPTION_SERIALIZER_CONTEXT] ?? [];
        if ($this->isPreloadDtoRequired($className, $options, $request)) {
            $context = array_merge($context, [
                AbstractNormalizer::OBJECT_TO_POPULATE => $this->createPreloadedDto($name, $className, $options, $request),
            ]);
        }

        return $context;
    }

    private function isPreloadDtoRequired(string $className, array $options, Request $request): bool
    {
        if (!in_array($request->getMethod(), [Request::METHOD_PATCH, Request::METHOD_GET], true)) {
            return false;
        }
        if ($this->registry === null) {
            return false;
        }
        if ($options[self::OPTION_PRELOAD_ENTITY] === false) {
            return false;
        }
        $annotation = $this->getClassDtoAnnotation($className);

        return $annotation instanceof Dto && !empty($annotation->linkedEntity);
    }

    private function createPreloadedDto(string $name, string $className, array $options, Request $request): object
    {
        $dto = new $className();

        $entity = $this->findEntity($name, $className, $options, $request);

        if ($entity === null && $options[self::OPTION_STRICT_PRELOAD_ENTITY]) {
            throw new NotFoundHttpException("Entity for preloading \${$name} not found by the DtoParamConverter.");
        } elseif ($entity !== null) {
            $mapper = $this->mapperFactory->getMapper($className);

            $mapper->mapToDto($entity, $dto);
        }

        return $dto;
    }

    private function findEntity(string $name, string $className, array $options, Request $request): ?object
    {
        if (!empty($expr = $options[self::OPTION_ENTITY_EXPR])) {
            return $this->findEntityViaExpression($className, $request, $expr, $options);
        } elseif (!empty($mapping = $options[self::OPTION_ENTITY_MAPPING])) {
            return $this->findEntityByMapping($className, $request, $mapping, $options);
        } else {
            $repository = $this->getManager($options[self::OPTION_ENTITY_MANAGER], $className)
                ->getRepository($this->getEntityClassForDto($className));
            $identifierValue = $this->getIdentifierValue($name, $options, $request);

            return $repository->find($identifierValue);
        }
    }

    private function findEntityViaExpression(string $className, Request $request, string $expression, array $options): ?object
    {
        if ($this->language === null) {
            throw new LogicException(sprintf('To use the @ParamConverter tag with the "expr" option, you need to install the ExpressionLanguage component.'));
        }
        $variables = array_merge($request->attributes->all(), [
            'repository' => $this->getManager($options[self::OPTION_ENTITY_MANAGER], $className)
                ->getRepository($this->getEntityClassForDto($className)),
            'user' => $this->getUser()
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

    private function findEntityByMapping(string $className, Request $request, array $mapping, array $options): ?object
    {
        $criteria = [];
        $em = $this->getManager($options[self::OPTION_ENTITY_MANAGER], $className);
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
            && !empty(($entityClass = $annotation->linkedEntity))
            && class_exists($entityClass)) {
            return $entityClass;
        }

        throw new LogicException("Unable to find entity class for {$dtoClassName}");
    }

    private function getIdentifierValue(string $name, array $options, Request $request)
    {
        $routeAttributes = $request->attributes->get('_route_params', []);

        if ($options[self::OPTION_ENTITY_ATTRIBUTE] !== null) {
            $attributeName = $options[self::OPTION_ENTITY_ATTRIBUTE];
        } elseif (count($routeAttributes) === 1) {
            $attributeName = array_key_first($routeAttributes);
        } else {
            $attributeName = $name;
            if (mb_strtolower(mb_substr($attributeName, -3)) === 'dto') {
                $attributeName = mb_substr($attributeName, 0, -3);
            }
        }
        if (array_key_exists($attributeName, $routeAttributes)) {
            return $routeAttributes[$attributeName];
        }

        throw new LogicException("Unable to guess how to get attribute from the request information for parameter \${$name}.");
    }

    private function getManager(?string $name, string $className): ?ObjectManager
    {
        if ($name === null) {
            return $this->registry->getManagerForClass($this->getEntityClassForDto($className));
        }

        return $this->registry->getManager($name);
    }

    private function getClassDtoAnnotation(string $className): ?Dto
    {
        $refClass = new ReflectionClass($className);

        return $this->reader->getClassAnnotation($refClass, Dto::class);
    }

    private function getUser(): ?UserInterface
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface)
        {
            return null;
        }

        if (($token = $this->tokenStorage->getToken()) === null) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }
        return $user;
    }
}