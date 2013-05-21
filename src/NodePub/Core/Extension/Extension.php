<?php

namespace NodePub\Core;

use NodePub\Core\Extension\ExtensionInterface;
use Silex\Application;

abstract class Extension implements ExtensionInterface
{
    protected $app;
    protected $config;

    public abstract function getName();

    public function register($app, array $config = array())
    {
        $this->app = $app;
        $this->config = $config;
    }

    public abstract function registerAdminContent();

    public abstract function registerDashboardComponents();

    public abstract function registerBlockTypes();

    public abstract function registerTwigFunctions();

    public abstract function registerSnippets();
}
