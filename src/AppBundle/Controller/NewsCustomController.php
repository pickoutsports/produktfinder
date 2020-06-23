<?php

namespace AppBundle\Controller;

use AppBundle\Lib\BaseController;
use AppBundle\Model\Newsletter;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\Session;

class NewsCustomController extends BaseController{

    /**
     * @Route("/newsletter", name="newsletter")
     * @param Request $request
     * @throws \Exception
     */
    public function defaultAction(Request $request, Session $session){

        // get newsletter by token
        $url = $_GET['token'];

        $newsletter = Newsletter::getByToken($url)->current();

        if($newsletter){

            $unsubscribe = md5(uniqid());
            $newsletter->setUnsubscribe($unsubscribe);
            $newsletter->setDeleteLink("https://www.pickoutsports.de/newsletterdelete?token=".$unsubscribe);

            $newsletter->setIsAbo(true);
            $newsletter->save();

            $this->addFlash('success', 'Du wirst ab jetzt regelmäßig interessante News von uns erhalten.');
        }else{
            $this->addFlash('warning', 'Tut uns leid. Dein Token ist uns nicht bekannt.');
        }
    }

    /**
     * @Route("/newsletter/delete", name="newsletterdelete")
     * @param Request $request
     */
    public function deleteAction(Request $request){

        $url = $_GET['token'];

        $newsletter = Newsletter::getByToken($url)->current();

        if($newsletter){
            $newsletter->delete();
        }
    }

}
