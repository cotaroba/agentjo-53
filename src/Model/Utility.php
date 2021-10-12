<?php
/**
 * Created by PhpStorm.
 * User: cotaroba2
 * Date: 10/7/18
 * Time: 21:02
 */

namespace App\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;


class Utility
{
    protected $em;
    protected $router;
    protected $container;
    protected $request;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $router, ContainerInterface $container)
    {
        $this->em = $em;
        $this->router = $router;
        $this->container = $container;
        $this->request = Request::createFromGlobals();

    }

    public function getPhotoArray($listingID)
    {
        $em = $this->em;
        $results = $em->createQueryBuilder()
            ->select('a.details')
            ->from('App:MlsgridData', 'a')
            ->where('a.mlsnumber = :listingID')
            ->andWhere('a.photocount > 0')
            ->setParameter('listingID', $listingID)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        $details = json_decode($results[0]['details'],true);
        if (key_exists("Media",$details)){
            return $details['Media'];//json_decode($media['media'],true);
        }
    }

    public function returnPhotoFileName($listingID)
    {
        $photoArray = $this->getPhotoArray($listingID);
        $mlsgrid = $this->getParameter('mlsgrid');
        $photoDir = $mlsgrid['photoDir'];
        $baseDir = $mlsgrid['baseDir'];
        $return = [];
        foreach ($photoArray as $k => $v) {
            $filename = $listingID . '/' . sprintf("%03d", $v['Order']) . '.jpg';
            if (file_exists($baseDir.$photoDir.$filename)){
                $return[$k]=$filename;
            }

        }
        return $return;
    }

    public function getParameter($name){
        $parameter = $this->container->getParameter($name);
        return $parameter;
    }

    public function getPhotos_FilenamesArray_path($mlsNumber, $path){
        $pattern = $path . $mlsNumber . '_???.jpg';
        $returnArray = glob($pattern);
        return $returnArray;
    }

    public function getPhotos_FilenameArray_derived($mlsnumber, $photocount)
    {
        $i = 0;
        while ($i < $photocount) {
            $filename[$i] = $mlsnumber . '_' . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . '.jpg';
            $i++;
        }
        return $filename;
    }

    public function getBreadcrumbs($currentPage){

        $links = $this->getParents($currentPage);
        $breadcrumb=[];
        foreach ($links as $k => $v){
            if ($currentPage != $v['pagename']) {
                $breadcrumb[$k]['name'] = $v['pagetitle'];
                if ($v['pageparent'] !== NULL){
                    $breadcrumb[$k]['url'] = $this->router->generate('page',array('pageName'=>$v['pagename']));

                } else {
                    $breadcrumb[$k]['url'] = $this->router->generate('homepage');
                }
            }
        }
        return array_reverse($breadcrumb);
    }

    public function getLastHmlsControl_dateUpdated(){
        $em = $this->em;
        $query = $em->createQueryBuilder()
            ->select('m.modtimestamp')
            ->from('App:MlsgridData', 'm')
            ->orderBy('m.modtimestamp', 'DESC')
            ->setMaxResults(1)
            ->getQuery();
        try {
            $results = $query->getResult(Query::HYDRATE_SINGLE_SCALAR);
            return $results;
        }
        catch (ORMException $e)
        {
            return 0;
        }
    }

    public function getLastHmlsControl_dateLastPhotoMod(){
        $em = $this->em;
        $query = $em->createQueryBuilder()
            ->select('m.dateLastPhotoMod')
            ->from('App:Hmlscontrol', 'm')
            ->orderBy('m.dateUpdated', 'DESC')
            ->setMaxResults(1)
            ->getQuery();
        try {
            $results = $query->getResult(Query::HYDRATE_SINGLE_SCALAR);
            return $results;
        }
        catch (ORMException $e)
        {
            return 0;
        }
    }

    private function getParents($currentPage){
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('App:Pages', 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'pageName', 'pagename');
        $rsm->addFieldResult('p', 'pageTitle', 'pagetitle');
        $rsm->addFieldResult('p', 'pageContent', 'pagecontent');
        $rsm->addFieldResult('p', 'pageParent', 'pageparent');
        $rsm->addFieldResult('p', 'dateMod', 'datemod');
        $query= $this->em->createNativeQuery('
WITH RECURSIVE breadcrumbs AS (SELECT * FROM pages WHERE pageName=?
UNION
        SELECT p.* FROM pages AS p, breadcrumbs AS b
        WHERE p.pageName = b.pageParent
    )
SELECT * FROM breadcrumbs',$rsm);
        $query->setParameter(1, $currentPage);
        $links = $query->getResult(Query::HYDRATE_ARRAY);
        return $links;
    }


    public function baseQuery($sort = NULL)
    {
        $em = $this->em;
        $query =
            $em->createQueryBuilder()
                ->select('d.mlsnumber, d.addressfull, d.city, d.state, d.zip, d.subdivision, d.bookingsection, 
             d.totalsqft, d.propertytype, d.modtimestamp, d.agentname, d.officename, d.modtimestamp,
             d.listprice, d.bathsfull, d.bathshalf, d.bedrooms, d.taxes, d.county, d.comments, d.photocount,
             d.photocount as actualPhotoCount')
                ->from("App:MlsgridData", 'd');
        if (is_array($sort)){
            $query->addOrderBy($sort['field'],$sort['direction']);
        }
        return $query;
    }


    public function getSort(){
        $sort = [];
        switch (strtolower($this->request->query->get('s'))){
            case 'price':
                $sort['field'] = 'd.listprice';
                $sort['fieldText'] = 'Price';
                break;
            default:
                $sort['field'] = 'd.modtimestamp';
                $sort['fieldText'] = 'Date Modified';
        }
        switch (strtolower($this->request->query->get('d'))){
            case 'asc':
                $sort['direction'] = 'ASC';
                $sort['directionText'] = 'Ascending';
                break;
            default:
                $sort['direction'] = 'DESC';
                $sort['directionText'] = 'Descending';
        }
        return $sort;
    }

    public function getAddressFull($houseNumber = '', $streetDir = '', $streetName = '', $streetSuffix = ''){

        switch ($streetDir){
            case "NE": $streetDirPrefix = "Northeast"; break;
            case "SE": $streetDirPrefix = "Southeast"; break;
            case "SW": $streetDirPrefix = "Southwest"; break;
            case "NW": $streetDirPrefix = "Northwest"; break;
            default: $streetDirPrefix = $streetDir;
        }
        if ($streetSuffix == 'N/A' ||$streetSuffix == 'XX' ){ $streetSuffix =''; }
        $addressFull = trim (trim($houseNumber . ' ' . $streetDirPrefix) . ' '. trim($streetName) .
            ' ' . trim($streetSuffix));
        return $addressFull;
    }

}