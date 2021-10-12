<?php
/**
 * Created by PhpStorm.
 * User: cotaroba2
 * Date: 10/3/18
 * Time: 14:31
 */

namespace App\Model;


use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\Utility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Browse
{
    protected $em;
    protected $paginator;
    protected $utility;
    protected $request;
    protected $mlsgrid;


    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator,
                                Utility $utility)
    {
        $this->em = $em;
        $this->paginator = $paginator;
        $this->utility  = $utility;
        $this->request =  Request::createFromGlobals();
        $this->mlsgrid = $this->utility->getParameter('mlsgrid');
    }

    public function browsePropertyType($propertyType, $page = 1, $limit = 24,$database='App:MlsgridData')
    {
        $sort = $this->utility->getSort();
        $query = $this->utility->baseQuery($sort,$database);
        if ($propertyType != 'All'){
            $query->andWhere('d.propertytype = :propertyClass')
                ->setParameter('propertyClass', $propertyType)
                ->getQuery();
        }
        $pagnation = $this->paginate($query, $page, $limit, $sort);
        return $pagnation;
    }

    public function browseBySearchTerm($field, $search, $page = 1, $limit = 24,$database='App:MlsgridData')
    {
        $sort = $this->utility->getSort();
        $query = $this->utility->baseQuery($sort,$database);
        $query
            ->setMaxResults($limit)
            ->andWhere("d.$field = :search")
            ->setParameter('search', $search)
            ->getQuery();
        $pagnation = $this->paginate($query, $page, $limit, $sort);

        return $pagnation;
    }

    public function browseByFocus($page = 1, $limit = 24,$database='App:MlsgridData')
    {
        $sort = $this->utility->getSort();
        $query = $this->utility->baseQuery($sort,$database);
        $search = $this->utility->getParameter("Search");
        $q="";
        if (key_exists('Focus',$search)) {
            foreach ($search['Focus']['counties'] as $key => $val) {
                $q .= "(d.county ='" . $val . "') OR ";
            }
            $q = "(" . substr($q, 0, -4) . ")";
            $query->andWhere($q);
        }
        $query
            ->setMaxResults($limit)
            ->getQuery();
        $pagnation = $this->paginate($query, $page, $limit, $sort);

        return $pagnation;
    }

    public function getThumbnailFilenames_Array(array $items){
        $thumbnailsArray = [];
        if (!is_array($items)){
            return $thumbnailsArray;
        }
        foreach ($items as $key => $val){
            $firstPhoto = $this->getFirstPhoto($val['mlsnumber']);
            if ($firstPhoto !== false){
                $thumbnailsArray[$val['mlsnumber']] = $firstPhoto;
            } else {
                $thumbnailsArray[$val['mlsnumber']] = '';
            }
        }
        return $thumbnailsArray;
    }

    private function paginate($query, $page = 1, $limit = 24)
    {
        $paginator = $this->paginator->paginate($query, $page,  $limit);
        $paginator->setCustomParameters($this->utility->getSort());
        return $paginator;
    }

    private function getFirstPhoto($mlsnumber){
        $photoDir = $this->mlsgrid['baseDir'].$this->mlsgrid['photoDir'];
        if (!file_exists($photoDir.$mlsnumber)) { return false;}
        $scanDir  = scandir($photoDir.$mlsnumber);
        $photoArray = array_diff($scanDir, array('.', '..'));
        rsort($photoArray);
        $filename = array_pop($photoArray);
        if (file_exists($photoDir.$mlsnumber."/".$filename) ){
            return $this->mlsgrid['photoDir'].$mlsnumber."/".$filename;
        } else {
            return false;
        }

    }
}