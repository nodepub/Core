<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Provider\BaseServiceProvider;
use NodePub\Core\Extension\ExtensionContainer;
use NodePub\Core\Extension\DomManipulator;
use NodePub\Core\Routing\ExtensionRouting;
use NodePub\Core\Controller\ExtensionController;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtensionServiceProvider extends BaseServiceProvider 
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['np.extensions'] = $app->share(function($app) {
            return new ExtensionContainer(array(
                'admin' => $app['np.admin'],
                'toolbar' => $app['np.admin.toolbar']
            ));
        });
    }

    public function registerAdmin(Application $app)
    {
        $app['np.extensions.mount_point'] = '/extensions';

        $app['np.extensions.controller'] = $app->share(function($app) {
            return new ExtensionController($app, $app['np.extensions']);
        });
    }

    public function boot(Application $app)
    {
        parent::boot($app);

        $app['np.extensions']->boot();

        $app->after(function(Request $request, Response $response) use ($app) {

            $app['np.extensions']->prepareSnippets();

            $response->setContent(
                $app['np.extensions']['snippet_queue']->processAll($app, $response->getContent())
            );
        });
    }

    public function bootAdmin(Application $app)
    {
        $app->mount(
            $app['np.admin.route_prefix']->create($app['np.extensions.mount_point']),
            new ExtensionRouting()
        );
    }
}
