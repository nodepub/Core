<?php

namespace NodePub\Core\Extension;

use NodePub\Common\Trait\SourceDirectoryAwareInterface;
use NodePub\Common\Trait\SourceDirectoryAwareTrait;
use Symfony\Component\Finder\Finder;

class ExtensionLoader implements SourceDirectoryAwareInterface
{
    use SourceDirectoryAwareTrait;
    
    function __construct()
    {
        $this->addSource(__DIR__ . '/../Extensions');
    }
    
    /**
     * Finds all files in the configured posts dir(s)
     * with the configured file extension.
     *
     * @return array SplFileInfo objects
     */
    protected function findExtensionDirectories()
    {
        $finder = Finder::create()
            ->directories()
            ->depth('== 0')
            ->name('*Extension')
            ;
        
        return $this->findInSourceDirs($finder);
    }
}
