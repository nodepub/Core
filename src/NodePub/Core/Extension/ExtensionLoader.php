<?php

namespace NodePub\Core\Extension;

use Symfony\Component\Finder\Finder;

class ExtensionLoader
{
    protected $sourceDirs = array();
    
    function __construct()
    {
        $this->addSource(__DIR__ . '/../Extensions');
    }
    
    /**
     * Adds a directory path to the list of sources to load from.
     */
    public function addSource($sourcePath)
    {
        if (is_dir($sourcePath) || is_link($sourcePath)) {
            $this->sourceDirs[] = $sourcePath;
        } else {
            throw new \Exception("Specified load path is not a directory");
        }
    }
    
    /**
     * Finds all files in the configured posts dir(s)
     * with the configured file extension.
     *
     * @return array SplFileInfo objects
     */
    protected function findExtensionDirectories()
    {
        $extDirs = Finder::create()
            ->directories()
            ->depth('== 0')
            ->name('*Extension')
            ;
        
        # add all source paths to the finder
        foreach ($this->sourceDirs as $dir) {
           $extDirs->in($dir);
        }
        
        return $extDirs;
    }
}
