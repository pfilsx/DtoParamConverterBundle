<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\DependencyInjection;

use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

final class DtoParamConverterExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(DtoMapperInterface::class)
            ->addTag('pfilsx.dto_converter.dto_mapper');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }
}