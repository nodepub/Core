<?php

namespace NodePub\Core\Model;

use NodePub\Core\Model\ToolbarItem;

/**
 * Holds the toolbar items and manages their order
 */
class Toolbar
{
    protected $items;

    function __construct()
    {
        $this->items = array();
    }

    function addItem(ToolbarItem $item)
    {
        $this->items[] $item;
    }

    /**
     * Returns array of active toolbar items
     * @return array
     */
    function getActiveItems()
    {
        return $this->items;
    }
}