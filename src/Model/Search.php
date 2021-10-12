<?php

namespace App\Model;


use Doctrine\ORM\Query;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class Search
{
    protected $em;
    protected $paginator;
    protected $request;
    protected $utility;


    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator,
                                Utility $utility)
    {
        $this->em = $em;
        $this->paginator = $paginator;
        $this->utility = $utility;
        $this->request = Request::createFromGlobals();

    }

    public function searchResults($query, $page = 1, $limit = 12)
    {
        $paginator = $this->paginator->paginate($query, $page,  $limit);
        $paginator->setCustomParameters($this->utility->getSort());
        return $paginator;
    }


    public function searchQuery(array $lastSearch)
    {
        $sort = $this->utility->getSort();
        $query = $this->utility->baseQuery($sort);
        return $this->getSearch($lastSearch,$query);
    }

    public function searchTerm($lastSearch)
    {
        return $this->getSearchTerm(unserialize($lastSearch));
    }


    public function getSearchTerm($search){
        $searchTerm = '';
        $dropdown = $this->utility->getParameter("Search");
        $fieldName = $dropdown['Dropdown'];
        if (isset($search)) {
            if (key_exists('photosOnly',$search) && (bool)$search['photosOnly'] == true){
                $searchTerm .= "Listings with Photos, ";
            }
            if (key_exists('focus',$search) && (bool)$search['focus'] == true){
                $searchTerm .= "Listings in " . $dropdown['Focus']['name'].", ";
            }
            if (key_exists('maxprice',$search) && $search["maxprice"] !== null) {
                $searchTerm .= "Price is less than $" . number_format(floatval($search['maxprice']), 0, ".", ",") . ", ";
            }
            if (key_exists('mlsnumber',$search) && $search["mlsnumber"] !== null) {
                $searchTerm .= "MLS Number or Address containing '" . $search['mlsnumber'] . "', ";
            }
            foreach ($fieldName as $key => $val) {
                if (key_exists($key,$search) && $search[$key] !== null) {
                    $searchTerm .= $val . " is '" . $search[$key] . "', ";
                }
            }
        }
        $searchTerm = substr($searchTerm, 0, -2);
        return ($searchTerm === false) ? "All Listings" : $searchTerm;
    }

    private function getSearch($search,$query){

        $dropdown = $this->utility->getParameter("Search");
        $fieldName = $dropdown['Dropdown'];

        if (isset($search)) {
            if (key_exists('photosOnly',$search) && (bool)$search['photosOnly'] === true){
                $query->andWhere("d.photocount > 0");
            }
            if (key_exists('focus',$search) && (bool)$search['focus'] == true){
                $q="";
                foreach($dropdown['Focus']['counties'] as $key=>$val){
                    $q.="(d.county ='" .$val. "') OR ";
                }

                $q = "(" . substr($q,0,-4) .")";
                $query->andWhere($q);
            }
            if (key_exists('maxprice',$search) && $search["maxprice"] !== null) {
                $query->andWhere("d.listprice <= :maxprice");
                $query->setParameter('maxprice', intval($search['maxprice']));
            }
            if (key_exists('mlsnumber',$search) && $search["mlsnumber"] !== null) {
                $query->andWhere("(d.mlsnumber LIKE :mlsnumber OR d.addressfull LIKE :addressfull)");
                $query->setParameter('mlsnumber', "%" . $search['mlsnumber'] . "%");
                $query->setParameter('addressfull', "%" . $search['mlsnumber'] . "%");
            }
            foreach ($fieldName as $key => $val) {
                if (key_exists($key,$search) && $search[$key] !== null) {
                    $query->andWhere("d.$key = :$key");
                    $query->setParameter($key, $search[$key]);
                }
            }
        }
        $query->getQuery();
        return $query;
    }

    function getCount($lastSearch){
        $query = $this->em->createQueryBuilder()
            ->select("COUNT (DISTINCT (d.mlsnumber))")
            ->from("App:MlsgridData","d");
        $getSearch = $this->getSearch($lastSearch,$query);
        $rs = $getSearch
            ->getQuery()
            ->getResult(Query::HYDRATE_SINGLE_SCALAR);
        return $rs;
    }

    function getDropDown($column,$lastSearch)
    {
        $dropDown = [];
        $query = $this->em->createQueryBuilder()
            ->select("DISTINCT d.$column")
            ->from('App:MlsgridData', 'd')
            ->orderBy("d.$column","ASC");
        $getSearch = $this->getSearch($lastSearch,$query);
        $rs = $getSearch
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        if (is_array($rs)) {
            foreach($rs as $val){
                if (key_exists($column,$val)) {
                    $rsVal = $val[$column];
                    if ($rsVal <> '') {
                        $dropDown[$rsVal] = $rsVal;
                    }
                }
            }
        }
        return $dropDown;
    }

    public function parseOdata($url){
        $base = $url;
        $p = explode("&",$url);
        forEach($p as $k=>$v){
            $c = explode("=",$v);
            if ($c[0] === "\$filter") { $filter = $this->parseFilter($c[1]); }
        }
        return $filter;
    }

    private function parseFilter($str){
        $q = [];
        if ($str === "") return $q;
        $p = explode(" and ",$str);
            foreach ($p as $k => $v) {
                $t = $this->getQueryPart($v);
                if (is_array($t)) {
                    $q += $t;
                }
            }
        return $q;
    }

    private function getQueryPart($str){
        $p = explode(" ",$str,3);
        switch ($p[1]) {
            case "eq" : $op = "="; break;
            case "gt" : $op = ">"; break;
            case "lt" : $op = "<"; break;
            case "ge" : $op = ">="; break;
            case "le" : $op = "<="; break;
            case "in" : $op = "in"; break;
            default: return false;
        }
        $a[$p[0]]=$p[2];
        return $a;
    }

}