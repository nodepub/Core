<?php

namespace NodePub\Core\Model;

/**
 * This is a stub for holding a collection of block sources
 */
class BlockProvider
{
    protected $sources;

    public function __construct()
    {
        $this->sources = array();
    }

    public function addSource($source)
    {
        if (method_exists($source, 'get')) {
            $this->sources[] = $source;
        } else {
            throw new \Exception("Source must be of proper type");
        }
    }

    public function get($blockId)
    {
        foreach ($this->sources as $source) {
            if ($block = $source->get($blockId)) {
                return $block;
            }
        }
    }
}
