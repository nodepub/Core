<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use NodePub\Core\Yaml\YamlCollectionLoader;
use NodePub\Core\Form\Type\TextTagsType;
use NodePub\ThemeEngine\Provider\ThemeServiceProvider;

/**
 * Initializes other core Service Providers -- those built-in to Silex,
 * as well as the ones specific to NodePub.
 */
class CoreServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['config_dir'] = $app['app_dir'].'/config';
        $app['cache_dir']  = $app['app_dir'].'/_cache';
        $app['log_dir']    = $app['app_dir'].'/_logs';
        $app['web_dir']    = $app['app_dir'].'/../web';
        $app['np.homepage_route'] = 'blog_get_posts';
        
        $app['host_name'] = $app->share(function($app) {
            
            // need a fallback for when running from cli (tests, etc.)
            $hostName = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']: 'nodepub.dev';
            
            // If local development is running on a .dev hostname
            // automatically set debug mode, but to avoid having different local configs
            // set the name back to what it will be in production (defaults to .com)
            if (array_pop(explode('.', $app['host_name'])) == 'dev') {
                $app['debug'] = true;
                $hostName = str_replace('.dev', '.com', $hostName);
            }
            
            return $hostName;
        });
        
        $app['np.yaml_loader'] = $app->share(function($app) {
            $loader = new YamlCollectionLoader($app['config_dir']);
            if ($app['debug'] && is_dir($app['app_dir'].'/stubs')) {
                $loader->addSource($app['app_dir'].'/stubs');
            }
            return $loader;
        });
        
        # ===================================================== #
        #    SILEX SERVICE PROVIDERS                            #
        # ===================================================== #

        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $app->register(new \Silex\Provider\ServiceControllerServiceProvider());
        $app->register(new \Silex\Provider\SessionServiceProvider());
        $app->register(new \Silex\Provider\FormServiceProvider(), array(
            'form.secret' => md5('This needs to be configured!')
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
                'cache' => !$app['debug'],
            )
        ));
        
        # ===================================================== #
        #    NP SERVICE PROVIDERS                               #
        # ===================================================== #

        $app->register(new AdminDashboardServiceProvider());
        $app->register(new ExtensionServiceProvider());
        $app->register(new SiteServiceProvider());
        $app->register(new ThemeServiceProvider());
    }

    public function boot(Application $app)
    {
        # ===================================================== #
        #    DEFAULT ROUTES                                     #
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
