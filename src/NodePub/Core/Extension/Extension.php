<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\ExtensionInterface;
use NodePub\Core\Config\ExtensionConfiguration;
use Silex\Application;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

abstract class Extension implements ExtensionInterface
{
    protected $app,
              $config
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
        $classInfo = new \ReflectionClass($this);
        $configFile = dirname($classInfo->getFileName()) . '/config.yml';
        
        if (is_file($configFile)) {
            return $configFile;
        } else {
            throw new \Exception("No config.yml file for extension", 500);
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
    public function getAssets()
    {
        return $this->config['assets'];
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