<?php

namespace NodePub\Core\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Application config for a NodePub installation. The defaults defined here
 * are used to generate the initial app.yml file which is then user configurable.
 */
class ApplicationConfiguration implements ConfigurationInterface
{
    const VERSION = '0.5';
    
    const DEFAULT_ADMIN_URI = '/np-admin';
    
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('np_app');
        
        $rootNode
            ->children()
                
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('uri')
                            ->isRequired()
                            ->defaultValue(self::DEFAULT_ADMIN_URI)
                        ->end()
                    ->end()
                ->end()
                
                ->arrayNode('form')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('secret')
                            // this value is used in the initial installation for generating a unique value
                            // per np install. It will be combined with the site host name
                            ->defaultValue(md5(strtotime('now')))
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                
                ->integerNode('cache_age')
                    ->defaultValue(7200)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}