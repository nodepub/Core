<?php

namespace NodePub\Core\Yaml;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Loads a YAML file into a Doctrine ArrayCollection
 */
class YamlCollectionLoader
{
    protected $sourceDirs = array();
    
    function __construct($sourceDirs)
    {
        if (is_array($sourceDirs)) {
            foreach ($sourceDirs as $dir) {
                $this->addSource($dir);
            }
        } elseif (is_string($sourceDirs)) {
            $this->addSource($sourceDirs);
        }
    }
    
    /**
     * Adds a directory path to the list of sources to load from.
     */
    public function addSource($sourcePath)
    {
        if (is_dir($sourcePath)) {
            $this->sourceDirs[] = $sourcePath;
        } else {
            throw new \Exception("Specified load path is not a directory");
        }
    }

    /**
     * Searches source paths for the specified YAML file and parses its contents.
     * @return ArrayCollection
     */
    public function load($name)
    {
        foreach ($this->sourceDirs as $dir) {
            $yamlFile = $dir.'/'.$name.'.yml';
            
            if (is_file($yamlFile)) {
                return new ArrayCollection(Yaml::parse($yamlFile));
            }
        }
        
        throw new \Exception("No Yaml file found");
    }
}
