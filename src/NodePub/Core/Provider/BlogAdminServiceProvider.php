<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use NodePub\Core\Controller\BlogAdminController;
use NodePub\Core\Routing\BlogAdminRouting;
use NodePub\BlogEngine\PostManager;

/**
 * Service Provider that registers blog admin settings and routes
 */
class BlogAdminServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.blog_admin.mount_point'] = '/blog';
        $app['np.blog_admin.post_limit'] = 15;
        $app['np.blog_admin.drafts_dir'] = '';

        // Create new instance of post manager only for drafts
        $app['np.blog_admin.draft_manager'] = $app->share(function($app) {
            return new PostManager($app['np.blog_admin.drafts_dir']);
        });

        $app['np.blog_admin.controller'] = $app->share(function($app) {
            return new BlogAdminController($app, $app['np.blog.post_manager']);
        });

        $app['np.blog_admin.draft_controller'] = $app->share(function($app) {
            return new BlogAdminController($app, $app['np.blog_admin.draft_manager'] );
        });

        $app['np.admin.controllers'] = $app->share($app->extend('np.admin.controllers', function($adminControllers, $app) {
            $blogControllers = new BlogAdminRouting();
            $blogControllers = $blogControllers->connect($app);
            $adminControllers->mount($app['np.blog_admin.mount_point'], $blogControllers);
            
            return $adminControllers;
        }));
    }

    public function boot(Application $app)
    {
        // Create an index shortcut
        $app->get(
            $app['np.admin.controller.prefix_factory']->create($app['np.blog_admin.mount_point']),
            'np.blog_admin.controller:dashboardAction'
        )->bind('admin_blog');
    }
}
