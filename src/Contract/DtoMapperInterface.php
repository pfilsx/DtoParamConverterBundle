<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Contract;

interface DtoMapperInterface
{
    public static function getDtoClassName(): string;

    /**
     * @param mixed $entity
     * @param mixed $dto
     */
    public function mapToDto($entity, $dto): void;

    /**
     * @param mixed $dto
     * @param mixed $entity
     */
    public function mapToEntity($dto, $entity): void;
}
