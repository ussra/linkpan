<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObjectShare
 *
 * @ORM\Table(name="object_share")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\ObjectShareRepository")
 */
class ObjectShare
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=5)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="objectId", type="integer")
     */
    private $objectId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ObjectShare
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     *
     * @return ObjectShare
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    
        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }


}

