<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;

class DebugControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'np.debug.controller:indexAction');

        $controllers->get('/test-email', 'np.debug.controller:testEmailAction');

        return $controllers;
    }
}