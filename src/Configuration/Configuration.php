<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Configuration;

use Pfilsx\DtoParamConverter\Contract\NormalizerExceptionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @internal
 */
final class Configuration
{
    private string $normalizerExceptionClass;

    private PreloadConfiguration $preloadConfiguration;

    private ValidationConfiguration $validationConfiguration;

    private StrictTypesConfiguration $strictTypesConfiguration;

    public function __construct(
        string $normalizerExceptionClass,
        array $preloadParams,
        array $validationParams,
        array $strictTypes
    ) {
        if (!class_exists($normalizerExceptionClass)) {
            throw new InvalidConfigurationException("Unable to determine class: {$normalizerExceptionClass}");
        }

        if (!is_subclass_of($normalizerExceptionClass, NormalizerExceptionInterface::class)) {
            throw new InvalidConfigurationException('Normalizer exception class should implements ' . NormalizerExceptionInterface::class);
        }

        $this->normalizerExceptionClass = $normalizerExceptionClass;

        $this->preloadConfiguration = PreloadConfiguration::create($preloadParams);

        $this->validationConfiguration = ValidationConfiguration::create($validationParams);

        $this->strictTypesConfiguration = StrictTypesConfiguration::create($strictTypes);
    }

    public function getNormalizerExceptionClass(): string
    {
        return $this->normalizerExceptionClass;
    }

    public function getPreloadConfiguration(): PreloadConfiguration
    {
        return $this->preloadConfiguration;
    }

    public function getValidationConfiguration(): ValidationConfiguration
    {
        return $this->validationConfiguration;
    }

    public function getStrictTypesConfiguration(): StrictTypesConfiguration
    {
        return $this->strictTypesConfiguration;
    }
}
