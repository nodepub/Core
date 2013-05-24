<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\ExtensionInterface;
use NodePub\Core\Extension\SnippetQueue;
use NodePub\Core\Extension\DomManipulator;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class ExtensionManager
{
    protected $app;
    protected $extensions;
    protected $snippetQueue;
    protected $booted;
    
    function __construct(Application $app)
    {
        $this->app = $app;
        $this->extensions = array();
        $this->snippetQueue = new SnippetQueue($app);
        $this->booted = false;
    }

    /**
     * Registers an extension.
     *
     * @param ExtensionInterface $extension An ExtensionInterface instance
     *
     * @return ExtensionManager
     */
    public function register(ExtensionInterface $extension)
    {
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

    /**
     * Tests if admin features are enabled
     *
     * @return bool
     */
    protected function isAdminEnabled()
    {
        return (isset($this->app['np.admin']) && true === $this->app['np.admin']);
    }

    /**
     * Adds all toolbar items from all extensions to the application toolbar
     */
    protected function activateToolbarItems(ExtensionInterface $extension)
    {
        if ($this->isAdminEnabled() && isset($this->app['np.admin.toolbar'])) {
            foreach ($extension->getToolbarItems() as $item) {
                $this->app['np.admin.toolbar']->addItem($item);
            }
        }
    }

    /**
     * Symlinks all extension resources to the web root
     */
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

    /**
     * Removes an extension's resources from the web root.
     */
    public function uninstallResorces($extensionName)
    {
        $extension = $this->getExtension($extensionName);

        foreach ($extension->getResourceManifest() as $resourcePath) {
            unlink();
        }
    }

    public function getExtension($extensionName)
    {
        if (isset($this->extensions[$extensionName])) {
            return $this->extensions[$extensionName];
        }

        throw new \Exception("No extension found with name [$extensionName]");
    }

    public function collectAdminContent()
    {
        $adminContent = '';

        if ($this->isAdminEnabled()) {
            $adminContent = $this->collectMethodCalls('getAdminContent');
        }

        return $adminContent;
    }

    /**
     * Calls a method on each extension and collects the results in an array.
     *
     * @param string $method The method name
     * @return array The collected method returns
     */
    public function collectMethodCalls($method)
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

    /**
     * Aggregates admin content from all extensions and adds it to the snippet queue
     * for insertion right before the closing </body> tag.
     */
    public function prepareAdminContent()
    {
        $this->snippetQueue->insert(DomManipulator::END_BODY, $this->collectAdminContent());
    }

    /**
     * Adds all snippets to a queue and inserts them all into the response body.
     *
     * @param Response
     */
    public function insertSnippets(Response $response)
    {
        foreach ($this->extensions as $extension) {
            $this->snippetQueue->add($extension->getSnippets());
        }

        $html = $this->snippetQueue->processAll($response->getContent());
        $response->setContent($html);
    }
}