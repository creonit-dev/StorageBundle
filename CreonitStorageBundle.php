<?php

namespace Creonit\StorageBundle;

use Creonit\StorageBundle\DependencyInjection\Compiler\StorageVariablePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CreonitStorageBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new StorageVariablePass());
    }
}
