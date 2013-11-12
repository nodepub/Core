<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use NodePub\Core\Controller\SiteController;
use NodePub\Common\Yaml\YamlConfigurationProvider;
use NodePub\Core\Model\SiteCollection;

/**
 * Provides multisite configuration
 */
class SiteServiceProvider implements ServiceProviderInterface 
{
    public function register(Application $app)
    {
        $app['np.sites.debug'] = false;
        $app['np.sites.mount_point'] = '/sites';
        $app['np.sites.config_file'] = $app['config_dir'].'/sites.yml';
        
        // don't remember what this was for - remove if not needed
        //$app['np.sites.site_class'] = 'NodePub\Core\Model\Site';

        $app['np.sites.provider'] = $app->share(function($app) {
            return new YamlConfigurationProvider($app['np.sites.config_file']);
            
            return new SiteCollection();
        });

        $app['np.sites'] = $app->share(function($app) {
            return $app['np.sites.provider']->getAll();
        });

        $app['np.sites.active'] = $app->share(function($app) {

            if (!$site = $app['np.sites.provider']->getByHostName($app['host_name'])) {
                throw new \Exception("No site configured for host {$hostName}", 500);
            }

            return $site;
        });

        $app['np.sites.controller'] = $app->share(function($app) {
            return new SiteController($app);
        });
    }

    public function boot(Application $app)
    {
        // Convert hostname into site object
        $siteConverter = function($hostName) use($app) {
            if (!$site = $app['np.sites.provider']->getByHostName($hostName)) {
                throw new \Exception("Site not found", 404);
            }

            return $site;
        };

        # ===================================================== #
        #    ROUTES                                             #
        # ===================================================== #

        $siteControllers = $app['controllers_factory'];

        $siteControllers->get('/', 'np.sites.controller:sitesAction')
            ->bind('admin_sites');

        $siteControllers->match('/{site}/settings', 'np.sites.controller:settingsAction')
            ->convert('site', $siteConverter)
            ->bind('admin_site');

        $siteControllers->post('/switch/{site}', 'np.sites.controller:switchSiteAction')
            ->convert('site', $siteConverter)
            ->bind('admin_sites_switch');

        $app->mount(
            $app['np.admin.route_prefix']->create($app['np.sites.mount_point']),
            $siteControllers
        );
    }
}
