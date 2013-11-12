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

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $hostName
     */
    public function setHostName($hostName)
    {   
        $this->hostName = $hostName;
        
        return $this;
    }

    /**
     * @return string 
     */
    public function getHostName()
    {
        return $this->hostName;
    }
    
    /**
     * @param string $title
     */
    public function setTitle(title)
    {   
        $this->title = title;
        
        return $this;
    }

    /**
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @param string $tagline
     */
    public function setTagline(tagline)
    {   
        $this->tagline = tagline;
        
        return $this;
    }

    /**
     * @return string 
     */
    public function getTagline()
    {
        return $this->tagline;
    }
    
    /**
     * @param string $description
     */
    public function setDescription(description)
    {   
        $this->description = description;
        
        return $this;
    }

    /**
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param string $theme
     */
    public function setTheme(theme)
    {   
        $this->theme = theme;
        
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
        if (!$this->attributes->find($name)) {
            $this->attributes->add($name, $value);
        }
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getAttribute($name, $defaultValue = null)
    {
        if ($attr = $this->attributes->find($name)) {
            return $attr;
        } else {
            return $defaultValue;
        }
    }
}
