<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Extension\ExtensionManager;
use NodePub\Core\Extension\ThemeEngineExtension;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Extension implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        $routes = $this->registerRoutes($app['controller_factory']);
        if (isset($this->mountPoint)) {
            $app->mount($this->mountPoint, $routes);
        }

        $app->after(function(Request $request, Response $response) use ($app) {
            $this->prepareAdminContent();
            $this->insertSnippets($response);
        });
    }

    public function registerRoutes($controllerFactory) {
        return $controllerFactory;
    }
}
