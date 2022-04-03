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
                ->scalarNode('validation_exception_class')->defaultValue(ConverterValidationException::class)->end()
                ->scalarNode('normalizer_exception_class')->defaultValue(NotNormalizableConverterValueException::class)->end()
                ->arrayNode('preload')
                    ->info('data preload from entity into dto before request handling configuration')
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('methods')
                            ->scalarPrototype()->end()
                        ->defaultValue([Request::METHOD_GET, Request::METHOD_PATCH])
                        ->end()
                        ->booleanNode('optional')->defaultFalse()->end()
                        ->scalarNode('entity_manager_name')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('strict_types')
                    ->info('type enforcement on denormalization configuration')
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('excluded_methods')
                            ->scalarPrototype()->end()
                        ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
