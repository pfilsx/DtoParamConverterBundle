<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AddExpressionLanguageProvidersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('pfilsx.dto_converter.expression_language.default')) {
            $definition = $container->findDefinition('pfilsx.dto_converter.expression_language.default');
            foreach ($container->findTaggedServiceIds('security.expression_language_provider') as $id => $attributes) {
                $definition->addMethodCall('registerProvider', [new Reference($id)]);
            }
        }
    }
}
