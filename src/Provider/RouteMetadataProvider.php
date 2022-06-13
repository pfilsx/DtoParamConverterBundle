<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Provider;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Persistence\Proxy;
use Pfilsx\DtoParamConverter\Annotation\DtoResolver;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;

final class RouteMetadataProvider
{
    private const PREFIX = 'pfilsx_route_metadata_';

    private CacheItemPoolInterface $cache;

    private Reader $reader;

    private array $localCollection = [];

    public function __construct(CacheItemPoolInterface $cache, Reader $reader)
    {
        $this->cache = $cache;
        $this->reader = $reader;
    }

    public function getMetadata(string $key): array
    {
        if (\array_key_exists($key, $this->localCollection)) {
            return $this->localCollection[$key];
        }

        try {
            $cacheItem = $this->cache->getItem(self::PREFIX . $key);
        } catch (CacheException $e) {
            return [];
        }

        if ($cacheItem->isHit()) {
            $this->localCollection[$key] = $cacheItem->get();

            return $this->localCollection[$key];
        }

        return [];
    }

    public function createMetadata(string $key, array $controller): void
    {
        if (\array_key_exists($key, $this->localCollection)) {
            return;
        }

        try {
            $cacheItem = $this->cache->getItem(self::PREFIX . $key);
        } catch (CacheException $e) {
            $metadata = $this->readMetadata($controller);
            $this->localCollection[$key] = $metadata;

            return;
        }

        if ($cacheItem->isHit()) {
            $this->localCollection[$key] = $cacheItem->get();

            return;
        }

        $metadata = $this->readMetadata($controller);
        $this->localCollection[$key] = $metadata;
        $cacheItem->set($metadata);

        $this->cache->save($cacheItem);
    }

    private function readMetadata(array $controller): array
    {
        $className = $this->getRealClass(\get_class($controller[0]));
        $object = new ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $methodOptions = $this->getOptions($this->reader->getMethodAnnotations($method));

        if (\PHP_VERSION_ID >= 80000) {
            $methodAttributes = array_map(
                function (\ReflectionAttribute $attribute) {
                    return $attribute->newInstance();
                },
                $method->getAttributes(DtoResolver::class, \ReflectionAttribute::IS_INSTANCEOF)
            );
            $methodOptions = array_merge($methodOptions, $this->getOptions($methodAttributes));
        }

        return $methodOptions;
    }

    private function getOptions(array $annotations): array
    {
        $options = null;
        foreach ($annotations as $annotation) {
            if ($annotation instanceof DtoResolver) {
                if ($options !== null) {
                    throw new \LogicException('Multiple "DtoResolver" annotations are not allowed.');
                }
                $options = $annotation->getOptions();
            }
        }

        return $options ?? [];
    }

    private function getRealClass(string $class): string
    {
        if (interface_exists(Proxy::class)) {
            if (false === $pos = strrpos($class, '\\' . Proxy::MARKER . '\\')) {
                return $class;
            }

            return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
        }

        return $class;
    }
}
