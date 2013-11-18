<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
//use Symfony\Component\Routing\RouteCollection;

//use NodePub\Core\Provider\RoutePrefixFactory;
use NodePub\Core\Config\ApplicationConfiguration;

/**
 * Service Provider that registers admin routes
 */
class AdminControllersServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        if (isset($app['np.admin']) && true === $app['np.admin']) {

            $app['np.admin.controllers'] = $app->share(function($app) {
                return $app['controllers_factory'];
            });

            // base url for all admin routes
            $app['np.admin.controllers.prefix'] = $app->share(function($app) {
                return isset($app['np.app_config']['admin']['uri']) 
                    ? $app['np.app_config']['admin']['uri']
                    : ApplicationConfiguration::DEFAULT_ADMIN_URI;
            });
            
            // factory for creating mount points that have the same prefix prepended
            $app['np.admin.controller.prefix_factory'] = $app->share(function($app) {
                return new RoutePrefixFactory($app['np.admin.controllers.prefix']);
            });
        }
    }

    public function boot(Application $app)
    {
        if (isset($app['np.admin']) && true === $app['np.admin']) {
            $app->mount($app['np.admin.controllers.prefix'], $app['np.admin.controllers']);
        }
    }
}
