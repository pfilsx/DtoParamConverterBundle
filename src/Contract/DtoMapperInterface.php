<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Contract;

interface DtoMapperInterface
{
    public static function getDtoClassName(): string;

    public function mapToDto(object $entity, object $dto): void;

    public function mapToEntity(object $dto, object $entity): void;
}
