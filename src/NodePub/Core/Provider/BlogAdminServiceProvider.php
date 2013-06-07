<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Controller\BlogAdminController;
use NodePub\Core\Provider\BlogAdminControllerProvider;
use NodePub\BlogEngine\PostManager;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service Provider that registers blog admin settings and routes
 */
class BlogAdminServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.blog_admin.post_limit'] = 15;
        $app['np.blog_admin.drafts_dir'] = '';

        $app['np.blog_admin.mount_point'] = $app->share(function($app) {
            $mountPoint = '/blog';
            if (isset($app['np.admin.mount_point'])) {
                $mountPoint = $app['np.admin.mount_point'] . $mountPoint;
            }
            return $mountPoint;
        });

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

    public function boot(Application $app)
    {
        # ===================================================== #
        #    BLOG ADMIN ROUTES                                  #
        # ===================================================== #

        $app->mount($app['np.blog_admin.mount_point'].'/posts', new BlogAdminControllerProvider());
        //$app->mount($app['np.blog_admin.mount_point'].'/drafts', new BlogAdminControllerProvider());

        // create an index shortcut
        $app->get($app['np.blog_admin.mount_point'], 'np.blog_admin.controller:dashboardAction')->bind('admin_blog');
    }
}
