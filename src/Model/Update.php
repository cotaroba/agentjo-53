<?php

namespace App\Model;


use App\Model\Utility;
use App\Entity\MlsgridData;
use App\Entity\MlsgridData_Temp;
use DateTime;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class Update
{

    protected $em;
    protected $utility;
    protected $mlsgrid;
    protected $client;
    protected $errorMsg = '';
    protected $time = [];
    protected $total = [];


    public function __construct(EntityManagerInterface $em, Utility $utility, HttpClientInterface $client)
    {
        $this->em = $em;
        $this->utility = $utility;
        $this->client = $client;
        $this->mlsgrid = $this->utility->getParameter('mlsgrid');

    }

    public function downloadPhotos($scope = "new", $limit = 0)
    {
        echo "\ndownloadPhotos() scope=" . $scope . "; limit=" . $limit;
        $li = 1;
        $count = 0;
        $cum = 0;
        $start = microtime(true);
        $photoDir = $this->mlsgrid['baseDir'] . $this->mlsgrid['photoDir'];
        $function = "getMediaArrayListingId_" . $scope;
        $photoArray = $this->$function();
        foreach ($photoArray as $k2 => $v2) {
            echo "\n\nMLS Number: " . $v2['mlsnumber'];
            $details = json_decode($v2['details'], true);
            $cum += $this->getPhotos($v2['mlsnumber'], $details);
            $count++;
            echo "\n\n    Property Count : " . $count . " of " . count($photoArray) . "\n     Elapsed time: " . round((microtime(true) - $start), 2) . "s\n";
            if ($li == $limit) {
                break;
            } else {
                $li++;
            }
        }
        $end = microtime(true);
        echo "\n\n";
        $total["Total Photos Downloaded"] = $cum;
        return $total;
    }

    public function downloadPhotos_missing()
    {
        $start = microtime(true);
        $count = 0;
        $baseDir = $this->mlsgrid['baseDir'];
        $photoDir = $this->mlsgrid['photoDir'];
        $mlsNumber = $this->getMediaArrayListingId_all();
        foreach ($mlsNumber as $k => $v) {
            if (!is_dir($baseDir . $photoDir . $v['mlsnumber'])) {
                echo "\n\nMLS Number: " . $v['mlsnumber'];
                mkdir($baseDir . $photoDir . $v['mlsnumber']);
                $details = json_decode($v['details'], true);//$this->utility->getPhotoArray($v);
                if (array_key_exists("Media", $details)) {
                    $photosUrl = $details['Media'];
                    echo "\n     Photo Count " . count($photosUrl);
                    foreach ($photosUrl as $k3 => $v3) {
                        $filename = sprintf("%03d", $v3['Order']) . '.jpg';
                        $this->saveMedia($v3['MediaURL'], $baseDir . $photoDir . $v['mlsnumber'] . '/' . $filename);
                        echo "\n        filename: " . $filename;
                        $count++;
                    }
                }
            }
        }
        $end = microtime(true);
        $total["Time to Download $count Photos"] = round(($end - $start), 2) . "s\n";
        return $total;
    }

    public function downloadPhotos_mlsnumber($mlsnumber)
    {
        $start = microtime(true);
        $count = 0;
        echo "\n\nMLS Number: " . $mlsnumber;
        $m = $this->getMediaArrayByMls($mlsnumber);
        if (count($m) > 0) {
            $details = json_decode($m[0]['details'], true);
            $count = $this->getPhotos($mlsnumber, $details);
        }
        $end = microtime(true);
        $total["Time to Download $count Photos"] = round(($end - $start), 2) . "s\n";
        return $total;
    }

    public function updateMls()
    {
        echo "\nupdateMls()\n";
        $query = "?\$filter=MlgCanView eq true and OriginatingSystemName eq '" .
            $this->mlsgrid['OriginatingSystemName'] . "' and StandardStatus eq 1&\$expand=Media,PropertyRooms,PropertyUnitTypes&\$top=1000";
        // 1. Create Temp Database
        $this->buildTempMlsTable();
        // 2. Update Temp Database
        $start = microtime(true);
        //$photoModDate = $this->getLastTimestamp('photomodifieddate');
        $count = 0;
        $moreData = true;
        $url = $this->mlsgrid['url'] . 'v2/Property' . $query;
        while ($moreData) {
            $data = $this->getMlsGridArray($url);
            if (key_exists('value', $data)) {
                $count += count($data['value']);
                $this->saveResults($data['value']);
            }
            if (key_exists("@odata.nextLink", $data)) {
                $url = $data["@odata.nextLink"];
            } else {
                $moreData = false;
            }
        }
        $total['Database Updated. Records: '] = $count;
        $end = microtime(true);
        $total["Time to Update Database"] = round(($end - $start), 2) . "s\n";
        return $total;
    }

    public function syncMedia()
    {
        $totalPhotos = intval(shell_exec('ls -alR ' . $this->mlsgrid['baseDir'] . $this->mlsgrid['photoDir'] . ' | wc -l'));
        $totalPhotoDirs = intval(shell_exec('ls -al' . $this->mlsgrid['baseDir'] . $this->mlsgrid['photoDir'] . ' | wc -l'));
        return $totalPhotos;
    }

    public function cleanPhotoDir()
    {
        echo "\ncleanPhotoDir()";
        $start = microtime(true);
        $photoDir = $this->mlsgrid['baseDir'] . $this->mlsgrid['photoDir'];
        $photoArray = $this->flatten($this->getMediaArrayListingId_all(), "mlsnumber");
        $photoDirArray = scandir($photoDir);
        $diffArray = array_diff($photoDirArray, $photoArray);
        foreach ($diffArray as $k => $v) {
            if ($v !== "." && $v !== "..") {
                if (is_dir($photoDir . $v)) {
                    array_map('unlink', glob($photoDir . $v . '/*.jpg'));
                    rmdir($photoDir . $v);
                    echo "\n    MLS deleted " . $v;
                }
            }
        }
        echo "\n";
        $end = microtime(true);
        $total ["Time To Clean Photos Directory   "] = round(($end - $start), 2) . "s\n";
        return $total;
    }

    public function swapTempMlsTable($dbTable = 'mlsGridData')
    {
        echo "\nswapTempMlsTable($dbTable)\n";
        $start = microtime(true);
        $dbTable_Temp = $dbTable . '_Temp';
        $dbTable_Old = $dbTable . '_Old';
        $em = $this->em;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->exec("DROP TABLE IF EXISTS $dbTable_Old");
        $em->getConnection()->exec("RENAME TABLE $dbTable TO $dbTable_Old");
        $em->getConnection()->exec("RENAME TABLE $dbTable_Temp TO $dbTable");
        $end = microtime(true);
        $total ["Time To Swap Databases: "] = round(($end - $start) * 1000, 2) . "ms\n";
        return $total;
    }


//=====================================PRIVATE======================================================//

    private function getPhotos($mlsnumber, $details)
    {
        $count = 0;
        $baseDir = $this->mlsgrid['baseDir'];
        $photoDir = $this->mlsgrid['photoDir'];
        if (is_dir($baseDir . $photoDir . $mlsnumber)) {
            array_map('unlink', glob($baseDir . $photoDir . $mlsnumber . '/*.jpg'));
            rmdir($baseDir . $photoDir . $mlsnumber);
        }
        mkdir($baseDir . $photoDir . $mlsnumber);
        if (array_key_exists("Media", $details)) {
            $media = $details['Media'];
            if (is_array($media)) {
                echo "\n     Photo Count " . count($media);
                foreach ($media as $k3 => $v3) {
                    $filename = sprintf("%03d", $v3['Order']) . '.jpg';
                    $this->saveMedia($v3['MediaURL'], $baseDir . $photoDir . $mlsnumber . '/' . $filename);
                    echo "\n        filename: " . $filename;
                    $count++;
                }
            }
        }
        return $count;
    }

    private function getMlsGridArray($url)
    {
        $start = microtime(true);
        //$guzzle = new Client();
        $token = $this->mlsgrid['token'];
        $response = $this->client->request('GET', $url,[
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        $responseJson = json_decode((string)$response->getContent(), true);
        $end = microtime(true);
        $this->time['time_from_mlsGrid'] = round(($end - $start) * 1000, 4) . ' milliseconds';
        return $responseJson;
    }

    private function buildTempMlsTable($dbTable = 'mlsGridData')
    {
        $dbTable_Temp = $dbTable . '_Temp';
        $em = $this->em;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->exec("CREATE TABLE IF NOT EXISTS $dbTable_Temp LIKE $dbTable");
        $em->getConnection()->exec("TRUNCATE TABLE $dbTable_Temp");
    }


    private function replaceMlsTable($dbTable = 'mlsGridData')
    {
        $dbTable_Temp = $dbTable . '_Temp';
        $em = $this->em;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $em->getConnection()->exec("DROP TABLE IF EXISTS $dbTable");
        $em->getConnection()->exec("RENAME TABLE $dbTable_Temp TO $dbTable");
        $em->flush();
        $em->clear();
    }

    private function saveResults($results)
    {//, $class){
        $mlsDataFields = $this->mlsgrid['DataFields'];
        $batch = 250;
        $i = 0;
        $em = $this->em;
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        foreach ($results as $key => $record) {
            $mlsData = new MlsgridData_Temp();
            $this->setArrayData($mlsDataFields, $mlsData, $record);
            $addressFull = $this->utility->getAddressFull(
                (array_key_exists('StreetNumber', $record)) ? $record['StreetNumber'] : '',
                (array_key_exists("StreetDirPrefix", $record)) ? $record['StreetDirPrefix'] : '',
                (array_key_exists("StreetName", $record)) ? ucfirst($record['StreetName']) : '',
                (array_key_exists("StreetSuffix", $record)) ? $record['StreetSuffix'] : '');
            $mlsData->setAddressfull($addressFull);
            //$mlsData->setPropertytype($class);
            $details = json_encode($record);
            $mlsData->setDetails($details);
            /*
            if (key_exists("Media",$record)) {
                $media = json_encode($record['Media']);
                $mlsData->setMedia($media);
            }
            */
            $em->persist($mlsData);
            $i++;
            if ($i % $batch === 0) {
                $em->flush();
                $em->clear();
            }
        }
        $em->flush();
        $em->clear();
    }

//=====================================Media Functions================================================//

    private function getLastPhotoModDate()
    {
        $em = $this->em;

        $query = $em->createQueryBuilder()
            ->select('m.photomodifieddate')
            ->from('App:MlsgridData', 'm')
            ->orderBy('m.photomodifieddate', 'DESC')
            ->setMaxResults(1)
            ->getQuery();
        try {
            $results = $query->getResult(Query::HYDRATE_SINGLE_SCALAR);
            return $results;
        } catch (ORMException $e) {
            return 0;
        }
    }

    private function getMediaArrayListingId_new()
    {
        echo "\nstart getMediaArrayListingId_new()";
        $em = $this->em;
        $pmd = $this->getLastPhotoModDate();
        echo "\n\n  lastPhotoModDate = " . $pmd;
        $results = $em->createQueryBuilder()
            ->select('a.mlsnumber, a.details')
            ->from('App:MlsgridData_Temp', 'a')
            ->where('a.photocount > 0')
            ->andWhere('a.details is not NULL')
            ->andWhere('a.photomodifieddate > :pmd')
            ->setParameter('pmd', $pmd)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        echo "\n  Number of Properties with new photos : " . count($results);
        echo "\n\nfinish getMediaArrayListingId_new()";
        return $results;
        //return $this->flatten($results,'mlsnumber');
    }

    private function getMediaArrayListingId_all()
    {
        $em = $this->em;
        $results = $em->createQueryBuilder()
            ->select('a.mlsnumber, a.details')
            ->from('App:MlsgridData', 'a')
            ->where('a.photocount > 0')
            ->andWhere('a.details is not NULL')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        return $results;
        //return $this->flatten($results,'mlsnumber');
    }

    private function getMediaArrayByMls($mlsnumber)
    {
        $em = $this->em;
        $results = $em->createQueryBuilder()
            ->select('a.details')
            ->from('App:MlsgridData', 'a')
            ->where('a.photocount > 0')
            ->andWhere('a.details is not NULL')
            ->andWhere('a.mlsnumber = :mlsnumber')
            ->setParameter('mlsnumber', $mlsnumber)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        return $results;
        //return $this->flatten($results,'mlsnumber');
    }

    private function flatten($a, $n)
    { //$a = array; $n = name/field to make the primary value
        $o = [];
        foreach ($a as $k => $v) {
            $o[$k] = $v[$n];
        }
        return $o;
    }

    private function saveMedia($urlFrom, $pathFile)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlFrom);
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $result = curl_exec($ch);
        curl_close($ch);
        $fp = fopen($pathFile, 'wb');
        fwrite($fp, $result);
        fclose($fp);
        $result = null;
        return;
    }

//=================================End Media Functions================================================//

    private function setArrayData(array $fields, $entity, $array)
    {
        if (!is_array($array)) {
            return false;
        }
        foreach ($fields as $key => $item) {
            if (array_key_exists($key, $array)) {
                $function = 'set' . ucfirst($item);
                $data = trim(stripslashes($array[$key]));
                $entity->$function($data);
            }
        }
    }

    private function getAddressFull($houseNumber = '', $streetDir = '', $streetName = '', $streetSuffix = '')
    {

        switch ($streetDir) {
            case "NE":
                $streetDirPrefix = "Northeast";
                break;
            case "SE":
                $streetDirPrefix = "Southeast";
                break;
            case "SW":
                $streetDirPrefix = "Southwest";
                break;
            case "NW":
                $streetDirPrefix = "Northwest";
                break;
            default:
                $streetDirPrefix = $streetDir;
        }
        if ($streetSuffix == 'N/A' || $streetSuffix == 'XX') {
            $streetSuffix = '';
        }
        $addressFull = trim(trim($houseNumber . ' ' . $streetDirPrefix) . ' ' . trim($streetName) .
            ' ' . trim($streetSuffix));
        return $addressFull;
    }

    private function getLastTimestamp($field = 'photomodifieddate')
    {
        //for MlsgridData use 'modtimestamp'; for photos use 'photomodifieddate'
        $em = $this->em;
        $record = $em->getRepository('App:MlsgridData')->findOneBy(['standardstatus' => 'Active'], [$field => 'DESC']);
        if ($record === NULL) {
            return false;
        }
        $function = 'get' . ucfirst($field);
        $lastTimestamp = $record->$function();
        return $lastTimestamp;
    }
}
