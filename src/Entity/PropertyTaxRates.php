<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * HmlsControl
 *
 * @ORM\Table(name="propertyTaxRates",
 *     indexes={@ORM\Index(name="county", columns={"county"}), @ORM\Index(name="PRIMARY", columns={"id"})},
 *     uniqueConstraints={@UniqueConstraint(name="mlsNumber_U",columns={"mlsNumber"})})
 * @ORM\Entity
 */
class PropertyTaxRates
{

    /**
     * @var string
     *
     *@ORM\Column(name="county",type="string", length=128, nullable=false)
     */
    private $county;

    /**
     * @var float
     *
     *@ORM\Column(name="tax_rate", type="decimal", precision=10, scale=4)
     */
    private $taxRate;

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
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @param string $county
     */
    public function setCounty($county)
    {
        $this->county = $county;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }




}

