<?php

namespace NodePub\Core\Controller;

use NodePub\BlogEngine\Post;
use NodePub\BlogEngine\PostManager;
use NodePub\Core\Form\Type\PostType;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST Actions for blog posts
 */
class BlogAdminController
{
    protected $app;
    protected $postManager;
    
    function __construct(Application $app, PostManager $postManager)
    {
        $this->app = $app;
        $this->postManager = $postManager;
    }

    public function dashboardAction()
    {
        $postLimit = $this->app['np.blog_admin.post_limit'];
        
        return new Response($this->app['twig']->render('@np-admin/panels/blog.twig', array(
            'posts' => $this->app['blog.post_manager']->findRecentPosts($postLimit),
            'drafts' => array(), //$this->app['np.blog_admin.draft_manager']->findRecentPosts($postLimit),
            'form' => $this->getPostForm()->createView(),
            'page_number' => 1,
            'published_page_count' => $this->postManager->getPageCount($postLimit),
            'drafts_page_count' => 0 //$this->app['np.blog_admin.draft_manager']->getPageCount($postLimit)
        )));
    }

    public function getPostsAction($page)
    {
        $postLimit = $this->app['np.blog_admin.post_limit'];

        return new Response(
            $this->app['twig']->render('@np-admin/panels/blog_post_index.twig', array(
                'posts' => $this->postManager->findRecentPosts($postLimit, $page),
                'page_number' => $page,
                'page_count' => $this->postManager->getPageCount($postLimit),
                'list_type' => 'Published Posts'
            ))
        );
    }

    public function getPostAction(Post $post)
    {
        return new Response($this->app['twig']->render('@np-admin/panels/blog_post.twig', array(
            'post' => $post,
            'form' => $this->getPostForm($post)->createView()
        )));
    }

    public function newPostAction()
    {
        return new Response($this->app['twig']->render('@np-admin/panels/blog_post_new.twig', array(
            'form' => $this->getPostForm()->createView()
        )));
    }

    public function createPostAction(Request $request)
    {
        $post = new Post();
        $form = $this->getPostForm($post)->bindRequest($request);
    
        if ($form->isValid()) {
            $post = $this->postManager->savePost($post);
            
            // if ($post && $request->isXmlHttpRequest()) {
            //     return $this->getJsonResponse($this->preparePostForJson($post), 201);
            // }
    
            return $this->app->redirect($this->app['url_generator']->generate('admin_dashboard'));
        } else {
            # forward to template for errors
        }
    }

    public function editPostAction(Post $post)
    {
        return new Response($this->app['twig']->render('@np-admin/panels/blog_post_edit.twig', array(
            'post' => $post,
            'form' => $this->getPostForm($post)->createView()
        )));
    }

    public function updatePostAction(Request $request, Post $post)
    {
        $form = $this->getPostForm($post)->bindRequest($request);
    
        if ($form->isValid()) {
            $post = $this->postManager->savePost($post);
    
            return $this->app->redirect($this->app['url_generator']->generate('admin_dashboard'));
        } else {
            # forward to template for errors
        }
    }

    public function deletePostAction(Post $post)
    {
    }

    protected function getPostForm($post = null) {
        $post = $post?: new Post();
        $form = $this->app['form.factory']
            ->createBuilder(new PostType(), $post)
            ->getForm();

        return $form;
    }
}