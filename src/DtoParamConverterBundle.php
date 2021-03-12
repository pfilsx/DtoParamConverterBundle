<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter;


use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class DtoParamConverterBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->registerForAutoconfiguration(DtoMapperInterface::class)
            ->addTag('pfilsx.dto_converter.dto_mapper');
    }
}