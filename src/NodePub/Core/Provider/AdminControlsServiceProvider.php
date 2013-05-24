<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Model\Toolbar;
use NodePub\Core\Model\ToolbarItem;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service Provider for Silex integration
 */
class AdminControlsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // display admin controlls
        $app['np.admin'] = $app->share(function($app) {
            return (isset($app['security']) && true === $app['security']->isGranted('ROLE_ADMIN'));
        });

        // base url for all admin routes
        $app['np.admin.mount_point'] = '/np-admin';

        // active toolbar items
        $app['np.admin.toolbar'] = $app->share(function($app) {

            $toolbar = new Toolbar();

            $toolbar
                ->addItem(new ToolbarItem('Dashboard', 'admin_dashboard'))
                ->addItem(new ToolbarItem('Sites', 'admin_sites'))
                ->addItem(new ToolbarItem('Nodes', 'admin_sitemap'))
                ->addItem(new ToolbarItem('Users', 'admin_users'))
                ->addItem(new ToolbarItem('Cache', 'admin_clear_cache'));

            return $toolbar;
        });
    }

    public function boot(Application $app)
    {
        // $app->before(function() use ($app) {

        //     $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
        //         // $twig->addGlobal('themes_path', '/themes/');
        //         // $twig->addGlobal('active_theme', $app['np.theme.active']);
        //         // $twig->addGlobal('standalone', true); // TODO: this will determine if we're in the larger NP app
        //         // $twig->addExtension(new ThemeTwigExtension(
        //         //     $app['np.theme.manager'],
        //         //     $app['np.theme.templates.custom_css'],
        //         //     $app['np.theme.minify_assets']
        //         // ));

        //         return $twig;
        //     }));

        //     $app['twig.loader.filesystem']->addPath(__DIR__.'/../Resources/views', 'np_admin');
        // });

        $app->after(function(Request $request, Response $response) use ($app) {
            # Inject the theme switcher form onto the page
            # if 'theme_preview' is set in the session
            if ($app['np.admin'] === true) {


            }
        });
    }
}
