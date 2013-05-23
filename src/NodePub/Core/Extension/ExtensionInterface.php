<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\SnippetQueue;
use Silex\Application;

/**
 * Interface that must implement all NodePub extensions.
 */
interface ExtensionInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getResourceDirectory();

    /**
     * @return array
     */
    public function getResourceManifest();

    /**
     * @return string
     */
    public function getAdminContent();

    /**
     * Returns the toolbar items to add to the admin toolbar.
     *
     * @return array An array of ToolbarItem instances
     */
    public function getToolbarItems();

    /**
     * @return array An array of BlockType instances
     */
    public function getBlockTypes();

    /**
     * @return array An array of Twig Functions
     */
    public function getTwigFunctions();

    /**
     * @return array An array of snippets
     */
    public function getSnippets();
}