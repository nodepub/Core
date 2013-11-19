<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\Event;

use NodePub\Core\Controller\AdminController;
use NodePub\Core\Controller\DebugController;
use NodePub\Core\Routing\AdminRouting;
use NodePub\Core\Routing\DebugRouting;
use NodePub\Core\Provider\RoutePrefixFactory;
use NodePub\Core\Twig\AdminTwigExtension;
use NodePub\Core\Model\Toolbar;
use NodePub\ThemeEngine\ThemeEvents;
use NodePub\Install\InstallerManager;

/**
 * Service Provider that registers admin settings and dashboard objects
 */
class AdminDashboardServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // theme to use for the admin ui
        $app['np.admin.theme'] = 'np-admin';
        
        // admin theme template
        $app['np.admin.template'] = '@np-admin/panel.twig';

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
        
        $app['np.installer'] = $app->share(function($app) {
            return new InstallerManager($app);
        });
        
        if ($app['debug'] && isset($app['np.admin']) && true === $app['np.admin']) {
            $app['np.admin.controllers'] = $app->share($app->extend('np.admin.controllers', function($controllers, $app) {
                
                $debugControllers = new DebugRouting();
                $debugControllers = $debugControllers->connect($app);
                
                $controllers->mount('/debug', $debugControllers);
                return $controllers;
            }));
        }
        
        if (isset($app['twig'])) {
            $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                $twig->addExtension(new AdminTwigExtension());
                return $twig;
            }));
        }
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
        
        // Because it's at the root admin prefix we mount it directly instead of adding it to the admin controllers
        $app->mount($app['np.admin.controllers.prefix'], new AdminRouting());
    }
}
