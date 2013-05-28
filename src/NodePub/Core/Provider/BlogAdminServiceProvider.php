<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Controller\BlogAdminController;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Service Provider that registers blog admin settings and routes
 */
class BlogAdminServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // base url for all admin routes
        $app['np.blog_admin.mount_point'] = '/blog';

        $app['np.blog_admin.controller'] = $app->share(function($app) {
            return new BlogAdminController($app);
        });
    }

    public function boot(Application $app)
    {
        # ===================================================== #
        #    BLOG ADMIN ROUTES                                  #
        # ===================================================== #

        $blogAdmin = $app['controllers_factory'];

        $blogAdmin->get('/', 'np.blog_admin.controller::indexAction')
            ->bind('blog_admin');

        $app->mount($app['np.blog_admin.mount_point'], $blogAdmin);
    }
}
