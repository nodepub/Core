<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\ExtensionInterface;
use NodePub\Core\Extension\SnippetQueue;
use NodePub\Core\Extension\DomManipulator;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class ExtensionContainer extends \Pimple
{
    protected $extensions = array();
    protected $booted = false;

    public function __construct(array $values = array())
    {
        parent::__construct();

        $this['debug'] = false;
        $this['admin'] = false;
        $this['admin_content'] = '';

        $this['snippet_queue'] = $this->share(function() {
            return new SnippetQueue();
        });

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    }

    /**
     * Registers an extension.
     *
     * @param ExtensionInterface $extension An ExtensionInterface instance
     *
     * @return ExtensionContainer
     */
    public function register(ExtensionInterface $extension)
    {
        $this->extensions[$extension->getName()] = $extension;
        //$extension->register($this);

        return $this;
    }

    /**
     * Boots all extensions.
     */
    public function boot()
    {
        if (!$this->booted) {

            foreach ($this->extensions as $extension) {
                if ($this['admin']) {
                    $this['toolbar']->addItems($extension->getToolbarItems());
                }
            }

            $this->booted = true;
        }
    }

    /**
     * Aggregates admin content from all extensions and adds it to the snippet queue
     * for insertion right before the closing </body> tag.
     */
    public function prepareSnippets()
    {
        foreach ($this->extensions as $extension) {

            $this['snippet_queue']->add($extension->getSnippets());

            if ($this['admin']) {
                $this['admin_content'] .= $extension->getAdminContent();
            }
        }

        $this['snippet_queue']->insert(DomManipulator::END_BODY, $this['admin_content']);
    }

    public function getExtension($extensionName)
    {
        if (isset($this->extensions[$extensionName])) {
            return $this->extensions[$extensionName];
        }

        throw new \Exception("No extension found with name [$extensionName]");
    }

    // /**
    //  * Calls a method on each extension and collects the results in an array.
    //  *
    //  * @param string $method The method name
    //  * @return array The collected method returns
    //  */
    // public function collectMethodCalls($method)
    // {
    //     $results = array();
    //     foreach ($this->extensions as $extension) {
    //         if (method_exists($extension, $method)) {
    //             $results[]= call_user_func(array($extension, $method));
    //         }
    //     }

    //     if (is_string(reset($results))) {
    //         $results = implode('', $results);
    //     }

    //     return $results;
    // }
}