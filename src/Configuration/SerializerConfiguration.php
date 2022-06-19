<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Configuration;

use Pfilsx\DtoParamConverter\Contract\NormalizerExceptionInterface;
use Pfilsx\DtoParamConverter\Exception\NotNormalizableConverterValueException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @internal
 */
final class SerializerConfiguration
{
    private string $normalizerExceptionClass;

    private StrictTypesConfiguration $strictTypesConfiguration;

    public static function create(array $params): self
    {
        $configuration = new self();
        $configuration->strictTypesConfiguration = StrictTypesConfiguration::create($params['strict_types'] ?? []);

        $normalizerExceptionClass = $params['normalizer_exception_class'] ?? NotNormalizableConverterValueException::class;
        if (!class_exists($normalizerExceptionClass)) {
            throw new InvalidConfigurationException("Unable to determine class: {$normalizerExceptionClass}");
        }

        if (!is_subclass_of($normalizerExceptionClass, NormalizerExceptionInterface::class)) {
            throw new InvalidConfigurationException('Normalizer exception class should implements ' . NormalizerExceptionInterface::class);
        }

        $configuration->normalizerExceptionClass = $normalizerExceptionClass;

        return $configuration;
    }

    public function getNormalizerExceptionClass(): string
    {
        return $this->normalizerExceptionClass;
    }

    public function getStrictTypesConfiguration(): StrictTypesConfiguration
    {
        return $this->strictTypesConfiguration;
    }
}
