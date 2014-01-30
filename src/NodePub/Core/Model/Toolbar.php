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

    public function addItem(ToolbarItem $item)
    {
        $this->items[$item->name]= $item;
        return $this;
    }

    public function addItems(array $items)
    {
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $item['name'] = $key;
                $item = ToolbarItem::factory($item);
            }
            
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Returns array of active toolbar items
     * @return array
     */
    public function getActiveItems()
    {
        return $this->items;
    }
    
    /**
     * Returns array of active toolbar item groups
     */
    public function getGroupedActiveItems($groupSize = 5)
    {
        return array_chunk($this->items, $groupSize);
    }
}