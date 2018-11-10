<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BoostPan
 *
 * @ORM\Table(name="boost_pan")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\BoostPanRepository")
 */
class BoostPan
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
     * Set boostId
     *
     * @param string $boostId
     *
     * @return BoostPan
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

