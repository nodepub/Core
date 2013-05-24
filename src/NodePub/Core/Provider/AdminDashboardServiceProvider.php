<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Model\Toolbar;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service Provider that registers admin settings and dashboard objects
 */
class AdminDashboardServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // display admin controlls
        $app['np.admin'] = $app->share(function($app) {
            return (isset($app['security']) && true === $app['security']->isGranted('ROLE_ADMIN'));
        });

        // base url for all admin routes
        $app['np.admin.mount_point'] = '/np-admin';

        // initialize empty toolbar,
        // extensions will register individual toolbar items
        $app['np.admin.toolbar'] = $app->share(function() {
            return new Toolbar();
        });
    }

    public function boot(Application $app)
    {
        $app->before(function() use ($app) {
            $app['twig.loader.filesystem']->addPath(__DIR__.'/../Resources/views', 'core');
        });
    }
}
