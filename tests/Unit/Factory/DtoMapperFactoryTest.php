<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Unit\Factory;

use Pfilsx\DtoParamConverter\Factory\DtoMapperFactory;
use Pfilsx\DtoParamConverter\Tests\Fixtures\DtoMapper\TestDtoMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class DtoMapperFactoryTest extends TestCase
{
    /** @var mixed */
    private $returningResult = null;

    private DtoMapperFactory $factory;

    public function setUp(): void
    {
        $locator = $this->createMock(ServiceLocator::class);
        $locator
            ->expects($this->once())
            ->method('get')
            ->with('testDto')
            ->willReturnReference($this->returningResult);

        $this->factory = new DtoMapperFactory($locator);
    }

    public function testGetMapper(): void
    {
        $this->returningResult = new TestDtoMapper();
        $result = $this->factory->getMapper('testDto');

        self::assertInstanceOf(TestDtoMapper::class, $result);
        self::assertSame($this->returningResult, $result);
    }

    public function testGetMapperOnInvalidMapper(): void
    {
        $this->expectException(\LogicException::class);
        $this->returningResult = new \stdClass();
        $this->factory->getMapper('testDto');
    }
}
