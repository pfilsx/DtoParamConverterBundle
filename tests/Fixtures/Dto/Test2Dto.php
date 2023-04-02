<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\Dto;

use Pfilsx\DtoParamConverter\Annotation\Dto;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Dto()
 */
final class Test2Dto
{
    /**
     * @Assert\NotBlank()
     *
     * @Assert\Url()
     */
    public ?string $url = null;
}
