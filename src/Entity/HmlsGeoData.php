<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * HmlsControl
 *
 * @ORM\Table(name="hmlsGeoData",
 *     indexes={@ORM\Index(name="mlsNumber", columns={"mlsNumber"}), @ORM\Index(name="PRIMARY", columns={"id"})},
 *     uniqueConstraints={@UniqueConstraint(name="mlsNumber_U",columns={"mlsNumber"})})
 * @ORM\Entity
 */
class HmlsGeoData
{
    /**
     * @var string
     *
     * @ORM\Column(name="mlsNumber", type="string", nullable=false)
     */
    private $mlsnumber;

    /**
     * @var float
     *
     *@ORM\Column(name="lat", type="decimal", precision=14, scale=7)
     */
    private $lat;

    /**
     * @var float
     *
     *@ORM\Column(name="lng", type="decimal", precision=14, scale=7)
     */
    private $lng;

    /**
     * @var string
     *
     *@ORM\Column(name="address",type="string", length=256, nullable=false)
     */
    private $address;

    /**
     * @var string
     *
     *@ORM\Column(name="location_type",type="string", length=180, nullable=false)
     */
    private $location_type;

    /**
     * @var string
     *
     *@ORM\Column(name="place_id",type="string", length=256, nullable=true)
     */
    private $place_id;

    /**
     * @var string
     *
     * @ORM\Column(name="googleResponse", type="text", nullable=false)
     */
    private $googleResponse;

    /**
     * @var string
     *
     *@ORM\Column(name="dateMlsModified",type="string", length=32, nullable=false)
     */
    private $dateMlsModified;

    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @return int
     */
    public function getMlsnumber()
    {
        return $this->mlsnumber;
    }

    /**
     * @param int $mlsnumber
     */
    public function setMlsnumber($mlsnumber)
    {
        $this->mlsnumber = $mlsnumber;
    }

    /**
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param string $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return string
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param string $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getLocationType()
    {
        return $this->location_type;
    }

    /**
     * @param string $location_type
     */
    public function setLocationType($location_type)
    {
        $this->location_type = $location_type;
    }

    /**
     * @return string
     */
    public function getPlaceId()
    {
        return $this->place_id;
    }

    /**
     * @param string $place_id
     */
    public function setPlaceId($place_id)
    {
        $this->place_id = $place_id;
    }

    /**
     * @return string
     */
    public function getDateMlsModified()
    {
        return $this->dateMlsModified;
    }

    /**
     * @param string $dateMlsModified
     */
    public function setDateMlsModified($dateMlsModified)
    {
        $this->dateMlsModified = $dateMlsModified;
    }

    /**
     * @return string
     */
    public function getGoogleResponse()
    {
        return $this->googleResponse;
    }

    /**
     * @param string $googleResponse
     */
    public function setGoogleResponse($googleResponse)
    {
        $this->googleResponse = $googleResponse;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }




}

