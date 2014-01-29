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
    public function getNamespace();
    
    /**
     * @return string
     */
    public function getPath();

    /**
     * Returns an array of the extension's public asset paths.
     * @return array
     */
    public function getAssets();

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
     * @return array An array of Twig Extension instances
     */
    public function getTwigExtensions();

    /**
     * @return array An array of snippets
     */
    public function getSnippets();
}