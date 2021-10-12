<?php
/**
 * Created by PhpStorm.
 * User: cotaroba2
 * Date: 10/7/18
 * Time: 20:34
 */

namespace App\Model;

use App\Entity\HmlsGeoData;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\Utility;
use App\Model\Mortgage;
use Doctrine\ORM\Query;
use Twig\Environment;
use Doctrine\ORM\ORMException;

class Listing
{

    protected $em;
    protected $twig;
    protected $utility;
    protected $mortgage;
    protected $rets;
    protected $hmlsConfig;
    protected $errorMsg = '';

    public function __construct(EntityManagerInterface $em, Environment $twig, Utility $utility, Mortgage $mortgage)
    {
        $this->em = $em;
        $this->utility = $utility;
        $this->mortgage = $mortgage;
        $this->twig = $twig;
        $this->mlsgrid = $this->utility->getParameter('mlsgrid');

    }

    public function getMlsListing($mlsnumber, $database="App:MlsgridData"){
        $listingJSON = $this->getMlsListing_query($mlsnumber,$database);
        if ($listingJSON === false){
            return false;
        }
        if (array_key_exists("details", $listingJSON)){
            $listingDetails = json_decode($listingJSON['details'],true);
        }
        if (array_key_exists("googleResponse",$listingJSON)){
            $geodata = json_decode($listingJSON['googleResponse'],true);
            if ($geodata !== null) {
                if ($geodata['status'] === 'OK') {
                    $listingDetails['geodata'] = $geodata['results'][0];
                }
            } else {
                $listingDetails['geodata'] = null;
            }
        }
        /*
        foreach($listingDetails as $k=>$v){
            $listingDetails[$k]=json_decode($v,true);
        }

        $listingDetails['api'] = $this->utility->getParameter('googleMapApi');
        $mapScript = $this->twig->render('listing/listing.map.js.twig',[
            'listing' => $listingDetails]);
        $listingDetails['mapScript'] = $mapScript;
        */
    switch ($listingDetails['PropertyType']){
        case 'Residential': $listingDetails['propertyTypeName'] = 'Residential'; break;
        case 'Residential Income': $listingDetails['propertyTypeName']= 'Multi-Family'; break;
        case 'Commercial Sale': $listingDetails['propertyTypeName'] = 'Commericial'; break;
        case 'Land': $listingDetails['propertyTypeName'] = 'Land'; break;
        default: $listingDetails['propertyTypeName'] = '';
    }
    //$listingDetails['geodata']=$this->em->getRepository(HmlsGeoData::class)->findOneBy(['mlsnumber' => $mlsnumber]);
        $listingDetails['addressfull'] = $this->utility->getAddressFull(
            (array_key_exists('StreetNumber',$listingDetails)) ? $listingDetails['StreetNumber'] : '',
            (array_key_exists("StreetDirPrefix",$listingDetails)) ? $listingDetails['StreetDirPrefix'] : '',
            (array_key_exists("StreetName",$listingDetails)) ? ucfirst($listingDetails['StreetName']) : '',
            (array_key_exists("StreetSuffix", $listingDetails)) ? $listingDetails['StreetSuffix'] : '');
        $listingDetails['dateMlsDataUpdate'] = $listingDetails['ModificationTimestamp'];//$this->utility->getLastHmlsControl_dateUpdated();
        $taxes = (key_exists("HMS_TaxTotalAmount",$listingDetails)) ? $listingDetails['HMS_TaxTotalAmount']: 0;
        $listingDetails['mortgage'] = $this->mortgage->getMortgage_fromListing($listingDetails['ListPrice'],$listingDetails['CountyOrParish'],false,$taxes);
        $listingDetails['apiKey'] = $this->utility->getParameter("googleMapApi");
        $listingDetails['mapping'] = $this->utility->getParameter("Property");
        /*
        if ($listingDetails['photocount'] > 0 ){
            $listingDetails['photos']=$this->utility->returnPhotoFileName($mlsnumber);//getPhotos_FilenameArray_derived($mlsnumber, $listingDetails['photocount']);
            $listingDetails['thumb']= $mlsnumber . '/thumb.jpg';//implode($this->utility->getPhotos_FilenameArray_derived($mlsnumber, 1));
        }
        */
        return $listingDetails;
    }


    /*

    private function getMlsListing_query($mlsnumber,$database){
        $em = $this->em;
        $results =
            $em->createQueryBuilder()
                ->select('m.details')
                ->from($database,'m')
                ->where('m.mlsnumber = :mls')
                ->setParameter('mls', $mlsnumber)
                ->getQuery()
                ->getResult(Query::HYDRATE_ARRAY);
        if (key_exists('0',$results)){
            return json_decode($results[0]['details'],true);//$merged;
        } else {
            return false;
        }
    }

    private function getGeoData($mlsnumber){
        $em = $this->em;
        $results =
            $em->createQueryBuilder()
                ->select('m.details')
                ->from(App:HmlsGeoData,'m')
                ->where('m.mlsnumber = :mls')
                ->setParameter('mls', $mlsnumber)
                ->getQuery()
                ->getResult(Query::HYDRATE_ARRAY);
        if (key_exists('0',$results)){
            return json_decode($results[0]['details'],true);//$merged;
        } else {
            return false;
        }
    }
*/
    private function getMlsListing_query($mlsnumber,$database){
        $em = $this->em;
        $results =
            $em->createQueryBuilder()
                ->select('m.details', 'p.googleResponse')
                ->from($database,'m')
                ->leftJoin('App:HmlsGeoData', 'p','WITH','m.mlsnumber = p.mlsnumber')
                ->where('m.mlsnumber = :mls')
                ->setParameter('mls', $mlsnumber)
                ->getQuery()
                ->getResult(Query::HYDRATE_ARRAY);
        if (key_exists('0',$results)){
            //$merged = (is_array($results[1])) ? array_merge($results[0],$results[1]) : $results[0];
            return $results[0];
        } else {
            return false;
        }
    }
}