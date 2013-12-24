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
            
            $slugifyFunction =  new \Twig_SimpleFunction('slugify', function($arg) {
                $slugify = new Slugify();
                return $slugify->slugify($arg);
            });
            $twig->addFunction($slugifyFunction);
            
            return $twig;
        }));
    }

    public function boot(Application $app)
    {
    }
}
