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
            
            $slugifyFilter =  new \Twig_SimpleFilter('slugify', function($string) use ($app) {
                return $app['np.slugify']->slugify($string);
            });
            $twig->addFilter($slugifyFilter);
            
            return $twig;
        }));
    }

    public function boot(Application $app)
    {
    }
}
