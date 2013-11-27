<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use NodePub\Common\Yaml\YamlCollectionLoader;
use NodePub\Core\Bootstraper;
use NodePub\Core\Config\ApplicationConfiguration;
use NodePub\Core\Event\ThemeActivateListener;
use NodePub\ThemeEngine\ThemeEvents;

use NodePub\Core\Provider\AdminDashboardServiceProvider;
use NodePub\Core\Provider\AdminRoutesServiceProvider;
use NodePub\Core\Provider\ExtensionServiceProvider;
use NodePub\Core\Provider\SiteServiceProvider;
use NodePub\ThemeEngine\Provider\ThemeServiceProvider;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * Initializes other core Service Providers -- those built-in to Silex,
 * as well as the ones specific to NodePub.
 */
class CoreServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.config_dir'] = $app['np.app_dir'].'/config';
        $app['np.cache_dir']  = $app['np.app_dir'].'/_cache';
        $app['np.log_dir']    = $app['np.app_dir'].'/_logs';
        $app['np.web_dir']    = $app['np.app_dir'].'/../web';
        $app['np.homepage_route'] = 'blog_get_posts';
        
        $app['np.host_name'] = $app->share(function($app) {
            
            // need a fallback for when running from cli (tests, etc.)
            $hostName = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']: 'nodepub.dev';
            
            // If local development is running on a .dev hostname
            // automatically set debug mode, but to avoid having different local configs
            // set the name back to what it will be in production (defaults to .com)
            if (array_pop(explode('.', $hostName)) == 'dev') {
                $app['debug'] = true;
                $hostName = str_replace('.dev', '.com', $hostName);
            }
            
            return $hostName;
        });
        
        $app['np.yaml_loader'] = $app->share(function($app) {
            $loader = new YamlCollectionLoader($app['np.config_dir']);
            if ($app['debug'] && is_dir($app['np.app_dir'].'/stubs')) {
                $loader->addSource($app['np.app_dir'].'/stubs');
            }
            return $loader;
        });
        
        $app['np.app_config'] = $app->share(function($app) {
            $processor = new Processor();
            $appConfig = new ApplicationConfiguration();
            
            return $processor->processConfiguration(
                $appConfig,
                array(Yaml::parse($app['np.config_dir'] . '/app.yml'))
            );
        });
        
        // display admin controlls
        $app['np.admin'] = $app->share(function($app) {
            return true; //(isset($app['security']) && true === $app['security']->isGranted('ROLE_ADMIN'));
        });
        
        $this->bootstrapSilex($app);
        $this->bootstrapNodePub($app);
    }
    
    public function bootstrapSilex(Application $app)
    {
        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $app->register(new \Silex\Provider\ServiceControllerServiceProvider());
        $app->register(new \Silex\Provider\SessionServiceProvider());
        $app->register(new \Silex\Provider\FormServiceProvider(), array(
            // ensure that each site gets a different hash
            'form.secret' => md5($app['np.host_name'] . $app['np.app_config']['form']['secret'])
        ));

        $app->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'locale_fallback' => 'en',
        ));
        $app->register(new \Silex\Provider\MonologServiceProvider(), array(
            'monolog.name' => 'np',
            'monolog.logfile' => $app['np.log_dir'].'/dev.log',
        ));
        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.options'    => array(
                'autoescape' => false,
                'cache' => !$app['debug'],
            )
        ));
    }
    
    public function bootstrapNodePub(Application $app)
    {
        $app->register(new AdminControllersServiceProvider());
        $app->register(new AdminDashboardServiceProvider());
        $app->register(new ExtensionServiceProvider());
        $app->register(new SiteServiceProvider());
        
        // These should be optional
        $app->register(new \NodePub\Cms\Provider\CmsServiceProvider());
        $app->register(new \NodePub\Cms\Provider\SitemapServiceProvider());
        $app->register(new \NodePub\Cms\Provider\DoctrineBootstrapServiceProvider());
        
        $app->register(new ThemeServiceProvider(), array(
            'np.theme.active' => $app['np.sites.active_site']->getTheme()
        ));
        
        // register extensions - this will eventually be configurable from the UI
        $app['np.extensions'] = $app->share($app->extend('np.extensions', function($extensions, $app) {
            $extensions->register(new \NodePub\Core\Extension\CoreExtension($app));
            $extensions->register(new \NodePub\Core\Extension\ThemeEngineExtension($app));
            $extensions->register(new \NodePub\Core\Extension\BlogEngineExtension($app));
            return $extensions;
        }));
    }

    public function boot(Application $app)
    {
        # ===================================================== #
        #    EVENT LISTENERS                                    #
        # ===================================================== #
        
        $themeActivateListener = new ThemeActivateListener($app);
        $app->on(ThemeEvents::THEME_ACTIVATE, array($themeActivateListener, 'onThemeActivate'));
        
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
