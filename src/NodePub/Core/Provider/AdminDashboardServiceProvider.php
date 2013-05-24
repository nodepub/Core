<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Model\Toolbar;
use Silex\Application;
use Silex\ServiceProviderInterface;

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

        // initialize empty toolbar,
        // extensions will register individual toolbar items
        $app['np.admin.toolbar'] = $app->share(function() {
            return new Toolbar();
        });
    }

    public function boot(Application $app)
    {
        $app->before(function() use ($app) {
            $app['twig.loader.filesystem']->addPath(__DIR__.'/../Resources/views', 'core');
        });


        # ===================================================== #
        #    ADMIN ROUTES                                       #
        # ===================================================== #

        $admin = $app['controllers_factory'];

        $admin->get('/', 'NodePub\Core\Controller\AdminController::indexAction');

        // TODO this should be added dynamically only if not installed yet
        $admin->get('/install', 'NodePub\Core\Controller\AdminController::installAction')
            ->bind('admin_install');

        $admin->get('/toolbar', 'NodePub\Core\Controller\AdminController::toolbarAction')
            ->bind('admin_toolbar');

        $admin->get('/dashboard', 'NodePub\Core\Controller\AdminController::dashboardAction')
            ->bind('admin_dashboard');

        $admin->get('/users', 'NodePub\Core\Controller\AdminController::usersAction')
            ->bind('admin_users');

        $admin->get('/users/{username}', 'NodePub\Core\Controller\AdminController::userAction')
            ->bind('admin_user');

        $admin->get('/logs', 'NodePub\Core\Controller\AdminController::logAction')
            ->bind('admin_logs');

        $admin->get('/stats', 'NodePub\Core\Controller\AdminController::statsAction')
            ->bind('admin_stats');

        $admin->get('/sitemap', 'NodePub\Core\Controller\AdminController::sitemapAction')
            ->bind('admin_sitemap');

        $admin->post('/clear-cache/{all}', 'NodePub\Core\Controller\AdminController::clearCacheAction')
            ->value('all', 'no')
            ->bind('admin_clear_cache');

        $admin->get('/test-email', 'NodePub\Core\Controller\AdminController::testEmailAction');

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
