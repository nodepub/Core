<?php

namespace NodePub\Core\Routing;

use Silex\Application;
use Silex\ControllerProviderInterface;

class AdminRouting implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        // TODO this should be added dynamically only if not installed yet
        $controllers->get('/install', 'np.admin.controller:installAction')
            ->bind('admin_install');

        $controllers->get('/toolbar', 'np.admin.controller:toolbarAction')
            ->bind('admin_toolbar');

        $controllers->get('/settings', 'np.admin.controller:settingsAction')
            ->bind('admin_settings');

        $controllers->get('/sitemap', 'np.admin.controller:sitemapAction')
            ->bind('admin_sitemap');

        $controllers->get('/users', 'np.admin.controller:usersAction')
            ->bind('admin_users');

        $controllers->get('/users/{username}', 'np.admin.controller:userAction')
            ->bind('admin_user');

        $controllers->get('/logs', 'np.admin.controller:logAction')
            ->bind('admin_logs');

        $controllers->get('/stats', 'np.admin.controller:statsAction')
            ->bind('admin_stats');

        $controllers->get('/cache', 'np.admin.controller:cacheAction')
            ->bind('admin_cache');

        $controllers->post('/clear-cache/{all}', 'np.admin.controller:postClearCacheAction')
            ->value('all', 'no')
            ->bind('admin_clear_cache');

        $controllers->get('/js/require', 'np.admin.controller:javaScriptsAction')
            ->bind('admin_javascripts');

        return $controllers;
    }
}