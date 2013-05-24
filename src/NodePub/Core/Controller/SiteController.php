<?php

namespace NodePub\Core\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteController
{
    protected $app;
    
    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function sitesAction()
    {
        $sites = $this->app['np.sites.provider']->findAll();
        
        return new Response($this->app['twig']->render('@core/admin/sites.twig', array(
            'sites' => $sites
        )));
    }
}