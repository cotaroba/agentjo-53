<?php

namespace App\Controller;

use App\Model\Listing;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ListingController extends AbstractController
{
    protected $listing;

    public function __construct(Listing $listing){
        $this->listing = $listing;
    }

    /**
     * @Route("/listing/{mlsnumber}", name="mlsDisplay")
     */
    public function mlsAction(Request $request)
    {
        $database="App:MlsgridData";
        $template="listing/listing.html.twig";
        $mlsnumber = $request->get('mlsnumber');
        $listing=$this->listing->getMlsListing($mlsnumber,$database);
        if (!$listing){
            $this->get('session')->getFlashBag()->add('error', 'The property you are looking for cannot be found.');
            return $this->redirect($this->generateUrl('homepage'));
        }
        // $mlsControl['dateMlsDataUpdate'] = $this->container->get('App\Model\Utility')->getLastHmlsControl_dateUpdated();
        return $this->render('listing/listing.html.twig', [
            'listing' => $listing, 'list' => json_encode($listing),
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
}
