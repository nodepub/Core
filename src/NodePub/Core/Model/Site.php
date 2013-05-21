<?php

namespace NodePub\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Site Model
 */
class Site
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $hostName;

    /**
     * @var ArrayCollection
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
     * @param string $name
     */
    public function setName($name)
    {   
        $this->name = $name;
        
        return $this;
    }

    /**
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $domainName
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
