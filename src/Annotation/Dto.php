<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Dto
{
    public ?string $linkedEntity = null;
}
