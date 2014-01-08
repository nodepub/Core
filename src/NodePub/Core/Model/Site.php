<?php

namespace NodePub\Core\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="np_sites")
 */
class Site
{
    /**
     * @Id @Column(type="integer", nullable=false) @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(type="string", length=250, name="host_name", nullable=false, unique=true)
     */
    private $hostName;
    
    /**
     * @Column(type="string", length=256)
     */
    private $url;
    
    /**
     * @Column(type="string", length=256)
     */
    private $title;
    
    /**
     * @Column(type="string", length=256)
     */
    private $tagline;
    
    /**
     * @Column(type="text")
     */
    private $description;
    
    /**
     * @Column(type="string", length=256, nullable=false)
     */
    private $theme;
    
    /**
     * @Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;
    

    /**
     * @var ArrayCollection
     * @todo enable SiteAttribute relation
     */
    private $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }
    
    # ===================================================== #
    #    Getters/Setters                                    #
    # ===================================================== #

    public function getId()
    {
        return $this->id;
    }

    public function setHostName($hostName)
    {   
        $this->hostName = $hostName;
        
        return $this;
    }

    public function getHostName()
    {
        return $this->hostName;
    }
    
    public function setUrl($url)
    {   
        $this->url = $url;
        
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }
    
    public function setTitle($title)
    {   
        $this->title = $title;
        
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTagline($tagline)
    {   
        $this->tagline = $tagline;
        
        return $this;
    }

    public function getTagline()
    {
        return $this->tagline;
    }
    
    public function setDescription($description)
    {   
        $this->description = $description;
        
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {   
        $this->theme = $theme;
        
        return $this;
    }

    /**
     * @return string 
     */
    public function getTheme()
    {
        return $this->theme;
    }
    
    /**
     * @return mixed
     */
    public function addAttribute($name, $value)
    {
        if (!$this->attributes->offsetExists($name)) {
            $this->attributes->set($name, $value);
        }
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getAttribute($name, $defaultValue = null)
    {
        $attr = $this->attributes->get($name);
        return $attr ?: $defaultValue;
    }
}
