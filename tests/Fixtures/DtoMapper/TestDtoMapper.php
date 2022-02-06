<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Tests\Fixtures\DtoMapper;

use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Dto\TestDto;
use Pfilsx\DtoParamConverter\Tests\Fixtures\Entity\TestEntity;

final class TestDtoMapper implements DtoMapperInterface
{
    public static function getDtoClassName(): string
    {
        return TestDto::class;
    }

    /**
     * @param object|TestEntity $entity
     * @param object|TestDto    $dto
     */
    public function mapToDto(object $entity, object $dto): void
    {
        $dto->title = $entity->getTitle();
        $dto->value = $entity->getValue();
    }

    public function mapToEntity(object $dto, object $entity): void
    {
    }
}
