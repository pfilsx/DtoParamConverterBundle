<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DtoParamConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('pfilsx.dto_converter.mapper_factory') === false) {
            return;
        }

        $definition = $container->getDefinition('pfilsx.dto_converter.mapper_factory');
        $mappersServices = $container->findTaggedServiceIds('pfilsx.dto_converter.dto_mapper', true);

        $mappersReferences = [];

        foreach ($mappersServices as $serviceId => $tags) {
            $serviceDefinition = $container->getDefinition($serviceId);
            $indexMethod = $serviceDefinition->getClass() . '::getDtoClassName';
            $mapperIndex = $indexMethod();
            $mappersReferences[$mapperIndex] = new Reference($serviceId);
        }

        $definition->replaceArgument(0, ServiceLocatorTagPass::register($container, $mappersReferences));
    }
}
