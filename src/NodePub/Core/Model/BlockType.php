<?php

namespace NodePub\Core\Model;

use NodePub\Core\Extension\ExtensionInterface;

class BlockType
{
    public
        $name,
        $namespace,
        $extension,
        $isCore,
        $isInstalled
        ;
    
    public function __construct()
    {
        $this->isInstalled = false;
        $this->isCore = false;
    }
    
    public function getPath()
    {
        if ($this->extension instanceof ExtensionInterface) {
            return $this->extension->getPath() . '/Blocks/' . $this->name;
        }
    }
    
    public static function factory($data = array())
    {
        $blockType = new BlockType();
        
        foreach ($data as $key => $value) {
            if (property_exists($blockType, $key)) {
                $blockType->{$key} = $value;
            }
        }
        
        return $blockType;
    }
}