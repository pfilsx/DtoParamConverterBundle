<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Unit\Provider;

use Doctrine\Common\Annotations\Reader;
use Pfilsx\DtoParamConverter\Annotation\DtoResolver;
use Pfilsx\DtoParamConverter\Provider\RouteMetadataProvider;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Controller\SimpleController;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\CacheException;

final class RouteMetadataProviderTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var Reader
     */
    private $reader;

    private RouteMetadataProvider $provider;

    protected function setUp(): void
    {
        $this->cache = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $this->reader = $this->getMockBuilder(Reader::class)->getMock();
        $this->provider = new RouteMetadataProvider($this->cache, $this->reader);
    }

    public function testGetMetadataFromLocalCollection(): void
    {
        $this->setLocalCollection();

        self::assertEquals(['_default' => ['test' => true]], $this->provider->getMetadata('test_key'));
    }

    public function testGetMetadataFromCache(): void
    {
        $this->mockCacheItemWithHit();

        self::assertEquals(['_default' => ['test' => true]], $this->provider->getMetadata('test_key'));
    }

    public function testGetMetadataOnCacheException(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('pfilsx_route_metadata_test_key')
            ->willThrowException(new CacheException())
        ;

        self::assertEquals([], $this->provider->getMetadata('test_key'));
    }

    public function testGetMetadataOnEmptyData(): void
    {
        $this->mockCacheItemWithoutHit();

        self::assertEquals([], $this->provider->getMetadata('test_key'));
    }

    public function testCreateMetadataOnAlreadyInLocalCollectionKey(): void
    {
        $this->setLocalCollection();

        $this->provider->createMetadata('test_key', []);

        self::assertEquals(['test_key' => ['_default' => ['test' => true]]], $this->getLocalCollection());
    }

    public function testCreateMetadataFromCache(): void
    {
        $this->mockCacheItemWithHit();

        $this->provider->createMetadata('test_key', []);

        self::assertEquals(['test_key' => ['_default' => ['test' => true]]], $this->getLocalCollection());
    }

    public function testCreateMetadata(): void
    {
        $this->mockCacheItemWithoutHit();

        $this->reader
            ->expects($this->once())
            ->method('getMethodAnnotations')
            ->willReturn([new DtoResolver('dto', ['test' => true])]);

        $this->provider->createMetadata('test_key', [new SimpleController(), 'getAction']);

        self::assertEquals(['test_key' => ['dto' => ['test' => true]]], $this->getLocalCollection());
    }

    public function testCreateMetadataOnCacheError(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('pfilsx_route_metadata_test_key')
            ->willThrowException(new CacheException())
        ;

        $this->reader
            ->expects($this->once())
            ->method('getMethodAnnotations')
            ->willReturn([new DtoResolver('dto', ['test' => true])]);

        $this->provider->createMetadata('test_key', [new SimpleController(), 'getAction']);

        self::assertEquals(['test_key' => ['dto' => ['test' => true]]], $this->getLocalCollection());
    }

    private function setLocalCollection(): void
    {
        $refClass = new \ReflectionClass(RouteMetadataProvider::class);
        $refProp = $refClass->getProperty('localCollection');

        $refProp->setAccessible(true);
        $refProp->setValue($this->provider, ['test_key' => ['_default' => ['test' => true]]]);
    }

    private function getLocalCollection(): array
    {
        $refClass = new \ReflectionClass(RouteMetadataProvider::class);
        $refProp = $refClass->getProperty('localCollection');

        $refProp->setAccessible(true);

        return $refProp->getValue($this->provider);
    }

    private function mockCacheItemWithHit(): void
    {
        $itemMock = $this->createMock(CacheItem::class);
        $itemMock
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(true)
        ;
        $itemMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(['_default' => ['test' => true]]);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('pfilsx_route_metadata_test_key')
            ->willReturn($itemMock)
        ;
    }

    private function mockCacheItemWithoutHit(): void
    {
        $itemMock = $this->createMock(CacheItem::class);
        $itemMock
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false)
        ;

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('pfilsx_route_metadata_test_key')
            ->willReturn($itemMock)
        ;
    }
}
