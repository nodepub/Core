<?php

namespace NodePub\Core\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Defines the schema of a NodePub Extension config.
 */
class ExtensionConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('extension');
        
        $rootNode
            ->children()
                
                ->scalarNode('name')
                    ->isRequired()
                ->end()
                
                ->arrayNode('toolbar_items')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('route')->end()
                            ->scalarNode('icon')->end()
                        ->end()
                    ->end()
                    ->defaultValue(array())
                ->end()
                
                ->arrayNode('block_types')
                    ->prototype('scalar')->end()
                    ->defaultValue(array())
                ->end()
                
                ->arrayNode('twig_extensions')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('dependencies')
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                        ->end()
                    ->end()
                    ->defaultValue(array())
                ->end()
                
                ->arrayNode('assets')
                    ->prototype('scalar')->end()
                    ->defaultValue(array())
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}