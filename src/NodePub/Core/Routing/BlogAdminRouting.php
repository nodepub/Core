<?php

namespace NodePub\Core\Routing;

use Silex\Application;
use Silex\ControllerProviderInterface;

// index     GET      /posts           admin_blog_posts
// show      GET      /posts/:id       admin_blog_post
// new       GET      /posts/new       admin_blog_new_post
// create    POST     /posts           admin_blog_post_posts
// edit      GET      /posts/:id/edit  admin_blog_edit_post
// update    PUT      /posts/:id       admin_blog_update_post
// destroy   DELETE   /posts/:id       admin_blog_delete_post

class BlogAdminRouting implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $postProvider = function($id) use($app) {
            if ($post = $app['blog.post_manager']->findById($id)
                //|| $post = $app['np.blog_admin.draft_manager']->findById($id)
                ) {
                return $post;
            } else {
                throw new \Exception("Post not found", 404);
            }
        };

        $controllers->post('/', 'np.blog_admin.controller:createPostAction')
            ->bind('admin_blog_post_posts');

        $controllers->get('/page/{page}', 'np.blog_admin.controller:getPostsAction')
            ->value('page', 1)
            ->assert('page', "\d")
            ->bind('admin_blog_posts');

        $controllers->get('/{post}', 'np.blog_admin.controller:getPostAction')
            ->convert('post', $postProvider)
            ->bind('admin_blog_post');

        $controllers->get('/new', 'np.blog_admin.controller:newPostAction')
            ->bind('admin_blog_new_post');

        $controllers->get('/{post}/edit', 'np.blog_admin.controller:editPostAction')
            ->convert('post', $postProvider)
            ->bind('admin_blog_edit_post');

        $controllers->post('/{post}/update', 'np.blog_admin.controller:updatePostAction')
            ->convert('post', $postProvider)
            ->bind('admin_blog_update_post');

        $controllers->post('/{post}/delete', 'np.blog_admin.controller:deletePostAction')
            ->convert('post', $postProvider)
            ->bind('admin_blog_delete_post');

        return $controllers;
    }
}