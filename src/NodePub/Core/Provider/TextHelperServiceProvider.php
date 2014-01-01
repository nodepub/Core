<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Cocur\Slugify\Slugify;

/**
 * Service Provider that registers text helper objects and twig functions
 */
class TextHelperServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.slugify'] = $app->share(function($app) {
            return new Slugify();
        });
        
        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            $slugifyFilter = new \Twig_SimpleFilter('slugify', function($input) use ($app) {
                if (is_array($input)) {
                    foreach ($input as $key => $item) {
                        $input[$key] = $app['np.slugify']->slugify($item);
                    }
                    return $input;
                } else {
                    return $app['np.slugify']->slugify($input);
                }
            });
            $twig->addFilter($slugifyFilter);
            
            return $twig;
        }));
    }

    public function boot(Application $app)
    {
    }
}
