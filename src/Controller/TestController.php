<?php
//DO NOT UPLOAD THIS TO THE PRODUCTION SERVER

namespace App\Controller;

use App\Model\GeoData;
use App\Model\Mortgage;
use App\Model\Update;
use App\Model\Utility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\Browse;

class TestController extends AbstractController
{
    protected $utility;
    protected $browse;
    protected $mortgage;
    protected $update;
    protected $geo;

    public function __construct(Utility $utility, Browse $browse, Mortgage $mortgage, Update $update, GeoData $geo){
        $this->utility = $utility;
        $this->browse  = $browse;
        $this->mortgage= $mortgage;
        $this->update = $update;
        $this->geo = $geo;
    }


    /**
     * @Route("/test", name="test")
     */
    public function testAction(Request $request)
    {

        $mlsgrid = $this->geo->setGeoData_all();
        return $this->render('default/index.html.twig', [
            'mlsgrid' => $mlsgrid,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);

    }


}
