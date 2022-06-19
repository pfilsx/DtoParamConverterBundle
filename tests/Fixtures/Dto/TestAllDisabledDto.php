<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\Dto;

use Pfilsx\DtoParamConverter\Annotation\Dto;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Entity\TestEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Dto(linkedEntity=TestEntity::class, preload=false, validate=false)
 */
final class TestAllDisabledDto
{
    /**
     * @Assert\NotBlank()
     */
    public ?string $title = null;

    /**
     * @Assert\NotNull
     * @Assert\GreaterThanOrEqual(10)
     */
    public ?int $value = null;
}
