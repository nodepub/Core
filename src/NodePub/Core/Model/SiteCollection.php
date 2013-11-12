<?php

namespace NodePub\Core\Model;

use NodePub\Core\Model\Site;

/**
 */
class SiteCollection
{
    /**
     * Array of Site objects, keyed by site hostname property
     * @var array
     */
    protected $sites;

    public function __construct()
    {
        $this->sites = array();
    }
    
    # ===================================================== #
    #    Getters/Setters                                    #
    # ===================================================== #

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->sites;
    }

    /**
     * @param string $hostname
     * @return Site or null
     */
    public function getByHostName($hostName)
    {
        if (isset($this->sites[$hostName])) {
            return $this->sites[$hostName];
        }
    }
    
    /**
     * Adds a site to the collection, keyed by hostname
     * @return SiteCollection
     */
    public function addSite(Site $site)
    {
        if (!isset($this->sites[$site->getHostName()])) {
            $this->sites[$site->getHostName()] = $site;
        }
        
        return $this;
    }
    
    public function addSites($sites)
    {
        if (is_array($sites) || $sites instanceof \Traversable) {
            foreach ($sites as $site) {
                if ($site instanceof Site) {
                    $this->addSite($site);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Adds sites to the collection from a configuration array
     */
    public function addSitesFromConfig($siteConfig)
    {
        foreach ($siteConfig as $hostName => $config) {
            $site = new Site();
            $site
                ->setHostName($config['hostName'])
                ->setTitle($config['title'])
                ->setTagline($config['tagline'])
                ->setDescription($config['description'])
                ->setTheme($config['theme']);
            
            if (isset($config['attributes'])) {
                foreach ($config['attributes'] as $key => $value) {
                    $site->addAttribute($key, $value);
                }
            }
            
            $this->addSite($site);
        }
    }
}
