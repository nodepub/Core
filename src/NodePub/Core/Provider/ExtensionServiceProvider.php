<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Extension\ExtensionContainer;
use NodePub\Core\Extension\ThemeEngineExtension;
use NodePub\Core\Extension\DomManipulator;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtensionServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.extensions'] = $app->share(function($app) {
            return new ExtensionContainer(array(
                'admin' => $app['np.admin'],
                'toolbar' => $app['np.admin.toolbar']
            ));
        });

        $app['np.extensions'] = $app->share($app->extend('np.extensions', function($extensions, $app) {
            $extensions->register(new ThemeEngineExtension($app));
            return $extensions;
        }));
    }

    public function boot(Application $app)
    {
        $app['np.extensions']->boot();

        $app->after(function(Request $request, Response $response) use ($app) {

            $app['np.extensions']->prepareSnippets();

            $response->setContent(
                $app['np.extensions']['snippet_queue']->processAll($app, $response->getContent())
            );
        });
    }
}
