<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\DependencyInjection;

use Pfilsx\DtoParamConverter\Exception\ConverterValidationException;
use Pfilsx\DtoParamConverter\Exception\NotNormalizableConverterValueException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dto_param_converter');
        $rootNode = \method_exists(TreeBuilder::class, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('dto_param_converter');

        $rootNode
            ->children()
            ->booleanNode('preload_entity')->defaultTrue()->end()
            ->booleanNode('strict_preload_entity')->defaultTrue()->end()
            ->arrayNode('preload_methods')
            ->scalarPrototype()->end()
            ->defaultValue([Request::METHOD_GET, Request::METHOD_PATCH])
            ->end()
            ->scalarNode('validation_exception_class')->defaultValue(ConverterValidationException::class)->end()
            ->scalarNode('normalizer_exception_class')->defaultValue(NotNormalizableConverterValueException::class)->end()
            ->end();

        return $treeBuilder;
    }
}
