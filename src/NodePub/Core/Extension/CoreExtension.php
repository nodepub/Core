<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\Extension;
use NodePub\Core\Model\ToolbarItem;

class CoreExtension extends Extension
{
    public function getName() {
        return 'NodePub Core';
    }

    public function getToolbarItems() {
        return array(
            new ToolbarItem('Dashboard', 'admin_dashboard'),
            new ToolbarItem('Sites', 'admin_sites'),
            new ToolbarItem('Pages', 'admin_sitemap'),
            new ToolbarItem('Users', 'admin_users'),
            new ToolbarItem('Cache', 'admin_clear_cache')
        );
    }

    /**
     * If theme_preview is in the session, adds the theme switcher form.
     */
    public function getAdminContent()
    {
        return $this->getDashboard();
    }

    /**
     * Fetches the rendered dashboard through a subrequest.
     *
     * @return string
     */
    protected function getDashboard()
    {
        $subRequest = Request::create($this->app['url_generator']->generate('admin_dashboard');
        $subResponse = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);;

        return $subResponse->getContent();
    }
}