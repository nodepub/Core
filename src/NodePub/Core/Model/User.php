<?php

namespace NodePub\Core\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="np_users")
 */
class User
{
    /**
     * @Id @Column(type="integer", nullable=false) @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(type="string", length=250, nullable=false, unique=true)
     */
    private $username;
    
    /**
     * @Column(type="string", length=250, nullable=false, unique=true)
     */
    private $password;
    
    /**
     * @Column(type="string", length=250, nullable=false, unique=true)
     */
    private $salt;
    
    /**
     * @Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;

    # ===================================================== #
    #    RELATIONS                                          #
    # ===================================================== #
    
    public function __construct()
    {
        $this->mediaItems = new ArrayCollection();
        
        // set initial timestamps
        $this->createdAt = $this->updatedAt = new \DateTime("now");
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

        // todo use a more robust slugify option
        $this->slug = strtolower(str_replace(' ', '', $name));
        
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
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }
    
    # ===================================================== #
    #    RELATIONS                                          #
    # ===================================================== #
    
    /**
     * @return Doctrine\Common\Collections\ArrayCollection
     */
    public function getMediaItems()
    {
        return $this->mediaItems;
    }
    
    /**
     * @param NodePub\MediaModel\MediaItem
     * @return NodePub\Media\Model\Tag
     */
    public function addImage(MediaItem $mediaItem)
    {
        $this->mediaItems[] = $mediaItem;
        
        return $this;
    }
}
