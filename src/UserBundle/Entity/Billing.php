<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Billing
 *
 * @ORM\Table(name="billing")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\BillingRepository")
 */
class Billing
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
     * @var int
     *
     * @ORM\Column(name="CardNumber", type="integer",nullable=false)
     */
    private $cardNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="brand", type="text",nullable=false)
     */
    private $brand;

    /**
     * @var string
     *
     * @ORM\Column(name="cardId", type="text",nullable=false)
     */
    private $cardId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cardNumber
     *
     * @param integer $cardNumber
     *
     * @return Billing
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    /**
     * Get cardNumber
     *
     * @return int
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * Set brand
     *
     * @param string $brand
     *
     * @return Billing
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set cardId
     *
     * @param string $cardId
     *
     * @return Billing
     */
    public function setCardId($cardId)
    {
        $this->cardId = $cardId;

        return $this;
    }

    /**
     * Get cardId
     *
     * @return string
     */
    public function getCardId()
    {
        return $this->cardId;
    }
}

