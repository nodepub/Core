<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Controller\AdminController;
use NodePub\Core\Controller\DebugController;
use NodePub\Core\Routing\AdminRouting;
use NodePub\Core\Routing\DebugRouting;

use NodePub\Core\Model\Toolbar;
use NodePub\ThemeEngine\ThemeEvents;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\Event;

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

        // theme to use for the admin ui
        $app['np.admin.theme'] = 'np-admin';

        // factory for creating admin route uris
        $app['np.admin.route_prefix'] = function() use ($app) {
            return new AdminRoutePrefixFactory($app['np.admin.mount_point']);
        };

        // initialize empty toolbar,
        // extensions will register individual toolbar items
        $app['np.admin.toolbar'] = $app->share(function() {
            return new Toolbar();
        });

        $app['np.admin.controller'] = $app->share(function($app) {
            return new AdminController($app);
        });

        $app['np.debug.controller'] = $app->share(function($app) {
            return new DebugController($app);
        });
    }

    public function boot(Application $app)
    {
        if (false === $app['np.admin']) {
            return;
        }

        $app->before(function() use ($app) {

            $app['np.admin.theme'] = $app->share(function($app) {
                if (isset($app['np.theme.manager'])) {
                    $app['np.theme.manager']->get($app['np.admin.theme']);
                }
            });

        });

        // $app->on(ThemeEvents::THEME_MANAGER_INITIALIZED, function(Event $event) use ($app) {
        //     $app['np.admin.theme'] = $app->share(function($app) {
        //         if (isset($app['np.theme.manager'])
        //             && $theme = $app['np.theme.manager']->getTheme($app['np.admin.theme'])) {

        //             $javascripts = array();

        //             // add all extension js modules
        //             $resources = $app['np.extension_manager']->collectMethodCalls('getResourceManifest');
        //             foreach ($resources as $resource) {
        //                 if (0 === strpos($resource, '/js')) {
        //                     $javascripts[] = $resource;
        //                 }
        //             }

        //             $theme->addJavaScripts($javascripts);

        //             $app['np.theme.manager']->setTheme($theme);

        //             return $theme;
        //         }
        //     });
        // });

        # ===================================================== #
        #    ADMIN ROUTES                                       #
        # ===================================================== #

        $app->mount($app['np.admin.mount_point'], new AdminRouting());

        if ($app['debug']) {
            $app->mount($app['np.admin.mount_point'].'/debug', new DebugRouting());
        }
    }
}
