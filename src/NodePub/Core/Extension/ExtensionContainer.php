<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\DomManipulator;
use NodePub\Core\Extension\ExtensionInterface;
use NodePub\Core\Extension\SnippetQueue;
use NodePub\Core\Model\BlockType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

/**
 * A service container for all NodePub Extensions. Aggregates extension items.
 */
class ExtensionContainer extends \Pimple
{
    public $booted = false;
    protected $app;
    protected $extensions = array();
    protected $twigExtensionConfigs = array();
    protected $blockTypeConfigs = array();

    public function __construct(Application $app, array $values = array())
    {
        parent::__construct();
        
        $this->app = $app;

        $this['debug'] = false;
        $this['admin'] = false;
        $this['admin_content'] = '';
        
        $this['block_type_factory'] = $app->protect(function($blockTypeData) {
            return BlockType::factory($blockTypeData);
        });
        
        $this['block_types'] = $this->share(function() {
            return array();
        });
        
        $this['twig_extensions'] = $this->share(function() {
            return array();
        });

        $this['snippet_queue'] = $this->share(function() {
            return new SnippetQueue();
        });

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    }

    /**
     * Registers an extension.
     *
     * @param ExtensionInterface $extension An ExtensionInterface instance
     *
     * @return ExtensionContainer
     */
    public function register(ExtensionInterface $extension)
    {
        $this->extensions[$extension->getNamespace()] = $extension;
        
        // Collect extension elements to prevent having to iterate over them multiple times
        $this->registerTwigExtensions($extension);
        $this->registerBlockTypes($extension);
        
        return $this;
    }

    /**
     * Boots all extensions.
     */
    public function boot()
    {
        if (!$this->booted) {

            foreach ($this->extensions as $ext) {
                if ($this['admin'] && isset($this['toolbar'])) {
                    $this['toolbar']->addItems($ext->getToolbarItems());
                }
            }
            
            $this->bootTwigExtensions();
            $this->bootBlockTypes();

            $this->booted = true;
        }
    }

    /**
     * Aggregates admin content from all extensions and adds it to the snippet queue
     * for insertion right before the closing </body> tag.
     */
    public function prepareSnippets()
    {
        if (!$this->booted) { return; }
        
        foreach ($this->extensions as $ext) {
            
            $this['snippet_queue'] = $this->share($this->extend('snippet_queue', function($snippets) use ($ext) {
                $snippets->add($ext->getSnippets());
                return $snippets;
            }));
            
            if ($this['admin']) {
                $this['admin_content'] .= $ext->getAdminContent();
            }
        }

        $this['snippet_queue']->insert(DomManipulator::END_BODY, $this['admin_content']);
    }

    /**
     * Gets an extension by namespace if exists
     */
    public function getExtension($extNamespace)
    {
        if (isset($this->extensions[$extNamespace])) {
            return $this->extensions[$extNamespace];
        }

        throw new \Exception("No extension found with name [$extName]");
    }

    public function getAll()
    {
        return $this->extensions;
    }
    
    /**
     * Registers any twig extensions defined by an extension
     */
    protected function registerTwigExtensions(ExtensionInterface $ext) {
        $twigExtensions = $ext->getTwigExtensions();
        if (!empty($twigExtensions)) {
            $this->twigExtensionConfigs = array_merge($this->twigExtensionConfigs, $twigExtensions);
        }
    }
    
    /**
     * Instantiates configured twig extensions, passing in its configured dependencies
     */
    protected function bootTwigExtensions()
    {
        foreach ($this->twigExtensionConfigs as $className => $attrs) {
            if (isset($attrs['dependencies']) && is_array($attrs['dependencies'])) {
                $reflector = new \ReflectionClass($className);
                $extension = $reflector->newInstanceArgs(
                    $this->expandDependencies($attrs['dependencies'])
                );
            } else {
                $extension = new $className();
            }
            
            $this['twig_extensions'] = $this->share($this->extend('twig_extensions', function($twigExtensions) use ($extension) {
                $twigExtensions[] = $extension;
                return $twigExtensions;
            }));
        }
    }
    
    /**
     * Given an array of service IDs, returns an array of the actual services if they are found
     */
    protected function expandDependencies($dependencies = array())
    {
        $expandedDependencies = array();
        
        foreach ($dependencies as $serviceId) {
            if (isset($this->app[$serviceId])) {
                $expandedDependencies[] = $this->app[$serviceId];
            }
        }
        
        return $expandedDependencies;
    }
    
    /**
     * Registers any block types defined by an extension.
     * Uses the extension's class name as a key:
     *     $this['block_types']['CoreExtension'] = array(...);
     */
    protected function registerBlockTypes(ExtensionInterface $ext)
    {
        $blockTypes = $ext->getBlockTypes();
        if (!empty($blockTypes)) {
            $this->blockTypeConfigs = array_merge($this->blockTypeConfigs, $blockTypes);
        }
    }
    
    protected function bootBlockTypes()
    {
        $blockTypeFactory = $this['block_type_factory'];
        $blockTypes = array();
        
        foreach ($this->blockTypeConfigs as $blockTypeConfig) {
            $blockTypes[] = $blockTypeFactory($blockTypeConfig);
        }

        $this['block_types'] = $this->share(function() use ($blockTypes) {
            return $blockTypes;
        });
    }
}