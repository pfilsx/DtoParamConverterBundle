<?php

declare(strict_types=1);

namespace Pfilsx\DtoParamConverter;

use Pfilsx\DtoParamConverter\DependencyInjection\Compiler\AddExpressionLanguageProvidersPass;
use Pfilsx\DtoParamConverter\DependencyInjection\Compiler\AddMapperFactoriesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class DtoParamConverterBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AddMapperFactoriesPass());
        $container->addCompilerPass(new AddExpressionLanguageProvidersPass());
    }
}
