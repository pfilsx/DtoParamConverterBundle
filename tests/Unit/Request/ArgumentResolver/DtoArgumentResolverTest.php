<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Unit\Request\ArgumentResolver;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Pfilsx\DtoParamConverter\Configuration\Configuration;
use Pfilsx\DtoParamConverter\Exception\ConverterValidationException;
use Pfilsx\DtoParamConverter\Factory\DtoMapperFactory;
use Pfilsx\DtoParamConverter\Provider\RouteMetadataProvider;
use Pfilsx\DtoParamConverter\Request\ArgumentResolver\DtoArgumentResolver;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Dto\TestDto;
use Pfilsx\DtoParamConverter\Tests\Fixtures\DtoMapper\TestDtoMapper;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Entity\TestEntity;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Repository\TestEntityRepository;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Validation;

final class DtoArgumentResolverTest extends TestCase
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var ExpressionLanguage
     */
    private $language;
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    private DtoArgumentResolver $resolver;

    protected function setUp(): void
    {
        $this->registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();
        $this->language = $this->getMockBuilder(ExpressionLanguage::class)->getMock();
        $this->serviceLocator = $this->getMockBuilder(ServiceLocator::class)->disableOriginalConstructor()->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
    }

    /**
     * @dataProvider providerTestSupports
     *
     * @param mixed $className
     * @param bool  $expectedResult
     */
    public function testSupports($className, bool $expectedResult): void
    {
        $this->initializeResolver();
        self::assertSame($expectedResult, $this->resolver->supports(new Request(), $this->createArgumentMetadataMock($className)));
    }

    public function providerTestSupports(): array
    {
        return [
            'with annotation' => [TestDto::class, true],
            'without annotation' => [\stdClass::class, false],
            'not a classname' => ['', false],
            'not a string' => [null, false],
        ];
    }

    /**
     * @dataProvider providerTestApplyOnGetWithPreloadById
     */
    public function testApplyOnGetWithPreloadById(array $routeParams, array $converterOptions): void
    {
        $this->configureServiceLocatorMock(TestDto::class, new TestDtoMapper());
        $this->configureManagerRegistryMock(TestEntity::class, new TestEntityRepository());

        $request = $this->createRequest(Request::METHOD_GET);
        $request->attributes->set('_route_params', $routeParams);
        foreach ($routeParams as $key => $value) {
            $request->attributes->set($key, $value);
        }

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class, 'testDto');

        $this->initializeResolver(null, $converterOptions);

        $dto = $this->resolver->resolve($request, $argumentMetadata)->current();

        self::assertInstanceOf(TestDto::class, $dto);

        self::assertEquals(['title' => 'Test1', 'value' => 10], ['title' => $dto->title, 'value' => $dto->value]);
    }

    public function providerTestApplyOnGetWithPreloadById(): array
    {
        return [
            'id attribute' => [['id' => 1, 'foo' => 'bar'], []],
            'custom id attribute' => [['id' => 15, 'entityId' => 1], ['entityIdAttribute' => 'entityId']],
            'id attribute by arg name' => [['id' => 15, 'test' => 1], []],
        ];
    }

    /**
     * @dataProvider providerTestApplyOnGetWithPreloadByMapping
     */
    public function testApplyOnGetWithPreloadByMapping(array $routeParams, array $converterOptions): void
    {
        $this->configureServiceLocatorMock(TestDto::class, new TestDtoMapper());
        $this->configureManagerRegistryMock(TestEntity::class, new TestEntityRepository(), ['id', 'title', 'value']);

        $request = $this->createRequest(Request::METHOD_GET);
        $request->attributes->set('_route_params', $routeParams);
        foreach ($routeParams as $key => $value) {
            $request->attributes->set($key, $value);
        }

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver(null, $converterOptions);

        $dto = $this->resolver->resolve($request, $argumentMetadata)->current();

        self::assertInstanceOf(TestDto::class, $dto);

        self::assertEquals(['title' => 'Test2', 'value' => 20], ['title' => $dto->title, 'value' => $dto->value]);
    }

    public function providerTestApplyOnGetWithPreloadByMapping(): array
    {
        return [
            'default mapping' => [['title' => 'Test2'], []],
            'custom mapping' => [['custom' => 'Test2'], ['entityMapping' => ['custom' => 'title']]],
        ];
    }

    public function testApplyOnGetWithPreloadByExpression(): void
    {
        $this->configureServiceLocatorMock(TestDto::class, new TestDtoMapper());
        $repository = new TestEntityRepository();
        $this->configureManagerRegistryMock(TestEntity::class, $repository, ['id', 'title', 'value']);

        $request = $this->createRequest(Request::METHOD_GET);
        $request->attributes->set('_route_params', ['id' => 1]);
        $request->attributes->set('id', 1);

        $this->language->expects($this->once())
            ->method('evaluate')
            ->with('repository.findOneByCustomMethod(id)', [
                'repository' => $repository,
                'id' => 1,
                '_route' => 'test_route',
                '_route_params' => ['id' => 1],
                'user' => null,
            ])
            ->willReturn($repository->find(1))
        ;

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver(null, ['entityExpr' => 'repository.findOneByCustomMethod(id)']);

        $dto = $this->resolver->resolve($request, $argumentMetadata)->current();

        self::assertInstanceOf(TestDto::class, $dto);

        self::assertEquals(['title' => 'Test1', 'value' => 10], ['title' => $dto->title, 'value' => $dto->value]);
    }

    public function testApplyOnExpressionThrowsSyntaxException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('syntax error message around position 10');

        $this->configureManagerRegistryMock(TestEntity::class, new TestEntityRepository(), ['id', 'title', 'value']);

        $request = $this->createRequest(Request::METHOD_GET);
        $request->attributes->set('_route_params', ['id' => 1]);
        $request->attributes->set('id', 1);

        $this->language->expects($this->once())
            ->method('evaluate')
            ->will($this->throwException(new SyntaxError('syntax error message', 10)));

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver(null, ['entityExpr' => 'repository.findOneByCustomMethod(id)']);

        $this->resolver->resolve($request, $argumentMetadata)->current();
    }

    /**
     * @dataProvider providerTestApplyOnExpressionFailure
     */
    public function testApplyOnExpressionFailure(Stub $stub): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->configureManagerRegistryMock(TestEntity::class, new TestEntityRepository(), ['id', 'title', 'value']);

        $request = $this->createRequest(Request::METHOD_GET);
        $request->attributes->set('_route_params', ['id' => 1]);
        $request->attributes->set('id', 1);

        $this->language->expects($this->once())
            ->method('evaluate')
            ->will($stub);

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver(null, ['entityExpr' => 'repository.findOneByCustomMethod(id)']);

        $this->resolver->resolve($request, $argumentMetadata)->current();
    }

    public function providerTestApplyOnExpressionFailure(): array
    {
        return [
            'nothing found' => [$this->returnValue(null)],
            'NoResultException' => [$this->throwException(new NoResultException())],
            'ConversionException ' => [$this->throwException(new ConversionException())],
        ];
    }

    public function testApplyOnGetWithForcedValidation(): void
    {
        self::expectException(ConverterValidationException::class);

        $request = $this->createRequest(Request::METHOD_GET, ['title' => 'Test', 'value' => 5]);

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver(null, [DtoArgumentResolver::OPTION_PRELOAD_ENTITY => false, DtoArgumentResolver::OPTION_VALIDATE => true]);

        $this->resolver->resolve($request, $argumentMetadata)->current();
    }

    public function testApplyOnPost(): void
    {
        $request = $this->createRequest(Request::METHOD_POST, ['title' => 'Test', 'value' => 20]);

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver();

        $dto = $this->resolver->resolve($request, $argumentMetadata)->current();

        self::assertInstanceOf(TestDto::class, $dto);

        self::assertEquals(['title' => 'Test', 'value' => 20], ['title' => $dto->title, 'value' => $dto->value]);
    }

    public function testApplyOnPostWithDisabledValidation(): void
    {
        $request = $this->createRequest(Request::METHOD_POST, ['title' => '', 'value' => 5]);

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver(null, [DtoArgumentResolver::OPTION_VALIDATE => false]);

        $dto = $this->resolver->resolve($request, $argumentMetadata)->current();

        self::assertInstanceOf(TestDto::class, $dto);

        self::assertEquals(['title' => '', 'value' => 5], ['title' => $dto->title, 'value' => $dto->value]);
    }

    /**
     * @dataProvider providerTestApplyOnPostWithInvalidData
     *
     * @param array $requestData
     */
    public function testApplyOnPostWithInvalidData(array $requestData): void
    {
        self::expectException(ConverterValidationException::class);

        $request = $this->createRequest(Request::METHOD_POST, $requestData);

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver();

        $this->resolver->resolve($request, $argumentMetadata)->current();
    }

    public function providerTestApplyOnPostWithInvalidData(): array
    {
        return [
            'wrong type' => [['title' => 'Test', 'value' => 'test']],
            'similar type' => [['title' => 'Test', 'value' => '20']],
            'invalid value' => [['title' => '', 'value' => 5]],
        ];
    }

    public function testApplyOnPostWithStrictTypesDisabled(): void
    {
        $request = $this->createRequest(Request::METHOD_POST, ['title' => 'Test', 'value' => '20']);

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver(new Configuration(
            ['enabled' => true, 'methods' => ['GET', 'PATCH', 'OPTIONS']],
            ['strict_types' => ['enabled' => false]],
            ['enabled' => true, 'exception_class' => ConverterValidationException::class],
        ));

        $dto = $this->resolver->resolve($request, $argumentMetadata)->current();

        self::assertInstanceOf(TestDto::class, $dto);

        self::assertEquals(['title' => 'Test', 'value' => 20], ['title' => $dto->title, 'value' => $dto->value]);
    }

    /**
     * @dataProvider providerTestApplyOnPatchWithPreloadById
     */
    public function testApplyOnPatchWithPreloadById(array $payload, array $expectedResult): void
    {
        $this->configureServiceLocatorMock(TestDto::class, new TestDtoMapper());
        $this->configureManagerRegistryMock(TestEntity::class, new TestEntityRepository());

        $request = $this->createRequest(Request::METHOD_PATCH, $payload);
        $request->attributes->set('_route_params', ['id' => 1]);
        $request->attributes->set('id', 1);

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver();

        $dto = $this->resolver->resolve($request, $argumentMetadata)->current();

        self::assertInstanceOf(TestDto::class, $dto);

        self::assertEquals($expectedResult, ['title' => $dto->title, 'value' => $dto->value]);
    }

    public function providerTestApplyOnPatchWithPreloadById(): array
    {
        return [
            'with request' => [['title' => 'Patched'], ['title' => 'Patched', 'value' => 10]],
            'empty payload' => [[], ['title' => 'Test1', 'value' => 10]],
        ];
    }

    public function testApplyOnDifferentRequestMethodWithPreload(): void
    {
        $this->configureServiceLocatorMock(TestDto::class, new TestDtoMapper());
        $this->configureManagerRegistryMock(TestEntity::class, new TestEntityRepository());

        $request = $this->createRequest(Request::METHOD_OPTIONS);
        $request->attributes->set('_route_params', ['id' => 1]);
        $request->attributes->set('id', 1);

        $argumentMetadata = $this->createArgumentMetadataMock(TestDto::class);
        $this->initializeResolver();

        $dto = $this->resolver->resolve($request, $argumentMetadata)->current();

        self::assertInstanceOf(TestDto::class, $dto);

        self::assertEquals(['title' => 'Test1', 'value' => 10], ['title' => $dto->title, 'value' => $dto->value]);
    }

    private function initializeResolver(?Configuration $configuration = null, array $routeOptions = []): void
    {
        $reader = new AnnotationReader();

        $configuration = $configuration ?? new Configuration(
                ['enabled' => true, 'methods' => ['GET', 'PATCH', 'OPTIONS']],
                ['strict_types' => ['enabled' => true]],
                ['enabled' => true, 'exception_class' => ConverterValidationException::class]
        );

        $this->resolver = new DtoArgumentResolver(
            $configuration,
            new Serializer([new ObjectNormalizer(null, null, null, new ReflectionExtractor())], [new JsonEncoder()]),
            $reader,
            $this->initializeRouteMetadataProviderMock($routeOptions),
            new DtoMapperFactory($this->serviceLocator),
            Validation::createValidatorBuilder()->addLoader(new AnnotationLoader($reader))->getValidator(),
            $this->registry,
            $this->language,
            $this->tokenStorage
        );
    }

    private function createArgumentMetadataMock(?string $className, string $name = 'arg'): ArgumentMetadata
    {
        $metadata = $this
            ->getMockBuilder(ArgumentMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata
            ->expects($this->any())
            ->method('getType')
            ->willReturn($className)
        ;

        $metadata
            ->expects($this->any())
            ->method('getName')
            ->willReturn($name)
        ;

        return $metadata;
    }

    private function initializeRouteMetadataProviderMock(array $routeOptions = []): RouteMetadataProvider
    {
        $provider = $this->getMockBuilder(RouteMetadataProvider::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $provider
            ->expects($this->any())
            ->method('getMetadata')
            ->willReturn($routeOptions)
        ;

        return $provider;
    }

    private function createRequest(string $method = 'GET', array $parameters = []): Request
    {
        switch ($method) {
            case Request::METHOD_GET:
                $request = new Request($parameters);

                break;
            case Request::METHOD_POST:
            case Request::METHOD_PATCH:
                $request = new Request([], [], [], [], [], [], \json_encode($parameters));

                break;
            default:
                $request = new Request();

                break;
        }

        $request->setMethod($method);
        $request->headers->set('Content-Type', 'application/json');
        $request->attributes->set('_route', 'test_route');

        return $request;
    }

    private function configureServiceLocatorMock(string $expectedArgument, ?object $result): void
    {
        $this->serviceLocator
            ->expects($this->once())
            ->method('get')
            ->with($expectedArgument)
            ->willReturn($result);
    }

    private function configureManagerRegistryMock(string $expectedEntityClassName, object $repository, array $entityFields = []): void
    {
        $managerMock = $this->getMockBuilder(ObjectManager::class)->getMock();

        $managerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->with($expectedEntityClassName)
            ->willReturn($repository);

        $metadataMock = $this->getMockBuilder(ClassMetadata::class)->getMock();

        $metadataMock
            ->expects($this->any())
            ->method('hasField')
            ->willReturnCallback(static fn ($field) => \in_array($field, $entityFields));

        $metadataMock
            ->expects($this->any())
            ->method('isIdentifier')
            ->willReturnCallback(static fn ($field) => $field === 'id');

        $managerMock
            ->expects($this->any())
            ->method('getClassMetadata')
            ->with($expectedEntityClassName)
            ->willReturn($metadataMock);

        $this->registry
            ->expects($this->atLeastOnce())
            ->method('getManagerForClass')
            ->with($expectedEntityClassName)
            ->willReturn($managerMock);
    }
}
