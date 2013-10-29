<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Provider\BaseServiceProvider;
use NodePub\Core\Controller\BlogAdminController;
use NodePub\Core\Routing\BlogAdminRouting;
use NodePub\BlogEngine\PostManager;

use Silex\Application;

/**
 * Service Provider that registers blog admin settings and routes
 */
class BlogAdminServiceProvider extends BaseServiceProvider
{
    public function registerAdmin(Application $app)
    {
        $app['np.blog_admin.mount_point'] = '/blog';
        $app['np.blog_admin.post_limit'] = 15;
        $app['np.blog_admin.drafts_dir'] = '';

        // Create new instance of post manager only for drafts
        $app['np.blog_admin.draft_manager'] = $app->share(function($app) {
            return new PostManager($app['np.blog_admin.drafts_dir']);
        });

        $app['np.blog_admin.controller'] = $app->share(function($app) {
            return new BlogAdminController($app, $app['blog.post_manager']);
        });

        $app['np.blog_admin.draft_controller'] = $app->share(function($app) {
            return new BlogAdminController($app, $app['np.blog_admin.draft_manager'] );
        });
    }

    public function bootAdmin(Application $app)
    {
        # ===================================================== #
        #    BLOG ADMIN ROUTES                                  #
        # ===================================================== #

        $app->mount(
            $app['np.admin.route_prefix']->create($app['np.blog_admin.mount_point']),
            new BlogAdminRouting()
        );

        //$app->mount($app['np.blog_admin.mount_point'].'/drafts', new BlogAdminRouting());

        // create an index shortcut
        $app->get(
            $app['np.admin.route_prefix']->create($app['np.blog_admin.mount_point']),
            'np.blog_admin.controller:dashboardAction'
        )->bind('admin_blog');
    }
}
