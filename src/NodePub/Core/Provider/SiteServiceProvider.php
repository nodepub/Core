<?php

namespace NodePub\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use NodePub\Controller\SiteController;
use NodePub\Config\YamlConfigurationProvider;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides multisite configuration
 */
class SiteServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.sites'] = $app->share(function($app) {
            return $app['np.sites.provider']->getSites();
        });

        $app['np.sites.debug'] = false;
        $app['np.sites.site_class'] = 'NodePub\Model\Site';

        $app['np.sites.provider'] = $app->share(function($app) {
            return new YamlConfigurationProvider($app['np.sites.config_file']);
        });

        $app['np.sites.active'] = $app->share(function($app) {
            
            $hostName = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'example.com';

            if (array_pop(explode('.', $app['host_name'])) == 'dev') {
                $app['debug'] = true;
                $hostName = str_replace('.dev', '.com', $hostName);
            }

            return $app['np.sites.provider']->get($hostName);
        });

        $app['np.sites.controller'] = $app->share(function($app) {
            return new SiteController(
                $app
            );
        });
    }

    public function boot(Application $app)
    {
        $app->before(function() use ($app) {
        });

        $siteProvider = function($hostName) use($app) {
            if (!$site = $app['np.sites.provider']->get($hostName)) {
                throw new \Exception("Site not found", 404);
            }

            return $site;
        };

        # ===================================================== #
        #    ROUTES                                             #
        # ===================================================== #

        $siteControllers = $app['controllers_factory'];

        $siteControllers->get('/', 'np.sites.controller:sitesAction')
            ->bind('get_sites');

        $siteControllers->match('/{hostName}/settings', 'np.sites.controller:settingsAction')
            ->convert('hostName', $siteProvider)
            ->bind('site_settings');

        $app->mount($app['np.sites.mount_point'], $siteControllers);
    }
}
