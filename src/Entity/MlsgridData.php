<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UpdateMlsGrid
 *
 * @ORM\Table(name="mlsGridData",indexes={@ORM\Index(name="mlsGridData__mlsNumber_uindex", columns={"mlsNumber"}), @ORM\Index(name="mlsGridData__propertyType", columns={"propertyType"}), @ORM\Index(name="mlsGridData__modTimestamp", columns={"modTimestamp"}) })
 * @ORM\Entity
 */
class MlsgridData
{
    /**
     * @var string
     *
     * @ORM\Column(name="matrixID", type="string",length=32, nullable=false)
     */
    private $matrixid;

    /**
     * @var string
     *
     * @ORM\Column(name="mlsNumber", type="string",length=32, nullable=false)
     */
    private $mlsnumber;

    /**
     * @var string
     *
     * @ORM\Column(name="modTimestamp", type="string", length=32, nullable=true)
     */
    private $modtimestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", nullable=false)
     */
    private $details;

    /**
     * @var string
     *
     * @ORM\Column(name="propertyType", type="string", length=36, nullable=false)
     */
    private $propertytype;

    /**
     * @var string
     *
     * @ORM\Column(name="addressFull", type="string", length=160, nullable=true)
     */
    private $addressfull;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=60, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="county", type="string", length=60, nullable=true)
     */
    private $county;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=15, nullable=true)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=10, nullable=true)
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="subDivision", type="string", length=60, nullable=true)
     */
    private $subdivision;

    /**
     * @var string
     *
     * @ORM\Column(name="bookingSection", type="string", length=60, nullable=true)
     */
    private $bookingsection;

    /**
     * @var integer
     *
     * @ORM\Column(name="bathsFull", type="integer", nullable=true)
     */
    private $bathsfull;

    /**
     * @var integer
     *
     * @ORM\Column(name="bathsHalf", type="integer", nullable=true)
     */
    private $bathshalf;

    /**
     * @var integer
     *
     * @ORM\Column(name="bedrooms", type="integer", nullable=true)
     */
    private $bedrooms;

    /**
     * @var string
     *
     * @ORM\Column(name="listingAgentId1", type="string", length=10, nullable=true)
     */
    private $agentid;

    /**
     * @var string
     *
     * @ORM\Column(name="listingOfficeId", type="string", length=10, nullable=true)
     */
    private $officeid;

    /**
     * @var string
     *
     * @ORM\Column(name="listingAgentName", type="string", length=180, nullable=true)
     */
    private $agentname;

    /**
     * @var string
     *
     * @ORM\Column(name="listingOfficeName", type="string", length=180, nullable=true)
     */
    private $officename;

    /**
     * @var integer
     *
     * @ORM\Column(name="photoCount", type="integer", nullable=true)
     */
    private $photocount;

    /**
     * @var string
     *
     * @ORM\Column(name="photoModifiedDate", type="string", length=32, nullable=true)
     */
    private $photomodifieddate;

    /**
     * @var integer
     *
     * @ORM\Column(name="listPrice", type="integer", nullable=true)
     */
    private $listprice;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text", length=65535, nullable=true)
     */
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(name="schoolsDistrict", type="string", length=60, nullable=true)
     */
    private $schoolsdistrict;

    /**
     * @var string
     *
     * @ORM\Column(name="elementarySchool", type="string", length=60, nullable=true)
     */
    private $elementaryschool;

    /**
     * @var string
     *
     * @ORM\Column(name="middleSchool", type="string", length=60, nullable=true)
     */
    private $middleschool;

    /**
     * @var string
     *
     * @ORM\Column(name="highSchool", type="string", length=60, nullable=true)
     */
    private $highschool;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalSqft", type="integer", length=60, nullable=true)
     */
    private $totalsqft;

    /**
     * @var string
     *
     * @ORM\Column(name="standardStatus", type="string", length=60, nullable=true)
     */
    private $standardstatus;

    /**
     * @var integer
     *
     * @ORM\Column(name="taxes", type="integer", nullable=true)
     */
    private $taxes;

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
    public function getMatrixid()
    {
        return $this->matrixid;
    }

    /**
     * @param string $matrixid
     */
    public function setMatrixid($matrixid)
    {
        $this->matrixid = $matrixid;
    }

    /**
     * @return string
     */
    public function getMlsnumber()
    {
        return $this->mlsnumber;
    }

    /**
     * @param string $mlsnumber
     */
    public function setMlsnumber($mlsnumber)
    {
        $this->mlsnumber = $mlsnumber;
    }

    /**
     * @return string
     */
    public function getModtimestamp()
    {
        return $this->modtimestamp;
    }

    /**
     * @param string $modtimestamp
     */
    public function setModtimestamp($modtimestamp)
    {
        $this->modtimestamp = $modtimestamp;
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
     * @return string
     */
    public function getPropertytype()
    {
        return $this->propertytype;
    }

    /**
     * @param string $propertytype
     */
    public function setPropertytype($propertytype)
    {
        $this->propertytype = $propertytype;
    }

    /**
     * @return string
     */
    public function getAddressfull()
    {
        return $this->addressfull;
    }

    /**
     * @param string $addressfull
     */
    public function setAddressfull($addressfull)
    {
        $this->addressfull = $addressfull;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

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
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getSubdivision()
    {
        return $this->subdivision;
    }

    /**
     * @param string $subdivision
     */
    public function setSubdivision($subdivision)
    {
        $this->subdivision = $subdivision;
    }

    /**
     * @return string
     */
    public function getBookingsection()
    {
        return $this->bookingsection;
    }

    /**
     * @param string $bookingsection
     */
    public function setBookingsection($bookingsection)
    {
        $this->bookingsection = $bookingsection;
    }

    /**
     * @return int
     */
    public function getBathsfull()
    {
        return $this->bathsfull;
    }

    /**
     * @param int $bathsfull
     */
    public function setBathsfull($bathsfull)
    {
        $this->bathsfull = $bathsfull;
    }

    /**
     * @return int
     */
    public function getBathshalf()
    {
        return $this->bathshalf;
    }

    /**
     * @param int $bathshalf
     */
    public function setBathshalf($bathshalf)
    {
        $this->bathshalf = $bathshalf;
    }

    /**
     * @return int
     */
    public function getBedrooms()
    {
        return $this->bedrooms;
    }

    /**
     * @param int $bedrooms
     */
    public function setBedrooms($bedrooms)
    {
        $this->bedrooms = $bedrooms;
    }

    /**
     * @return string
     */
    public function getAgentid()
    {
        return $this->agentid;
    }

    /**
     * @param string $agentid
     */
    public function setAgentid($agentid)
    {
        $this->agentid = $agentid;
    }

    /**
     * @return string
     */
    public function getOfficeid()
    {
        return $this->officeid;
    }

    /**
     * @param string $officeid
     */
    public function setOfficeid($officeid)
    {
        $this->officeid = $officeid;
    }

    /**
     * @return string
     */
    public function getAgentname()
    {
        return $this->agentname;
    }

    /**
     * @param string $agentname
     */
    public function setAgentname($agentname)
    {
        $this->agentname = $agentname;
    }

    /**
     * @return string
     */
    public function getOfficename()
    {
        return $this->officename;
    }

    /**
     * @param string $officename
     */
    public function setOfficename($officename)
    {
        $this->officename = $officename;
    }

    /**
     * @return int
     */
    public function getPhotocount()
    {
        return $this->photocount;
    }

    /**
     * @param int $photocount
     */
    public function setPhotocount($photocount)
    {
        $this->photocount = $photocount;
    }

    /**
     * @return string
     */
    public function getPhotomodifieddate()
    {
        return $this->photomodifieddate;
    }

    /**
     * @param string $photomodifieddate
     */
    public function setPhotomodifieddate($photomodifieddate)
    {
        $this->photomodifieddate = $photomodifieddate;
    }

    /**
     * @return int
     */
    public function getListprice()
    {
        return $this->listprice;
    }

    /**
     * @param int $listprice
     */
    public function setListprice($listprice)
    {
        $this->listprice = $listprice;
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return string
     */
    public function getSchoolsdistrict()
    {
        return $this->schoolsdistrict;
    }

    /**
     * @param string $schoolsdistrict
     */
    public function setSchoolsdistrict($schoolsdistrict)
    {
        $this->schoolsdistrict = $schoolsdistrict;
    }

    /**
     * @return string
     */
    public function getElementaryschool()
    {
        return $this->elementaryschool;
    }

    /**
     * @param string $elementaryschool
     */
    public function setElementaryschool($elementaryschool)
    {
        $this->elementaryschool = $elementaryschool;
    }

    /**
     * @return string
     */
    public function getMiddleschool()
    {
        return $this->middleschool;
    }

    /**
     * @param string $middleschool
     */
    public function setMiddleschool($middleschool)
    {
        $this->middleschool = $middleschool;
    }

    /**
     * @return string
     */
    public function getHighschool()
    {
        return $this->highschool;
    }

    /**
     * @param string $highschool
     */
    public function setHighschool($highschool)
    {
        $this->highschool = $highschool;
    }

    /**
     * @return int
     */
    public function getTotalsqft()
    {
        return $this->totalsqft;
    }

    /**
     * @param int $totalsqft
     */
    public function setTotalsqft($totalsqft)
    {
        $this->totalsqft = $totalsqft;
    }

    /**
     * @return string
     */
    public function getStandardstatus()
    {
        return $this->standardstatus;
    }

    /**
     * @param string $standardstatus
     */
    public function setStandardstatus($standardstatus)
    {
        $this->standardstatus = $standardstatus;
    }

    /**
     * @return int
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @param int $taxes
     */
    public function setTaxes($taxes)
    {
        $this->taxes = $taxes;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}

