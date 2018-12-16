<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupPostLike
 *
 * @ORM\Table(name="group_post_like")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\GroupPostLikeRepository")
 */
class GroupPostLike
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
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\GroupPost")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $groupPost;


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
    public function getGroupPost()
    {
        return $this->groupPost;
    }

    /**
     * @param mixed $groupPost
     */
    public function setGroupPost($groupPost)
    {
        $this->groupPost = $groupPost;
    }


}

