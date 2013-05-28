<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Controller\AdminController;
use NodePub\Core\Model\Toolbar;
use NodePub\ThemeEngine\ThemeEvents;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\Event;


/**
 * Service Provider that registers admin settings and dashboard objects
 */
class AdminDashboardServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // display admin controlls
        $app['np.admin'] = $app->share(function($app) {
            return (isset($app['security']) && true === $app['security']->isGranted('ROLE_ADMIN'));
        });

        // base url for all admin routes
        $app['np.admin.mount_point'] = '/np-admin';

        // theme to use for the admin ui
        $app['np.admin.theme'] = 'np-admin';

        // initialize empty toolbar,
        // extensions will register individual toolbar items
        $app['np.admin.toolbar'] = $app->share(function() {
            return new Toolbar();
        });

        $app['np.admin.controller'] = $app->share(function($app) {
            return new AdminController($app);
        });
    }

    public function boot(Application $app)
    {
        if (false === $app['np.admin']) {
            return;
        }

        $app->before(function() use ($app) {
            // $app['twig.loader.filesystem']->addPath(__DIR__.'/../Resources/views', 'core');

            $app['np.admin.theme'] = $app->share(function($app) {
                if (isset($app['np.theme.manager'])) {
                    $app['np.theme.manager']->get($app['np.admin.theme']);
                }
            });

        });

        $app->on(ThemeEvents::THEME_MANAGER_INITIALIZED, function(Event $event) use ($app) {
            $app['np.admin.theme'] = $app->share(function($app) {
                if (isset($app['np.theme.manager'])
                    && $theme = $app['np.theme.manager']->getTheme($app['np.admin.theme'])) {

                    $javascripts = array();

                    // add all extension js modules
                    $resources = $app['np.extension_manager']->collectMethodCalls('getResourceManifest');
                    foreach ($resources as $resource) {
                        if (0 === strpos($resource, '/js')) {
                            $javascripts[] = $resource;
                        }
                    }

                    $theme->addJavaScripts($javascripts);

                    $app['np.theme.manager']->setTheme($theme);

                    return $theme;
                }
            });
        });

        # ===================================================== #
        #    ADMIN ROUTES                                       #
        # ===================================================== #

        $admin = $app['controllers_factory'];

        $admin->get('/', 'np.admin.controller::indexAction');

        // TODO this should be added dynamically only if not installed yet
        $admin->get('/install', 'np.admin.controller:installAction')
            ->bind('admin_install');

        $admin->get('/toolbar', 'np.admin.controller:toolbarAction')
            ->bind('admin_toolbar');

        $admin->get('/settings/more', 'np.admin.controller:moreSettingsAction')
            ->bind('admin_more_settings');

        $admin->get('/js/require', 'np.admin.controller:javaScriptsAction')
            ->bind('admin_javascripts');

        $admin->get('/dashboard', 'np.admin.controller:dashboardAction')
            ->bind('admin_dashboard');

        $admin->get('/users', 'np.admin.controller:usersAction')
            ->bind('admin_users');

        $admin->get('/users/{username}', 'np.admin.controller:userAction')
            ->bind('admin_user');

        $admin->get('/logs', 'np.admin.controller:logAction')
            ->bind('admin_logs');

        $admin->get('/stats', 'np.admin.controller:statsAction')
            ->bind('admin_stats');

        $admin->get('/sitemap', 'np.admin.controller:sitemapAction')
            ->bind('admin_sitemap');

        $admin->post('/clear-cache/{all}', 'np.admin.controller:clearCacheAction')
            ->value('all', 'no')
            ->bind('admin_clear_cache');

        $admin->get('/test-email', 'np.admin.controller:testEmailAction');

        # ===================================================== #
        #    ADMIN - NODE EDITING ROUTES                        #
        # ===================================================== #

        // $admin->get('/nodes/new', 'NodePub\Core\Controller\NodeController::newNodeAction')
        //     ->bind('admin_new_node');

        // $admin->get('/nodes/{id}/edit', 'NodePub\Core\Controller\NodeController::editNodeAction')
        //     ->bind('admin_edit_node');

        // $admin->post('/nodes/{id}/edit', 'NodePub\Core\Controller\NodeController::editNodeAction')
        //     ->bind('admin_update_node');

        // $admin->post('/nodes', 'NodePub\Core\Controller\NodeController::postNodesAction')
        //     ->bind('admin_post_nodes');

        # ===================================================== #
        #    ADMIN - BLOCK EDITING ROUTES                       #
        # ===================================================== #

        // $admin->get('/blocks', 'NodePub\Core\Controller\AdminController::blocksAction')
        //     ->bind('admin_blocks');

        // $admin->get('/blocks/{namespace}/new', 'NodePub\Core\Controller\NodeBlockController::newBlockAction')
        //     ->bind('admin_new_block');

        // $admin->post('/blocks', 'NodePub\Core\Controller\NodeBlockController::postBlocksAction')
        //     ->bind('admin_post_blocks');

        // $admin->get('/blocks/{id}/edit', 'NodePub\Core\Controller\NodeBlockController::editBlockAction')
        //     ->bind('admin_edit_block');

        // $admin->get('/blocks/{id}/update', 'NodePub\Core\Controller\NodeBlockController::editBlockAction')
        //     ->bind('admin_update_block');

        // $admin->get('/blocks/{id}/delete', 'NodePub\Core\Controller\NodeBlockController::deleteBlockAction')
        //     ->bind('admin_delete_block');

        // // TODO: change to post
        // $admin->get('/blocks/install/{blockNamespace}', 'NodePub\Core\Controller\AdminController::installBlockAction')
        //     ->bind('admin_block_install');

        $app->mount($app['np.admin.mount_point'], $admin);
    }
}
