<?php

namespace NodePub\Core\Provider;

use NodePub\Core\ExtensionManager;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtensionServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.extensions'] = $app->share(function ($app) {

            $extensions = new ExtensionManager($app);

            return $extensions;
        });
    }

    public function boot(Application $app)
    {
        $app->after(function(Request $request, Response $response) use ($app) {
            $app['extensions']->injectAdminContent($response);
        });
    }
}
