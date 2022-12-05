<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 *
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
final class DtoResolver
{
    private ?string $dtoName;

    private array $options;

    public function __construct($data = [], array $options = [])
    {
        $values = [];
        if (\is_string($data)) {
            $values['dtoName'] = $data;
        } else {
            $values = $data;
        }

        $this->dtoName = $values['dtoName'] ?? null;
        $this->options = $values['options'] ?? $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDtoName(): ?string
    {
        return $this->dtoName;
    }
}
