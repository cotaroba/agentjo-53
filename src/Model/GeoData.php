<?php
/**
 * Created by PhpStorm.
 * User: cotaroba2
 * Date: 10/6/18
 * Time: 11:20
 */

namespace App\Model;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\HmlsGeoData;
use Doctrine\ORM\Query;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class GeoData
{
    protected $utility;
    protected $em;
    protected $client;
    protected $api;

    public function __construct(Utility $utility, EntityManagerInterface $em, HttpClientInterface $client)
    {
        $this->utility  = $utility;
        $this->em       = $em;
        $this->client   = $client;
        $this->api = $this->utility->getParameter('googleMapApi');
    }

    public function setGeoData_all($database="App:MlsgridData"){
        echo "\nStarting Script\nsetGeoData_all($database)";
        $start = microtime(true);
        $mlsAddress_list = $this->getMlsAddress_list($database);
        if ($mlsAddress_list !== false){
            echo "\nNumber of Addresses " . sizeof($mlsAddress_list);
            foreach ($mlsAddress_list as $key=>$val){
                echo "\n   Address for MLS: " .$val['mlsnumber'];
                $this->setGeoData_entity($val);
            }
        }
        $end = microtime(true);
        echo "\n";
        $return['Time to Run setGeoData_all'] = round($end - $start, 2) . 's';
        return $return;
    }

    public function cleanGeoData($database="App:MlsgridData"){
        $start = microtime(true);
        $em=$this->em;
        $mlsAddress_list = $this->cleanMlsAddress_list($database);
        echo "\nclean-geodata\n";
        if ($mlsAddress_list !== false) {
            foreach($mlsAddress_list as $key=>$val){
                echo "\n  MLS " .$val['mlsnumber'];

                    $remove = $em->getRepository(HmlsGeoData::class)->find($val['id']);
                if ($remove !== null) {
                    $em->remove($remove);
                    $em->flush();
                    echo " -- removed";
                }
            }
        }
        $end = microtime(true);
        echo "\n";
        $return['Time to Run clean-geodata'] = round($end - $start, 2) . 's';
        return $return;
    }

    private function setGeoData_entity($hmlsData){
        $em = $this->em;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $geoData = new HmlsGeoData();
        $address = $hmlsData['addressfull'] . ', ' . $hmlsData['city'] . ' ' .
        $hmlsData['state'] . ', ' . $hmlsData['zip'] . ', USA';
        $start = microtime(true);
        $mapJson = $this->getAddress_JSON($address);
        $total = microtime(true) - $start;
        $map = json_decode($mapJson,JSON_OBJECT_AS_ARRAY);
        if (is_array($map)) {
            if ($map['status'] == 'OK') {
                $geoData->setLat($map['results']['0']['geometry']['location']['lat']);
                $geoData->setLng($map['results']['0']['geometry']['location']['lng']);
                $geoData->setAddress($map['results']['0']['formatted_address']);
                $geoData->setLocationType($map['results']['0']['geometry']['location_type']);
                $geoData->setPlaceId($map['results']['0']['place_id']);
                $geoData->setMlsnumber($hmlsData['mlsnumber']);
                $geoData->setDateMlsModified($hmlsData['modtimestamp']);
                $geoData->setGoogleResponse($mapJson);

                $em->persist($geoData);
                $em->flush();
            }
        }
    }

    private function getAddress_JSON($address){
        $start = microtime(true);
        $map = $this->client->request('GET','https://maps.googleapis.com/maps/api/geocode/json?address=' .
        urlencode($address) .'&key=' . $this->api);
        $total = microtime(true) - $start;
        $mapJson = (string) $map->getContent();
        return $mapJson;
    }

    private function getMlsAddress_list($database){
        $em = $this->em;
        $results = $em->createQueryBuilder()
            ->select('m.mlsnumber, m.addressfull, m.city, m.state, m.zip, m.modtimestamp')
            ->from($database,'m')
            ->where('m.mlsnumber NOT IN (select d.mlsnumber from App:HmlsGeoData d)')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        if (key_exists('0', $results)){
            return $results;
        } else {
            return false;
        }
    }

    private function cleanMlsAddress_list($database){
        $em = $this->em;
        $results = $em->createQueryBuilder()
            ->select('m.id,m.mlsnumber')
            ->from('App:HmlsGeoData','m')
            ->where("m.mlsnumber NOT IN (select d.mlsnumber from $database d)")
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        if (key_exists('0', $results)){
            return $results;
        } else {
            return false;
        }
    }
}