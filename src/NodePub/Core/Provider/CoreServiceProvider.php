<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use NodePub\Common\Yaml\YamlCollectionLoader;
use NodePub\Core\Form\Type\TextTagsType;
use NodePub\ThemeEngine\Provider\ThemeServiceProvider;
use Symfony\Component\EventDispatcher\Event;
use NodePub\ThemeEngine\ThemeEvents;

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
            'monolog.logfile' => $app['np.log_dir'].'/dev.log',
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
        $app->register(new ThemeServiceProvider(), array(
            'np.theme.active' => $app['np.sites.active_site']->getTheme()
        ));
    }

    public function boot(Application $app)
    {
        // Listen for theme activation and configure the relevant blog templates
        $app->on(ThemeEvents::THEME_ACTIVATE, function(Event $event) use ($app) {

            // We may want some kind of registry or ThemeTemplateResolver object
            // that uses theme's configuration to map its templates to common page types,
            // otherwise all themes have to use exact template names,
            // and there's no way to share a template for different page types, or fallback on a parent theme

            $theme = $event->getTheme();

            $name = $theme->getNamespace();
            
            // @TODO: $theme->getTemplates();
            // -define core page types,
            // each theme will define a template for each page type
            // default will be used for unknown types
    
            $app['np.blog.theme.options'] = array(
                'templates' => array(
                    'default'   => '@'.$name.'/blog_post.twig',
                    'frontpage' => '@'.$name.'/blog_index.twig',
                    'post'      => '@'.$name.'/blog_post.twig',
                    'tag_page'  => '@'.$name.'/blog_index.twig',
                    'category'  => '@'.$name.'/blog_index.twig',
                    'archive'   => '@'.$name.'/blog_archive.twig',
                )
            );

            $app['np.theme.templates.custom_css'] = '@'.$name.'/_styles.css.twig';

            // for standalone usage, use the theme layout
            // $app['np.admin.template'] = '@'.$name.'/layout.twig';
            // for full np app use panel
            $app['np.admin.template'] = '@np-admin/panel.twig';

            // set active theme's parent
            if ($parentName = $theme->getParentNamespace()) {
                if ($parent = $app['np.theme.manager']->getTheme($parentName)) {
                    $theme->setParent($parent);
                }
            }

            $theme->customize($app['np.theme.configuration_provider']->get($name));
        });
        
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
