<?php

namespace Creonit\StorageBundle\DependencyInjection;

use Creonit\StorageBundle\Storage\Storage;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


class CreonitStorageExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $storageConfiguration = $container->getDefinition(Storage::class);
        $storageConfiguration->addMethodCall('setLocales', [$config['locales']]);
        $storageConfiguration->addMethodCall('setSections', [$config['sections']]);
        $storageConfiguration->addMethodCall('setItems', [$config['items']]);
    }
}
