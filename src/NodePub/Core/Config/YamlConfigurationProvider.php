<?php

namespace NodePub\Core\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * Loads and saves custom theme settings to a yaml file
 */
class YamlConfigurationProvider
{
    protected $filePath,
              $config,
              $defaultConfig;

    /**
     * @param string $filePath
     */
    function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function get($key, $default = null)
    {
        if (is_null($this->config)) {
            $this->load();
        }

        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    public function getAll()
    {
        if (is_null($this->config)) {
            $this->load();
        }

        return $this->config;
    }

    public function update($key, $value)
    {
        $this->config[$key] = $value;
        
        try {
            $this->save();
            return true;
        } catch (\Exception $e) {
            # TODO: log the error
            return false;
        }
    }

    /**
     * Loads the yaml config file
     */
    protected function load()
    {
        $this->config = array();

        if (file_exists($this->filePath)) {
            $this->config = Yaml::parse($this->filePath);
        }
    }

    /**
     * Saves the current configuration array to the configuration yaml file
     */
    protected function save()
    {
        $yaml = Yaml::dump($this->config, 2);
        file_put_contents($this->filePath, $yaml);
    }
}