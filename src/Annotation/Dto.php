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

    private ?bool $validate;

    public function __construct(?string $linkedEntity = null, ?bool $preload = null, ?bool $validate = null)
    {
        $this->linkedEntity = $linkedEntity;
        $this->preload = $preload;
        $this->validate = $validate;
    }

    public function getLinkedEntity(): ?string
    {
        return $this->linkedEntity;
    }

    public function isPreload(): ?bool
    {
        return $this->preload;
    }

    public function isValidate(): ?bool
    {
        return $this->validate;
    }
}
