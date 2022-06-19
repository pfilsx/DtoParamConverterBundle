<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\DependencyInjection;

use Pfilsx\DtoParamConverter\Contract\DtoMapperInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\ClassExistenceResource;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class DtoParamConverterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadConfiguration($configs, $container);

        $definitionsToRemove = [];

        $container->addResource(new ClassExistenceResource(ExpressionLanguage::class));
        if (class_exists(ExpressionLanguage::class)) {
            $container->setAlias('pfilsx.dto_converter.expression_language', new Alias('pfilsx.dto_converter.expression_language.default', false));
        } else {
            $definitionsToRemove[] = 'pfilsx.dto_converter.expression_language.default';
        }

        $container->registerForAutoconfiguration(DtoMapperInterface::class)
            ->addTag('pfilsx.dto_converter.dto_mapper');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        foreach ($definitionsToRemove as $definition) {
            $container->removeDefinition($definition);
        }
    }

    private function loadConfiguration(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $configArray = $this->processConfiguration($configuration, $configs);

        $container->setParameter('pfilsx.dto_converter.preload_params', $configArray['preload']);

        $serializerConfig = $configArray['serializer'];

        $container->setParameter('pfilsx.dto_converter.serializer_params', $serializerConfig);
        $container->setAlias('pfilsx.dto_converter.serializer', new Alias($serializerConfig['service'], true));

        $container->setParameter('pfilsx.dto_converter.validation_params', $configArray['validation']);
    }
}
