<?php

namespace NodePub\Core\Model;

/**
 * Represents a single item in the NodePub admin toolbar
 * that has a name, icon (a Font Awesome class name), and an admin action.
 */
class ToolbarItem
{
    protected $name,
              $route,
              $icon,
              $isActive;

    /**
     * @param string $name
     * @param string $route
     * @param string $icon A Font Awesome icon class name
     */
    function __construct($name, $route, $icon)
    {
        $this->name = $name;
        $this->route = $route;
        $this->icon = $icon;
    }
    
    public static function factory(array $array)
    {
        if (isset($array['name'], $array['route'], $array['icon'])) {
            return new ToolbarItem($array['name'], $array['route'], $array['icon']);
        } else {
            throw new \Exception("ToolbarItem factory requires an array 'name', 'route', and 'icon' keys", 500);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSlug()
    {
        return strtolower(str_replace(' ', '', $this->name));
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function activate()
    {
        $this->isActive = true;
    }

    public function deactivate()
    {
        $this->isActive = false;
    }

    public function isActive()
    {
        return $this->isActive;
    }
}