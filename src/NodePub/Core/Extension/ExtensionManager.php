<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\ExtensionInterface;
use Symfony\Component\HttpFoundation\Response;

class ExtensionManager
{
    protected $app;
    protected $booted;
    protected $adminEnabled;
    protected $extensions;
    
    function __construct(Application $app)
    {
        $this->app = $app;
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
        $this->extensions[] = $extension;

        $extension->register($this->app, $config);

        return $this;
    }

    /**
     * Boots all extensions.
     */
    public function boot()
    {
        if (!$this->booted) {
            foreach ($this->extensions as $extension) {
                $extension->boot($this);
            }

            $this->booted = true;
        }
    }

    public function injectAdminContent(Response $response) {
        if (true === $this->adminEnabled) {
            $adminContent = '';
            foreach ($this->extensions as $extension) {
                $content.= $extension->registerAdminContent();
            }

            if (function_exists('mb_stripos')) {
                $posrFunction = 'mb_strripos';
                $substrFunction = 'mb_substr';
            } else {
                $posrFunction = 'strripos';
                $substrFunction = 'substr';
            }

            $content = $response->getContent();

            if (false !== $pos = $posrFunction($content, '</body>')) {
                $adminContent = "\n".str_replace("\n", '', $adminContent)."\n";
                $content = $substrFunction($content, 0, $pos).$adminContent.$substrFunction($content, $pos);
                $response->setContent($content);
            }
        }
    }
}