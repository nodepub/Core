<?php

namespace NodePub/Core/Extension;

use Silex\Application;

/**
 * Interface that must implement all NodePub extensions.
 */
interface ExtensionInterface
{
    public function getName();

    public function register(Application $app, array $config = array());

    public function registerAdminContent();

    public function registerDashboardComponents();

    public function registerBlockTypes();

    public function registerTwigFunctions();

    public function registerSnippets();
}