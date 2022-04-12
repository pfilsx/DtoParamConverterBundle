<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
final class DtoResolver
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
