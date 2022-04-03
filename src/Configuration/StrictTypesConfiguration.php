<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Configuration;

/**
 * @internal
 */
final class StrictTypesConfiguration
{
    private bool $enabled;

    private array $excludedMethods = [];

    public static function create(array $params): self
    {
        $configuration = new self();
        $configuration->enabled = $params['enabled'] ?? false;
        $configuration->excludedMethods = $params['excluded_methods'] ?? [];

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
}
