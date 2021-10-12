<?php

namespace App\Controller;

use App\Model\Browse;
use App\Model\Mortgage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use App\Model\Utility;

class BrowseController extends AbstractController
{
    protected $utility;
    protected $browse;
    protected $mortgage;

    public function __construct(Utility $utility,Browse $browse, Mortgage $mortgage){
        $this->utility = $utility;
        $this->browse  = $browse;
        $this->mortgage= $mortgage;
    }

    /**
     * @Route("/browse/property/{propertyType}", name="browse")
     */
    public function mlsAction(Request $request)
    {

        $propertyType = ucwords($request->get('propertyType'));
        $database="App:MlsgridData";
        $pagination = $this->browse->browsePropertyType($propertyType,
            $request->query->getInt('page', 1), $request->query->getInt('limit', 24),$database);
        $mortgageArray = $this->mortgage->getMortgage_paginationItems($pagination->getItems());
        $thumbsArray = $this->browse->getThumbnailFilenames_Array($pagination->getItems());
        $mlsControl['dateMlsDataUpdate'] = $this->utility->getLastHmlsControl_dateUpdated();

        return $this->render('browse/browse.html.twig', [
            'propertyType' => ucfirst($propertyType),
            'pagination' => $pagination,
            'mortgage' =>$mortgageArray,
            'thumbnail' =>$thumbsArray,
            'mlsControl' => $mlsControl,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);

    }

    /**
     * @Route("/browse/{propertyType}", name="browseSpecial")
     */
    public function browseAction(Request $request)
    {

        $propertyType = $request->get('propertyType');
        $database="App:MlsgridData";
        $search = $this->utility->getParameter("Search");
        $browseType = "";
        switch($propertyType){
            case "focus":
                $pagination = $this->browse->browseByFocus(
                $request->query->getInt('page', 1), $request->query->getInt('limit', 24),$database);
                $browseType = $search['Focus']['name'];
                break;
            case "office":
                $pagination = $this->browse->browseBySearchTerm(
                    "officeid",$search['Office']['id'],
                    $request->query->getInt('page', 1), $request->query->getInt('limit', 24),$database);
                $browseType = $search['Office']['name'];
                break;
            default:
                $pagination = $this->browse->browsePropertyType('All',
                    $request->query->getInt('page', 1), $request->query->getInt('limit', 24),$database);
        }

        $mortgageArray = $this->mortgage->getMortgage_paginationItems($pagination->getItems());
        $thumbsArray = $this->browse->getThumbnailFilenames_Array($pagination->getItems());
        $mlsControl['dateMlsDataUpdate'] = $this->utility->getLastHmlsControl_dateUpdated();

        return $this->render('browse/browse.html.twig', [
            'propertyType' => $browseType,
            'pagination' => $pagination,
            'mortgage' =>$mortgageArray,
            'thumbnail' =>$thumbsArray,
            'mlsControl' => $mlsControl,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);

    }


}