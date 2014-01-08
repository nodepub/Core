<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use NodePub\Core\Controller\SiteController;
use NodePub\Core\Model\SiteCollection;
use NodePub\Core\Routing\SitesAdminRouting;
use NodePub\Common\Yaml\YamlConfigurationProvider;

/**
 * Provides multisite configuration
 */
class SiteServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {        
        $app['np.sites.debug'] = false;
        $app['np.sites.mount_point'] = '/sites';
        $app['np.sites.config_file'] = $app['np.config_dir'].'/sites.yml';
        $app['np.site.class'] = '\NodePub\Core\Model\Site';

        // Loading from a yaml file for now, would like to be able to keep yaml site configuration
        // or have it all in the db for editing, which will override np.site.provider with a db entity manager
        $app['np.sites.provider'] = $app->share(function($app) {
            $sitesProvider = new SiteCollection();
            $sitesProvider->addSitesFromConfig($app['np.yaml_loader']->load($app['np.sites.config_file']));
            return $sitesProvider;
        });

        $app['np.sites'] = $app->share(function($app) {
            return $app['np.sites.provider']->getAll();
        });
        
        $app['np.multisite'] = $app->share(function($app) {
            return (count($app['np.sites']) > 1);
        });

        $app['np.sites.active_site'] = $app->share(function($app) {

            if (!$site = $app['np.sites.provider']->getByHostName($app['np.host_name'])) {
                throw new \Exception("No site configured for host {$hostName}", 500);
            }

            return $site;
        });

        $app['np.sites.controller'] = $app->share(function($app) {
            return new SiteController($app);
        });
        
        $app['np.admin.controllers'] = $app->share($app->extend('np.admin.controllers', function($adminControllers, $app) {
            $siteControllers = new SitesAdminRouting();
            $siteControllers = $siteControllers->connect($app);
            $adminControllers->mount($app['np.sites.mount_point'], $siteControllers);
            
            return $adminControllers;
        }));
        
        // check if a typekit id is configured with the site
        // typekit is gated by domain, so it makes more sense to configure them per site rather than theme
        $app['np.typekit_id'] = $app->share(function($app) {
            return $app['np.sites.active_site']->getAttribute('typekit_id');
        });
    }

    public function boot(Application $app)
    {
        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            $twig->addGlobal('site', $app['np.sites.active_site']);
            return $twig;
        }));
    }
}
