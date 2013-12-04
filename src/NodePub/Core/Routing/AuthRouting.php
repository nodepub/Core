<?php

namespace NodePub\Core\Routing;

use Silex\Application;
use Silex\ControllerProviderInterface;

class AuthRouting implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        // These actions don't really exist, as they are preceeded by the security service provider,
        // but we need to define them in order to generate them with our dynamic admin prefix
        
        $controllers->post('/authenticate', function() {
        })->bind('np_authenticate');
        
        $controllers->get('/logout', function() {
        })->bind('np_logout');

        return $controllers;
    }
}
