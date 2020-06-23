<?php

namespace AppBundle\Controller;

use AppBundle\Lib\BaseController;
use Pimcore\Model\DataObject\Blog;
use Pimcore\Model\DataObject\Pressmaterial;
use Pimcore\Model\DataObject\SocialMedia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;


class FooterController extends BaseController{

    /**
     * @Route("/Footer/datenschutz", name="datenschutz")
     * @param Request $request
     */
    public function datenschutzAction(Request $request){

    }

    /**
     * @Route("/Footer/impressum", name="impressum")
     * @param Request $request
     */
    public function impressumAction(Request $request){

    }

    /**
     * @Route("/Footer/kontakt", name="kontakt")
     * @param Request $request
     */
    public function kontaktAction(Request $request){

    }

    /**
     * @Route("/Footer/presse", name="presse")
     * @param Request $request
     * @throws \Exception
     */
    public function presseAction(Request $request){

        $materials = Pressmaterial::getList();
        $this->view->materials = $materials;


    }

    /**
     * @Route("/downloads", name="downloads")
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function downloadAction(Request $request) {
        $object = Pressmaterial::getById($request->get("id"));
        $response = new BinaryFileResponse($object->getAsset()->getFileSystemPath());
        $response->headers->set('Content-Type', $object->getAsset()->getMimeType());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $object->getAsset()->getKey()
        );

        return $response;
    }
}
