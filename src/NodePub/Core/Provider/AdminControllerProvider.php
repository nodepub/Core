<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;

class AdminControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'np.admin.controller:indexAction');

        // TODO this should be added dynamically only if not installed yet
        $controllers->get('/install', 'np.admin.controller:installAction')
            ->bind('admin_install');

        if ($app['debug']) {
            $controllers->get('/debug', 'np.admin.controller:debugAction');
        }

        $controllers->get('/toolbar', 'np.admin.controller:toolbarAction')
            ->bind('admin_toolbar');

        $controllers->get('/dashboard', 'np.admin.controller:dashboardAction')
            ->bind('admin_dashboard');

        $controllers->get('/settings', 'np.admin.controller:settingsAction')
            ->bind('admin_settings');

        $controllers->get('/js/require', 'np.admin.controller:javaScriptsAction')
            ->bind('admin_javascripts');

        $controllers->get('/users', 'np.admin.controller:usersAction')
            ->bind('admin_users');

        $controllers->get('/users/{username}', 'np.admin.controller:userAction')
            ->bind('admin_user');

        $controllers->get('/logs', 'np.admin.controller:logAction')
            ->bind('admin_logs');

        $controllers->get('/stats', 'np.admin.controller:statsAction')
            ->bind('admin_stats');

        $controllers->get('/sitemap', 'np.admin.controller:sitemapAction')
            ->bind('admin_sitemap');

        $controllers->post('/clear-cache/{all}', 'np.admin.controller:clearCacheAction')
            ->value('all', 'no')
            ->bind('admin_clear_cache');

        $controllers->get('/test-email', 'np.admin.controller:testEmailAction');

        return $controllers;
    }
}