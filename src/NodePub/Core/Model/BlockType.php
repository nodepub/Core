<?php

namespace NodePub\Core\Model;

use NodePub\Core\Extension\ExtensionInterface;

class BlockType
{
    public
        $name,
        $namespace,
        $extensionPath,
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
        $path = $this->extensionPath . '/Blocks/' . str_replace(' ', '', $this->name);
        if (is_dir($path) || is_link($path)) {
            return $path;
        } else {
            throw new \Exception("Block path is not valid: {$path}", 500);
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