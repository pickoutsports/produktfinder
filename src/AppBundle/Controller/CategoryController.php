<?php

namespace AppBundle\Controller;

use AppBundle\Lib\BaseController;
use AppBundle\Model\Category;
use AppBundle\Model\Logo;
use AppBundle\Model\Sporttype;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject\SocialMedia;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class CategoryController extends BaseController{

    /**
     * @param Request $request
     * @param Session $session
     * @Route("/category", name="category")
     * @return string
     * @throws \Exception
     */
    public function indexAction(Request $request,Session $session){

        $category = [];
        $categories = [];

        $cat = Category::getList();

        $categoriesssession = $session->get("categories");

        $data = $request->request->all();

        if($data){
            foreach($data as $id){
                $categories[$id] = ["id" => $id,"shown" => 0, "propertyContent" => []];
            }

            $session->set("categories", $categories);

            return $this->redirectToRoute('props',array(
                'categories' => $categories
            ));
        }

        if($categoriesssession){
            foreach($categoriesssession as $type){
                $category[] = \Pimcore\Model\DataObject\Category::getById($type["id"]);
            }
        }

        $this->view->category = $category;
        $this->view->cat=$cat;
        //$logo = Logo::getByName("logo")->current();
        //$this->view->logo = $logo;
        //$socials = SocialMedia::getList();
        //$this->view->socials = $socials;
    }
}
