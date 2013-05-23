<?php

namespace NodePub\Core\Model;

class ToolbarItem
{
    protected $name,
              $route,
              $isActive;

    function __construct($name, $route)
    {
        $this->name = $name;
        $this->route = $route;
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