<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MortgageRate
 *
 * @ORM\Table(name="mortgageRate", indexes={
 *     @ORM\Index(name="dateUpdated", columns={"dateUpdated"}),
 *     @ORM\Index(name="PRIMARY", columns={"id"})})
 * @ORM\Entity
 */
class MortgageRate
{
    /**
     * @var string
     *
     *@ORM\Column(name="dateUpdated",type="string", length=32, nullable=true)
     */
    private $dateUpdated;

    /**
     * @var float
     *
     *@ORM\Column(name="rate", type="decimal", precision=6, scale=2)
     */
    private $rate;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", nullable=false)
     */
    private $details;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @return string
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param string $dateUpdated
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }




}

