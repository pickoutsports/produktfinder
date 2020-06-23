<?php

namespace AppBundle\Lib;

use AppBundle\Model\Logo;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\SocialMedia;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class BaseController extends FrontendController{

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $this->setViewAutoRender($event->getRequest(),true,'twig');
        $logo = Logo::getByName("logo")->current();
        $this->view->logo = $logo;
        $socials = SocialMedia::getList();
        $this->view->socials = $socials;
    }
}
