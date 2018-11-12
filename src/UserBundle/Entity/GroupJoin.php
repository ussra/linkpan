<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupJoin
 *
 * @ORM\Table(name="group_join")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\GroupJoinRepository")
 */
class GroupJoin
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
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\Groupe")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $group;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }


}

