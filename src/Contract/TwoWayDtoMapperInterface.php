<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Contract;

interface TwoWayDtoMapperInterface extends DtoMapperInterface
{
    /**
     * @param mixed $dto
     * @param mixed $entity
     */
    public function mapToEntity($dto, $entity): void;
}
