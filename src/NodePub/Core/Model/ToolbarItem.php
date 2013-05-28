<?php

namespace NodePub\Core\Model;

class ToolbarItem
{
    protected $name,
              $route,
              $icon,
              $isActive;

    function __construct($name, $route, $icon)
    {
        $this->name = $name;
        $this->route = $route;
        $this->icon = $icon;
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