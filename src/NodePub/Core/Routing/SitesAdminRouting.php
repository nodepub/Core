<?php

namespace NodePub\Core\Routing;

use Silex\Application;
use Silex\ControllerProviderInterface;

class SitesAdminRouting implements ControllerProviderInterface
{
    public function connect(Application $app)
    {        
        // Convert hostname into site object
        $siteConverter = function($hostName) use($app) {
            if (!$site = $app['np.sites.provider']->getByHostName($hostName)) {
                throw new \Exception("Site not found", 404);
            }

            return $site;
        };

        $controllers = $app['controllers_factory'];
        
        //$controllers->secure('ROLE_ADMIN');

        $controllers->get('/', 'np.sites.controller:sitesAction')
            ->bind('admin_sites');
        
        $controllers->post('/', 'np.sites.controller:postSitesAction')
            ->bind('admin_sites_post');
        
        $controllers->get('/new', 'np.sites.controller:newSiteAction')
            ->bind('admin_sites_new_site');

        $controllers->match('/{site}/settings', 'np.sites.controller:settingsAction')
            ->convert('site', $siteConverter)
            ->bind('admin_site');

        $controllers->post('/switch/{site}', 'np.sites.controller:switchSiteAction')
            ->convert('site', $siteConverter)
            ->bind('admin_sites_switch');

        return $controllers;
    }
}