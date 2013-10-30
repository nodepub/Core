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
     * @Column(type="string", length=256, nullable=false)
     */
    private $name;

    /**
     * @Column(type="string", length=250, name="host_name", nullable=false, unique=true)
     */
    private $hostName;
    
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
