<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\DtoMapper;

use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Dto\TestAttributesDto;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Entity\TestEntity;

final class TestAttributesDtoMapper implements DtoMapperInterface
{
    public static function getDtoClassName(): string
    {
        return TestAttributesDto::class;
    }

    /**
     * @param TestEntity        $entity
     * @param TestAttributesDto $dto
     */
    public function mapToDto($entity, $dto): void
    {
        $dto->title = $entity->getTitle();
        $dto->value = $entity->getValue();
    }
}
