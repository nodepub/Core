<?php

use NodePub\Core\Extension\ExtensionInterface;

/**
 * Installs/removes an extension's assets
 */
class ExtensionInstaller
{
    const SHARED_PATH = '/themes/shared';
    const JS_PATH = self::SHARED_PATH . '/js/np';
    const CSS_PATH = self::SHARED_PATH . '/css';
    
    protected $baseDir;

    /**
     * @param string $basePath Base path for asset install (usually /web or /public)
     */
    function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Symlinks an Extension's resources to the web root
     */
    public function install(ExtensionInterface $extension)
    {
        $assets = $extension->getAssets();
        
        foreach ($this->buildPaths($extension) as $source => $destination) {
            
            $source = $extension->getPath() . $source;
            
            if (file_exists($source) && !is_link($destination) !is_file($destination)) {
                symlink($destination, realpath($source));
            }
        }
    }

    /**
     * Removes an extension's resources from the web root.
     */
    public function uninstall(ExtensionInterface $extension)
    {
        $uninstalled = array();
        $errors = array();
        
        foreach ($this->buildPaths($extension) as $path) {
            if (unlink($path)) {
                $uninstalled[]= $path;
            } else {
                $errors[]= $path;
            }
        }
        
        return array($uninstalled, $errors);
    }
    
    protected function buildPaths(ExtensionInterface $extension)
    {
        $paths = array();
        
        foreach ($extension->getAssets() as $assetPath) {

            // check if path is absolute
            if (strpos(DIRECTORY_SEPARATOR, $assetPath) === 0) {
                $path = '';
                $srcPath = $assetPath;
            } else {
                $srcPath = $extension->getPath() . $assetPath;
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext === 'js') {
                    $path = self::JS_PATH;
                } elseif ($ext === 'css') {
                    $path = self::CSS_PATH;
                } else {
                    $path = self::SHARED_PATH;
                }
            }
            
            $paths[$srcPath] = $this->basePath . $path . '/' . $assetPath;
        }
        
        return $paths;
    }
}