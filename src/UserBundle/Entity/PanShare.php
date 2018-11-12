<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PanShare
 *
 * @ORM\Table(name="pan_share")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\PanShareRepository")
 */
class PanShare
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\Pan")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $pan;

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

    /**
     * @return mixed
     */
    public function getPan()
    {
        return $this->pan;
    }

    /**
     * @param mixed $pan
     */
    public function setPan($pan)
    {
        $this->pan = $pan;
    }






    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}

