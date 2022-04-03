<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Configuration;

use Pfilsx\DtoParamConverter\Contract\NormalizerExceptionInterface;
use Pfilsx\DtoParamConverter\Contract\ValidationExceptionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @internal
 */
final class Configuration
{
    private string $validationExceptionClass;
    private string $normalizerExceptionClass;

    private PreloadConfiguration $preloadConfiguration;

    private StrictTypesConfiguration $strictTypesConfiguration;

    public function __construct(
        string $validationExceptionClass,
        string $normalizerExceptionClass,
        array $preloadParams,
        array $strictTypes
    ) {
        if (!class_exists($validationExceptionClass)) {
            throw new InvalidConfigurationException("Unable to determine class: {$validationExceptionClass}");
        }

        if (!is_subclass_of($validationExceptionClass, ValidationExceptionInterface::class)) {
            throw new InvalidConfigurationException('Validation exception class should implements ' . ValidationExceptionInterface::class);
        }

        $this->validationExceptionClass = $validationExceptionClass;

        if (!class_exists($normalizerExceptionClass)) {
            throw new InvalidConfigurationException("Unable to determine class: {$normalizerExceptionClass}");
        }

        if (!is_subclass_of($normalizerExceptionClass, NormalizerExceptionInterface::class)) {
            throw new InvalidConfigurationException('Normalizer exception class should implements ' . NormalizerExceptionInterface::class);
        }

        $this->normalizerExceptionClass = $normalizerExceptionClass;

        $this->preloadConfiguration = PreloadConfiguration::create($preloadParams);

        $this->strictTypesConfiguration = StrictTypesConfiguration::create($strictTypes);
    }

    public function getValidationExceptionClass(): string
    {
        return $this->validationExceptionClass;
    }

    public function getNormalizerExceptionClass(): string
    {
        return $this->normalizerExceptionClass;
    }

    public function getPreloadConfiguration(): PreloadConfiguration
    {
        return $this->preloadConfiguration;
    }

    public function getStrictTypesConfiguration(): StrictTypesConfiguration
    {
        return $this->strictTypesConfiguration;
    }
}
