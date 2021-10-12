<?php

namespace App\Controller;

use App\Model\Search;
use App\Model\Utility;
use App\Model\Browse;
use App\Model\Mortgage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class SearchController extends AbstractController
{
    protected $utility;
    protected $browse;
    protected $mortgage;
    protected $search;

    public function __construct(Search $search,Utility $utility,Browse $browse, Mortgage $mortgage){
        $this->search   = $search;
        $this->utility  = $utility;
        $this->browse   = $browse;
        $this->mortgage = $mortgage;
    }

    /**
     * @Route("/search/results", name="searchResults" )
     */
    public function searchResultsAction(Request $request)
    {
        $lastSearch = ($request->cookies->get('lastSearch') !== null) ? unserialize($request->cookies->get('lastSearch')) : [];
        $searchQuery = $this->search->searchQuery($lastSearch);
        $searchTerm = $this->search->getSearchTerm($lastSearch);
        $pagination = $this->search->searchResults($searchQuery,
            $request->query->getInt('page', 1), $request->query->getInt('limit', 24));
        $mortgageArray = $this->mortgage->getMortgage_paginationItems($pagination->getItems());
        $thumbsArray = $this->browse->getThumbnailFilenames_Array($pagination->getItems());
        $mlsControl['dateMlsDataUpdate'] = $this->utility->getLastHmlsControl_dateUpdated();
        return $this->render('browse/browse.html.twig', [
            'searchTerm' => $searchTerm,
            'pagination' => $pagination,
            'mortgage' =>$mortgageArray,
            'thumbnail' =>$thumbsArray,
            'mlsControl' => $mlsControl,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


     /**
     * @Route("/search/advance", name="advanceSearch")
     */
    public function advanceSearchAction(Request $request)
    {
        return $this->render('search/advanceSearch.html.twig');
    }

    /**
     * @Route("/search/clear", name="clearSearch")
     */
    public function clearSearchAction(Request $request)
    {
        $response = new Response;
        $response->headers->clearCookie('lastSearch');
        $response->sendHeaders();
        return $this->redirect($this->generateUrl('advanceSearch'));
    }

    /**
     * @Route("/search/api/{odata}", name="searchAPI")
     */
    public function apiCall(Request $request)
    {
        $odata = $request->get('odata');
        //$data = $request->toArray();
        if ($odata == "\$init") {
            $parse = ($request->cookies->get('lastSearch') !== null) ? unserialize($request->cookies->get('lastSearch')) : [];
        } else {
            $parse = $this->search->parseOdata($odata);
        }

        $SearchParameter = $this->utility->getParameter("Search");
        $dropdown = $SearchParameter['Dropdown'];
        $focus = $SearchParameter['Focus'];
        $searchTerm = $this->search->getSearchTerm($parse);
        foreach ($dropdown as $k => $v) {
            $dropdown[$k] = $this->search->getDropDown($k, $parse);
        }
        $count = $this->search->getCount($parse);

        $response = new Response;
        $search = serialize($parse);
        $cookie = Cookie::create('lastSearch')->withValue($search);
        $response->headers->setCookie($cookie);
        $response->sendHeaders();
        return $this->json(["searchTerm" => $searchTerm, "dropdown" => $dropdown, "search" => $parse, "count" => $count, "dropdownList" => $SearchParameter['Dropdown'], "focus" => $focus]);
    }

}
