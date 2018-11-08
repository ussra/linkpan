<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BoostPost
 *
 * @ORM\Table(name="boost_post")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\BoostPostRepository")
 */
class BoostPost
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
     * @ORM\Column(name="boostId", type="text")
     */
    private $boostId;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\Post")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $post;


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
     * Set boostId
     *
     * @param string $boostId
     *
     * @return BoostPost
     */
    public function setBoostId($boostId)
    {
        $this->boostId = $boostId;

        return $this;
    }

    /**
     * Get boostId
     *
     * @return string
     */
    public function getBoostId()
    {
        return $this->boostId;
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param mixed $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }


}

