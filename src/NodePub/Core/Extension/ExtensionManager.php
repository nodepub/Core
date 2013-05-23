<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\ExtensionInterface;
use NodePub\Core\Extension\SnippetQueue;
use NodePub\Core\Extension\DomManipulator;

use Symfony\Component\HttpFoundation\Response;

class ExtensionManager
{
    protected $app;
    protected $extensions;
    protected $snippetQueue;
    protected $booted;
    protected $adminEnabled;
    
    function __construct(Application $app)
    {
        $this->app = $app;
        $this->extensions = array();
        $this->snippetQueue = new SnippetQueue();
        $this->booted = false;
    }

    /**
     * Registers an extension.
     *
     * @param ExtensionInterface $extension A ExtensionInterface instance
     *
     * @return ExtensionManager
     */
    public function register(ExtensionInterface $extension, array $config = array())
    {
        //$extension->register($this->app, $config);
        $this->extensions[$extension->getName()] = $extension;

        return $this;
    }

    /**
     * Boots all extensions.
     */
    public function boot()
    {
        if (!$this->booted) {
            foreach ($this->extensions as $extension) {
                $this->snippetQueue->add($extension->getSnippets());
                $this->activateToolbarItems($extension);
            }

            $this->booted = true;
        }
    }

    protected function activateToolbarItems(ExtensionInterface $extension)
    {
        foreach ($extension->getToolbarItems() as $item) {
            $this->app['np.admin.toolbar']->addItem($item);
        }
    }

    public function installResorces()
    {
        foreach ($this->extensions as $extension) {

            $extension = $this->getExtension($extensionName);

            foreach ($extension->getResourceManifest() as $resourcePath) {
                $file = $extension->getResourceDirectory().$resourcePath;
                if (file_exists($file)) {
                    symlink($this->app['np.web_dir'].'/npub/'.$resourcePath, realpath($file));
                }
            }
        }
    }

    public function uninstallResorces($extensionName)
    {

        $extension = $this->getExtension($extensionName);

        foreach ($extension->getResourceManifest() as $resourcePath) {
        }
    }

    public function getExtension($extensionName)
    {
        if (isset($this->extensions[$extensionName])) {
            return $this->extensions[$extensionName];
        }

        throw new \Exception("No extension found with name [$extensionName]");
    }

    public function aggregateAdminContent()
    {

        $adminContent = '';

        if (true === $this->adminEnabled) {
            $adminContent = $this->aggregateMethods('getAdminContent');
        }

        return $adminContent;
    }

    public function aggregateMethods($method)
    {
        $results = array();
        foreach ($this->extensions as $extension) {
            if (method_exists($extension, $method)) {
                $results[]= call_user_func(array($extension, $method));
            }
        }

        if (is_string(reset($results))) {
            $results = implode('', $results);
        }

        return $results;
    }

    public function prepareAdminContent()
    {
        $this->snippetQueue->insert(DomManipulator::END_BODY, $this->aggregateAdminContent());
    }

    public function processSnippets(Response $response)
    {
        foreach ($this->extensions as $extension) {
            $this->snippetQueue->add($extension->getSnippets());
        }

        $html = $this->snippetQueue->processAll($response->getContent());
    }
}