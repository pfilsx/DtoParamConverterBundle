<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\Factory;

use LogicException;
use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class DtoMapperFactory
{
    private ServiceLocator $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function getMapper(string $dtoClassName): DtoMapperInterface
    {
        $mapper = $this->locator->get($dtoClassName);
        if (!$mapper instanceof DtoMapperInterface) {
            throw new LogicException('Dto mapper should implements ' . DtoMapperInterface::class);
        }

        return $mapper;
    }
}
