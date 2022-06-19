<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Provider;

use Doctrine\Common\Annotations\Reader;
use Pfilsx\DtoParamConverter\Annotation\Dto;
use ReflectionClass;

final class DtoMetadataProvider
{
    private Reader $reader;

    /**
     * @var array<string, null|Dto>
     */
    private array $localCollection = [];

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function getDtoMetadata(string $className): ?Dto
    {
        if (\array_key_exists($className, $this->localCollection)) {
            return $this->localCollection[$className];
        }

        try {
            $refClass = new ReflectionClass($className);
        } catch (\ReflectionException $e) {
            $this->localCollection[$className] = null;

            return null;
        }

        $metadata = $this->reader->getClassAnnotation($refClass, Dto::class);

        if ($metadata !== null) {
            $this->localCollection[$className] = $metadata;

            return $metadata;
        }

        if (\PHP_VERSION_ID >= 80000) {
            $metadata = array_map(
                function (\ReflectionAttribute $attribute) {
                    return $attribute->newInstance();
                },
                $refClass->getAttributes(Dto::class, \ReflectionAttribute::IS_INSTANCEOF)
            )[0] ?? null;

            if ($metadata !== null) {
                $this->localCollection[$className] = $metadata;

                return $metadata;
            }
        }

        return null;
    }
}
