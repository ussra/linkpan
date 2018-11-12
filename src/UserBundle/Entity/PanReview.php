<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PanReview
 *
 * @ORM\Table(name="pan_review")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\PanReviewRepository")
 */
class PanReview
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
     * @ORM\Column(name="review", type="text")
     */
    private $review;

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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set review
     *
     * @param string $review
     *
     * @return PanReview
     */
    public function setReview($review)
    {
        $this->review = $review;

        return $this;
    }

    /**
     * Get review
     *
     * @return string
     */
    public function getReview()
    {
        return $this->review;
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


}

