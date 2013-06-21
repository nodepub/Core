<?php

namespace NodePub\Core\Routing;

use Silex\Application;
use Silex\ControllerProviderInterface;

class DebugRouting implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'np.debug.controller:indexAction');

        $controllers->get('/test-email', 'np.debug.controller:testEmailAction');

        return $controllers;
    }
}