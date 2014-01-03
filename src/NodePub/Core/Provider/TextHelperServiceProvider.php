<?php

namespace NodePub\Core\Provider;

use Cocur\Slugify\Slugify;
use dflydev\markdown\MarkdownParser;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service Provider that registers text helper objects
 */
class TextHelperServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.slug_helper'] = $app->share(function($app) {
            return new Slugify();
        });
        
        $app['np.markdown'] = $app->share(function($app) {
           return new MarkdownParser();
        });
    }

    public function boot(Application $app)
    {
    }
}
