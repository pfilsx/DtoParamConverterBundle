<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Dto
{
    private ?string $linkedEntity;

    private ?bool $preload;

    public function __construct(?string $linkedEntity = null, ?bool $preload = null)
    {
        $this->linkedEntity = $linkedEntity;
        $this->preload = $preload;
    }

    public function getLinkedEntity(): ?string
    {
        return $this->linkedEntity;
    }

    public function isPreload(): ?bool
    {
        return $this->preload;
    }
}
