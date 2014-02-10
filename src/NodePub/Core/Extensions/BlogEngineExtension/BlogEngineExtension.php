<?php

namespace NodePub\Core\Extensions\BlogEngineExtension;

use NodePub\Core\Extension\Extension;

class BlogEngineExtension extends Extension
{
    public function isCore()
    {
        return true;
    }
    
    public function isInstalled()
    {
        return true;
    }
    
    public function isEnabled()
    {
        return true;
    }
}