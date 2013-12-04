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
        // display admin controls, default to false
        $app['np.admin'] = false;
        
        // base url for all admin routes (including login, so it's always created)
        $app['np.admin.controllers.prefix'] = $app->share(function($app) {
            return isset($app['np.app_config']['admin']['uri']) 
                ? $app['np.app_config']['admin']['uri']
                : ApplicationConfiguration::DEFAULT_ADMIN_URI;
        });
        
        // factory for creating mount points that have the same prefix prepended
        $app['np.admin.controller.prefix_factory'] = $app->share(function($app) {
            return new RoutePrefixFactory($app['np.admin.controllers.prefix']);
        });
        
        // all np-admin controllers will mount to separate controller group,
        // which is them mounted to at the admin prefix on boot
        $app['np.admin.controllers'] = $app->share(function($app) {
            return $app['controllers_factory'];
        });
    }

    public function boot(Application $app)
    {
        // Define login route at admin root, no slash
        $app->get($app['np.admin.controllers.prefix'], 'np.admin.controller:loginAction')
            ->bind('np_login');
        
        // Mount all the admin controllers
        $app->mount($app['np.admin.controllers.prefix'], $app['np.admin.controllers']);
        
        // If logged in as admin, display admin controls
        $app->before(function() use ($app) {
            $app['np.admin'] = ($app['security']->isGranted('ROLE_ADMIN'));
        });
    }
}
