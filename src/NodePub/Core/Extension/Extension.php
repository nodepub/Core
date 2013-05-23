<?php

namespace NodePub\Core;

use NodePub\Core\Extension\ExtensionInterface;
use Silex\Application;

abstract class Extension implements ExtensionInterface
{
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public abstract function getName();

    public abstract function getResourceDirectory();

    public function getResourceManifest() {
        return array();
    }

    public function getAdminContent() {
        return '';
    }

    public function getToolbarItems() {
        return array();
    }

    public function getBlockTypes() {
        return array();
    }

    public function getTwigFunctions() {
        return array();
    }

    public function getSnippets() {
        return array();
    }
}