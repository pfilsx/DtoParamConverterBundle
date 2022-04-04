<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Configuration;

use Pfilsx\DtoParamConverter\Contract\ValidationExceptionInterface;
use Pfilsx\DtoParamConverter\Exception\ConverterValidationException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @internal
 */
final class ValidationConfiguration
{
    private bool $enabled;

    private array $excludedMethods = [];

    private string $exceptionClass;

    public static function create(array $params): self
    {
        $configuration = new self();
        $configuration->enabled = $params['enabled'] ?? true;
        $configuration->excludedMethods = $params['excluded_methods'] ?? [];
        $exceptionClass = $params['exception_class'] ?? ConverterValidationException::class;

        if (!class_exists($exceptionClass)) {
            throw new InvalidConfigurationException("Unable to determine class: $exceptionClass");
        }

        if (!is_subclass_of($exceptionClass, ValidationExceptionInterface::class)) {
            throw new InvalidConfigurationException('Validation exception class should implements ' . ValidationExceptionInterface::class);
        }

        $configuration->exceptionClass = $exceptionClass;

        return $configuration;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getExcludedMethods(): array
    {
        return $this->excludedMethods;
    }

    public function getExceptionClass(): string
    {
        return $this->exceptionClass;
    }
}
