<?php

namespace AppBundle\Controller;

use AppBundle\Lib\BaseController;
use AppBundle\Model\Logo;
use Pimcore\Model\DataObject\Backgroundimage;
use Pimcore\Model\DataObject\Newsletter;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Provider;
use Pimcore\Model\DataObject\SocialMedia;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

// Use API site scope.
//define('RW_SDK__API_SCOPE', 'site');

// Modify the following definitions to your site details. Für die Sternebewertung in google.
//define('RW_SDK__SITE_ID',         '438456');
//define('RW_SDK__SITE_PUBLIC_KEY', 'c88cc785fdee0f454a244856aacf835e');
//define('RW_SDK__SITE_SECRET_KEY', 'Di(dlPruvNa<]#>j[DoDb>t+<Xe<exDO');
class DefaultController extends BaseController
{
    /**
     * @param Request $request
     * @throws \Exception
     */
    public function contentAction(Request $request){

        //to get Providers in Pimcore Backend (Document)
        $providers = Provider::getList();
        $this->view->providers = $providers;
    }

    /**
     * @Route("/index", name="index")
     * @param Request $request
     * @throws \Exception
     */
    public function defaultAction(Request $request, Session $session){

        //TODO Dies ist keine schöne Lösung. Muss schöner gelöst werden
        $backgroundImage = Backgroundimage::getByName("background")->current();
        $logo = Logo::getByName("logo")->current();

        //When opening the home page, the session is started
        $session->start();
        $session->set('category', 0);

        $providers = Product::getList();
        $this->view->providers = $providers;
        $this->view->backgroundImage = $backgroundImage;
        $this->view->logo = $logo;
        $socials = SocialMedia::getList();
        $this->view->socials = $socials;
    }

    /**
     * @Route("/landingpage", name="landingpage")
     * @param Request $request
     * @throws \Exception
     */
    public function landingpageAction(Request $request){

        //initialize RatingWidget to get star ratings
        /*$rw_api = new \RatingWidget\Api\Sdk\RatingWidget(
            RW_SDK__API_SCOPE,
            RW_SDK__SITE_ID,
            RW_SDK__SITE_PUBLIC_KEY,
            RW_SDK__SITE_SECRET_KEY
        );

        $ratings = $rw_api->Api('/ratings.json?fields=id,external_id,approved_count,avg_rate&count=100');
        $ratingsArray = [];

        //save ratings in array
        foreach($ratings as $rating){
            foreach($rating as $element){
                $ratingsArray[] = ["id"=>$element->id,"externalId" => $element->external_id,"approved_count" => $element->approved_count,"avg_rate"=>$element->avg_rate];
            }
        }*/

        $providers = Provider::getList();
        $this->view->providers = $providers;
        //$this->view->ratings = $ratingsArray;

    }
}
