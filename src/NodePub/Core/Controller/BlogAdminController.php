<?php

namespace NodePub\Core\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogAdminController
{
    protected $app;
    
    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function indexAction()
    {
        $postManager = $this->app['np.blog.post_manager'];
        $draftManager = $this->app['np.blog.draft_manager'];
        $form = $this->createForm(new PostType(), new Post());
        $postLimit = $this->container->getParameter('np.blog.dashboard.post_limit');
        
        return new Response($this->app['twig']->render('foo', array(
            'posts' => $postManager->findRecentPosts($postLimit),
            'drafts' => $draftManager->findRecentPosts($postLimit),
            'form' => $form->createView()
        )));
    }
}