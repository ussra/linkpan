<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupPostImage
 *
 * @ORM\Table(name="group_post_image")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\GroupPostImageRepository")
 */
class GroupPostImage
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
     * @ORM\Column(name="image", type="text")
     */
    private $image;

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
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
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

