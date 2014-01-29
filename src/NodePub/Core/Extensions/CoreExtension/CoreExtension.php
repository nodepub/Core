<?php

namespace NodePub\Core\Extensions\CoreExtension;

use NodePub\Core\Extension\Extension;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class CoreExtension extends Extension
{
    public function isCore()
    {
        return true;
    }
    
    public function isInstalled()
    {
        return true;
    }
    
    public function isEnabled()
    {
        return true;
    }
    
    /**
     * Adds the main admin toolbar.
     */
    public function getAdminContent()
    {
        return $this->getToolbar();
    }

    /**
     * Fetches the rendered admin toolbar through a sub-request.
     *
     * @return string
     */
    protected function getToolbar()
    {
        $subRequest = Request::create($this->app['url_generator']->generate('admin_toolbar'));
        $subResponse = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

        return $subResponse->getContent();
    }
}