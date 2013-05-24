<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\Extension;
use NodePub\Core\Model\ToolbarItem;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class CoreExtension extends Extension
{
    public function getName() {
        return 'NodePub Core';
    }

    public function getResourceDirectory() {
        return '';
    }

    public function getToolbarItems() {
        return array(
            new ToolbarItem('Dashboard', 'admin_dashboard'),
            //new ToolbarItem('Sites', 'admin_sites'),
            new ToolbarItem('Pages', 'admin_sitemap'),
            new ToolbarItem('Users', 'admin_users'),
            new ToolbarItem('Cache', 'admin_clear_cache')
        );
    }

    /**
     * Adds the main admin toolbar.
     */
    public function getAdminContent()
    {
        return $this->getToolbar();
    }

    /**
     * Fetches the rendered admin toolbar through a subrequest.
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