<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

abstract class BaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        if (isset($app['np.admin']) && true === $app['np.admin']) {
            $this->registerAdmin($app);
        }
    }

    public abstract function registerAdmin(Application $app);

    public function boot(Application $app)
    {
        if (isset($app['np.admin']) && true === $app['np.admin']) {
            $this->bootAdmin($app);
        }
    }

    public abstract function bootAdmin(Application $app);
}
