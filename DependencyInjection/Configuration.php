<?php

namespace Creonit\StorageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('creonit_storage');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('locales')->scalarPrototype()->end()->end()
                ->arrayNode('sections')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('title')->isRequired()->end()
                            ->scalarNode('icon')->defaultValue('')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('items')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('title')->isRequired()->end()
                            ->scalarNode('section')->defaultValue('')->end()
                            ->scalarNode('icon')->defaultValue('')->end()
                            ->booleanNode('collection')->defaultFalse()->end()
                            ->booleanNode('context')->defaultFalse()->end()
                            ->booleanNode('i18n')->defaultFalse()->end()
                            ->arrayNode('fields')
                                ->isRequired()
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('title')->defaultValue('')->end()
                                        ->scalarNode('type')->defaultValue('text')->end()
                                        ->scalarNode('notice')->defaultValue('')->end()
                                        ->booleanNode('caption')->defaultFalse()->end()
                                        ->booleanNode('i18n')->defaultFalse()->end()
                                        ->booleanNode('required')->defaultFalse()->end()
                                        ->variableNode('default')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
