<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use NodePub\Core\Extension\ExtensionContainer;
use NodePub\Core\Extension\DomManipulator;
use NodePub\Core\Routing\ExtensionRouting;
use NodePub\Core\Controller\ExtensionController;

class ExtensionServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.extensions'] = $app->share(function($app) {
            return new ExtensionContainer(array(
                'toolbar' => $app['np.admin.toolbar']
            ));
        });
        
        $app['np.extensions.mount_point'] = '/extensions';

        $app['np.extensions.controller'] = $app->share(function($app) {
            return new ExtensionController($app, $app['np.extensions']);
        });
        
        $app['np.admin.controllers'] = $app->share($app->extend('np.admin.controllers', function($adminControllers, $app) {
            $extensionControllers = new ExtensionRouting();
            $extensionControllers = $extensionControllers->connect($app);
            $adminControllers->mount($app['np.extensions.mount_point'], $extensionControllers);
            
            return $adminControllers;
        }));
    }

    public function boot(Application $app)
    {
        $app->before(function() use ($app) {
            $app['np.extensions']['debug'] = $app['debug'];
            $app['np.extensions']['admin'] = $app['np.admin'];
            $app['np.extensions']->boot();
        });

        $app->after(function(Request $request, Response $response) use ($app) {

            $app['np.extensions']->prepareSnippets();

            $response->setContent(
                $app['np.extensions']['snippet_queue']->processAll($app, $response->getContent())
            );
        });
    }
}