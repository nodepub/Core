<?php

namespace NodePub\Core\Controller;

use NodePub\Core\Model\Site;
use Silex\Application;

class SiteController
{
    protected $app;
    
    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function sitesAction()
    {
        return $this->app['twig']->render('@np-admin/panels/sites.twig', array(
            'sites' =>  $this->app['np.sites.provider']->getAll()
        ));
    }

    public function settingsAction(Site $site)
    {
        return $this->app['twig']->render('@np-admin/panels/site.twig', array(
            'site' => $site
        ));
    }

    public function switchSiteAction(Site $site)
    {
    }
}