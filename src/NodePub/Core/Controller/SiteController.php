<?php

namespace NodePub\Core\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use NodePub\Core\Model\Site;
use NodePub\Core\Form\Type\SiteType;

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
    
    public function postSitesAction(Request $request)
    {
        $this->getSiteForm()->bindRequest($request);

        if ($form->isValid()) {
            
            // TODO save
            
            return $this->app->redirect($this->app['url_generator']->generate('admin_sites'));
        } else {
            # forward to template for errors
            return $this->app['twig']->render('@np-admin/panels/site_settings.twig', array(
                'form' => $form->createView()
            ));
        }
    }
    
    public function newSiteAction()
    {
        return $this->app['twig']->render('@np-admin/panels/site_settings.twig', array(
            'form' => $this->getSiteForm()->createView()
        ));
    }

    public function settingsAction(Request $request, Site $site)
    {
        $form = $this->getSiteForm($site);
        
        if (false) {
            $form->bindRequest($request);
    
            if ($form->isValid()) {
                
                // TODO save
                
                return $this->app->redirect($this->app['url_generator']->generate('admin_sites'));
            }
        }
        
        return $this->app['twig']->render('@np-admin/panels/site_settings.twig', array(
            'siteSettings' => $site,
            'form' => $form->createView()
        ));
    }

    public function switchSiteAction(Site $site)
    {
    }

    protected function getSiteForm($site = null) {
        $site = $site?: new Site();
        $form = $this->app['form.factory']
            ->createBuilder(new SiteType(), $site)
            ->getForm();

        return $form;
    }
}