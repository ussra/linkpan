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
    private $grouppost;

    /**
     * @return mixed
     */
    public function getGrouppost()
    {
        return $this->grouppost;
    }

    /**
     * @param mixed $grouppost
     */
    public function setGrouppost($grouppost)
    {
        $this->grouppost = $grouppost;
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

    /**
     * Set image
     *
     * @param string $image
     *
     * @return GroupPostImage
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
}

