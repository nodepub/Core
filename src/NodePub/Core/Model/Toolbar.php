<?php

namespace NodePub\Core\Model;

use NodePub\Core\Model\ToolbarItem;

/**
 * Holds a collection of ToolbarItem objects, and manages their order.
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
        $this->items[]= $item;
        return $this;
    }

    function addItems(array $items)
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
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