<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\ExtensionInterface;
use NodePub\Core\Config\ExtensionConfiguration;
use Silex\Application;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

abstract class Extension implements ExtensionInterface
{
    const CONFIG_FILE = 'config.yml';
    
    protected $app,
              $config,
              $reflection,
              $path,
              $blocks
              ;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->loadConfig();
    }
    
    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = $this->loadConfig();
        }
        
        return $this->config;
    }
    
    protected function loadConfig()
    {
        $processor = new Processor();
        $configSchema = new ExtensionConfiguration();
        
        return $processor->processConfiguration(
            $configSchema,
            array(Yaml::parse($this->getConfigFilePath()))
        );
    }
    
    protected function getConfigFilePath()
    {
        $configFile = $this->getPath() . '/' . self::CONFIG_FILE;
        
        if (is_file($configFile)) {
            return $configFile;
        } else {
            throw new \Exception("No config.yml file for extension", 500);
        }
    }
    
    /**
     * Returns fs path to extension's dir
     */
    protected function getPath()
    {
        if (is_null($this->path)) {
            $reflection = $this->getReflection();
            $this->path = dirname($reflection->getFileName());
        }
    
        return $this->path;
    }
    
    protected function getReflection()
    {
        if (is_null($this->reflection)) {
            $this->reflection = new \ReflectionClass($this);
        }
        
        return $this->reflection;
    }
    
    /**
     * Loads the config for each block
     */
    protected function loadBlocks()
    {
        $extPath = $this->getPath();
        foreach ($this->getBlockTypes as $blockName) {
            $this->blocks[$blockName] = $this->loadBlockConfig($extPath, $blockName);
        }
    }
    
    /**
     * Loads the config for each of extension's blocks
     */
    protected function loadBlockConfig($extPath, $blockName)
    {
        $configFile = sprintf('%s/Blocks/%s/%s', $extPath, $blockName, self::CONFIG_FILE);
        if (is_file($configFile)) {
            return Yaml::parse($configFile);
        } else {
            throw new \Exception("No config.yml file for block {$blockName}", 500);
        }
    }

    public function isActive()
    {
        return false;
    }

    public function isCore()
    {
        return false;
    }
    
    /**
     * @ExtensionInterface
     */
    public function getName()
    {
        return $this->config['name'];
    }
    
    /**
     * @ExtensionInterface
     */
    public function getNamespace()
    {
        return $this->getReflection()->getShortName();
    }

    /**
     * @ExtensionInterface
     */
    public function getAssets()
    {
        $assets = $this->config['assets'];
        
        foreach ($this->loadBlocks() as $blockName => $config) {
            if (isset($config['assets'])) {
                $assets = array_merge($assets, $config['assets']);
            }
        }
        
        return $assets;
    }

    /**
     * @ExtensionInterface
     */
    public function getAdminContent()
    {
        return '';
    }

    /**
     * @ExtensionInterface
     */
    public function getToolbarItems()
    {
        return $this->config['toolbar_items'];
    }

    /**
     * @ExtensionInterface
     */
    public function getBlockTypes()
    {
        return $this->config['block_types'];
    }

    /**
     * @ExtensionInterface
     */
    public function getTwigExtensions()
    {
        return $this->config['twig_extensions'];
    }

    /**
     * @ExtensionInterface
     */
    public function getSnippets()
    {
        return array();
    }
}