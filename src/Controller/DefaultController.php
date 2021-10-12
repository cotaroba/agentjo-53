<?php

namespace App\Controller;


use App\Model\Utility;
use App\Model\Browse;
use App\Model\Mortgage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController
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
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $search = $this->utility->getParameter("Search");
        $database="App:MlsgridData";
        $pagination = $this->browse->browseByFocus(
            $request->query->getInt('page', 1), $request->query->getInt('limit', 24),$database);
        $searchTerm  = $search['Focus']['name'];
        $mortgageArray = $this->mortgage->getMortgage_paginationItems($pagination->getItems());
        $thumbsArray = $this->browse->getThumbnailFilenames_Array($pagination->getItems());
        $mlsControl['dateMlsDataUpdate'] = $this->utility->getLastHmlsControl_dateUpdated();
        return $this->render('browse/browse.html.twig', [
            'propertyType' => $searchTerm,
            'pagination' => $pagination,
            'mortgage' =>$mortgageArray,
            'thumbnail' =>$thumbsArray,
            'mlsControl' => $mlsControl,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/config", name="config")
     */
    public function configAction(Request $request)
    {
        return phpinfo();
    }
}