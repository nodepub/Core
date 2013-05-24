<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Extension\ExtensionManager;
use NodePub\Core\Extension\ThemeEngineExtension;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtensionServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.extension_manager'] = $app->share(function($app) {
            $manager = new ExtensionManager($app);
            return $manager;
        });

        $app['np.extensions'] = $app->share(function($app) {
            return array(
                new ThemeEngineExtension($app),
            );
        });
    }

    public function boot(Application $app)
    {
        foreach ($app['np.extensions'] as $extension) {
            $app['np.extension_manager']->register($extension);
        }

        $app['np.extension_manager']->boot();

        $app->after(function(Request $request, Response $response) use ($app) {
            $app['np.extension_manager']->prepareAdminContent();
            $app['np.extension_manager']->insertSnippets($response);
        });
    }
}
