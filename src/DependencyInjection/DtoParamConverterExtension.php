<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\DependencyInjection;

use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class DtoParamConverterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadConfiguration($configs, $container);

        $container->registerForAutoconfiguration(DtoMapperInterface::class)
            ->addTag('pfilsx.dto_converter.dto_mapper');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    private function loadConfiguration(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $configArray = $this->processConfiguration($configuration, $configs);

        $container->setParameter('pfilsx.dto_converter.preload_entity', $configArray['preload_entity']);
        $container->setParameter('pfilsx.dto_converter.strict_preload_entity', $configArray['strict_preload_entity']);
        $container->setParameter('pfilsx.dto_converter.preload_methods', $configArray['preload_methods']);
        $container->setParameter('pfilsx.dto_converter.validation_exception_class', $configArray['validation_exception_class']);
        $container->setParameter('pfilsx.dto_converter.normalizer_exception_class', $configArray['normalizer_exception_class']);
        $container->setParameter('pfilsx.dto_converter.strict_types', $configArray['strict_types']);
    }
}
