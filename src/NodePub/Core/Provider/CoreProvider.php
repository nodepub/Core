<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use NodePub\Core\Yaml\YamlCollectionLoader;
use NodePub\Core\Form\Type\TextTagsType;

/**
 * Provides multisite configuration
 */
class CoreServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['config_dir'] = $app['app_dir'].'/config';
        $app['cache_dir']  = $app['app_dir'].'/_cache';
        $app['log_dir']    = $app['app_dir'].'/_logs';
        $app['web_dir']    = $app['app_dir'].'/../web';
        $app['host_name']  = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']: 'nodepub.dev';
        
        $app['np.homepage_route'] = 'blog_get_posts';
        
        $app['np.yaml_loader'] = $app->share(function($app) {
            return new YamlCollectionLoader($app['config_dir']);
        });
        
        # ===================================================== #
        #    SILEX SERVICE PROVIDERS                            #
        # ===================================================== #

        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $app->register(new \Silex\Provider\ServiceControllerServiceProvider());
        $app->register(new \Silex\Provider\SessionServiceProvider());
        $app->register(new \Silex\Provider\FormServiceProvider(), array(
            'form.secret' => md5('I call the big one Bitey')
        ));
        // Register custom form types
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new TextTagsType();
            return $extensions;
        }));
        $app->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'locale_fallback' => 'en',
        ));
        $app->register(new \Silex\Provider\MonologServiceProvider(), array(
            'monolog.name' => 'np',
            'monolog.logfile' => $app['log_dir'].'/dev.log',
        ));
        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.options'    => array(
                'autoescape' => false,
                'cache' => false
            )
        ));
        
        # ===================================================== #
        #    NP SERVICE PROVIDERS                               #
        # ===================================================== #

        $app->register(new AdminDashboardServiceProvider());
        $app->register(new ExtensionServiceProvider());
        $app->register(new SiteServiceProvider());
    }

    public function boot(Application $app)
    {
        # ===================================================== #
        #    ROUTES                                             #
        # ===================================================== #

        $app->get('/', function() use ($app) {
            return $app->redirect($app['url_generator']->generate($app['np.homepage_route']));
        });

        $app->error(function (\Exception $e, $code) use ($app) {
            if ($app['debug']) {
                return;
            }

            switch ($code) {
                case 404:
                    $message = 'The requested page could not be found.';
                    break;
                default:
                    $message = 'We are sorry, but something went terribly wrong.';
            }

            return new Symfony\Component\HttpFoundation\Response($message);
        });
    }
}
