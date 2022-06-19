<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Configuration;

/**
 * @internal
 */
final class PreloadConfiguration
{
    private bool $enabled;

    private array $methods = [];

    private bool $optional;

    private ?string $managerName;

    public static function create(array $params): self
    {
        $configuration = new self();
        $configuration->enabled = $params['enabled'] ?? true;
        $configuration->methods = $params['methods'] ?? [];
        $configuration->optional = $params['optional'] ?? false;
        $configuration->managerName = $params['entity_manager_name'] ?? null;

        return $configuration;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function getManagerName(): ?string
    {
        return $this->managerName;
    }
}
