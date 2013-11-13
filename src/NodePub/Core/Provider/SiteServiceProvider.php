<?php

namespace NodePub\Core\Provider;

use Silex\Application;

use NodePub\Core\Provider\BaseServiceProvider;
use NodePub\Core\Controller\SiteController;
use NodePub\Core\Model\SiteCollection;
use NodePub\Common\Yaml\YamlConfigurationProvider;

/**
 * Provides multisite configuration
 */
class SiteServiceProvider extends BaseServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);
        
        $app['np.sites.debug'] = false;
        $app['np.sites.mount_point'] = '/sites';
        $app['np.sites.config_file'] = $app['config_dir'].'/sites.yml';

        // Loading from a yaml file for now, would like to be able to keep yaml site configuration
        // or have it all in the db for editing, which will override np.site.provider with a db entity manager
        $app['np.sites.provider'] = $app->share(function($app) {
            $sitesProvider = new SiteCollection();
            $sitesProvider->addSitesFromConfig($app['np.yaml_loader']->load($app['np.sites.config_file']));
        });

        $app['np.sites'] = $app->share(function($app) {
            return $app['np.sites.provider']->getAll();
        });

        $app['np.sites.active_site'] = $app->share(function($app) {

            if (!$site = $app['np.sites.provider']->getByHostName($app['host_name'])) {
                throw new \Exception("No site configured for host {$hostName}", 500);
            }

            return $site;
        });
    }
    
    public function registerAdmin(Application $app)
    {
        $app['np.sites.controller'] = $app->share(function($app) {
            return new SiteController($app);
        });
    }

    public function boot(Application $app)
    {
        parent::boot($app);
        
        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            $twig->addGlobal('site', $app['np.sites.active_site']);
            return $twig;
        }));
    }
    
    public function bootAdmin(Application $app)
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
