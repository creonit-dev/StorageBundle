<?php


namespace Creonit\StorageBundle\DependencyInjection\Compiler;


use Creonit\StorageBundle\Storage\StorageVariable;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StorageVariablePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $twig = $container->getDefinition('twig');
        $twig->addMethodCall('addGlobal', ['storage', $container->getDefinition(StorageVariable::class)]);
    }
}