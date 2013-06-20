<?php

use NodePub\Core\Extension\ExtensionInterface;

/**
 * Installs/removes an extension's dependencies
 */
class ExtensionInstaller
{
    protected $baseDir;

    function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * Symlinks an Extension's resources to the web root
     */
    public function install(ExtensionInterface $extension)
    {
        foreach ($extension->getResourceManifest() as $path) {
            $file = $extension->getResourceDirectory().$path;
            if (file_exists($file)) {
                symlink($this->baseDir.$path, realpath($file));
            }
        }
    }

    /**
     * Removes an extension's resources from the web root.
     */
    public function uninstall(ExtensionInterface $extension)
    {
        foreach ($extension->getResourceManifest() as $resourcePath) {
            unlink();
        }
    }
}