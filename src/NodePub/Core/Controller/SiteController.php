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
        $sites = $this->app['np.sites.provider']->getAll();

        return $this->app['twig']->render('@np-admin/panel.twig', array(
            'nav' => 'Sites',
            'content' => 'TODO'
        ));
    }
}