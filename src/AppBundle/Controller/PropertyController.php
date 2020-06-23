<?php

namespace AppBundle\Controller;

use AppBundle\Lib\BaseController;
use AppBundle\Model\Logo;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\SocialMedia;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class PropertyController extends BaseController{

    /**
     * @Route("/props", name="props")
     * @param Session $session
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function indexAction(Session $session,Request $request){

        $valid = $request->get('valid');

        if($valid == true){
            $this->view->valid = $valid;
        }

        $logo = Logo::getByName("logo")->current();
        $this->view->logo = $logo;
        $socials = SocialMedia::getList();
        $this->view->socials = $socials;


        $categories = $request->get('categories');

        $sporttypeId = 0;

        // Search the next Id with the value true and set sportTypeId
        if($categories){
            foreach($categories as &$type){
                if($type["shown"] == 0){ // Was ist shown?

                    $sporttypeId = $type["id"];
                    $type["shown"] = 1;

                    break;
                }
            }
        }

        $viewData = $request->request->all();
        if(!empty($viewData)){
            $this->view->viewData = $viewData;
        }

        $this->view->categories = $categories;

        //If sportTypeid isnt set, redirect to Provider
        if($sporttypeId){
            $category = Category::getById($sporttypeId);
            $subcategories = $category->getSubcategories();

            foreach($subcategories as $value){
                $landName = $value->getName();
                $flag = $value->getFlag();
                $landId = $value->getId();

                $property = $value->getProps();
                foreach($property as $element){
                    if($element->getType() !== "folder"){
                        $propertyName = $element->getName();
                        $propertyType = $element->getCategory()->getName();
                        $propertyDescription = $element->getDescription();
                        $propertyItems[] = ["name"=> $propertyName, "id" =>$element->getId(), "type"=>$propertyType, "toolTip" => $propertyDescription];
                    }elseif($element->getType() === "folder"){
                        foreach($element->getChildren() as $item){

                            $propertyName = $item->getName();
                            if($item->getCategory()){
                                $propertyType = $item->getCategory()->getName();
                            }
                            $propertyDescription = $item->getDescription();
                            $propertyItems[] = ["name"=> $propertyName, "id" =>$item->getId(), "type"=>$propertyType, "toolTip" => $propertyDescription];
                        }
                    }
                }

                $propertyValues[] = ["name" => $landName, "flag"=> $flag, "id" => $landId,"leagueMeta" => $propertyItems];
                $propertyItems = [];
            }

            $this->view->category = $category;
            $this->view->propertyValues = $propertyValues;

        }else{
            $valid = true;
            foreach ($categories as $value){
                if($value["propertyContent"]){
                    $valid = false;
                }
            }
            return $this->redirectToRoute('product',array(
                'categories' => $categories,
                'valid' => $valid
            ));
        }
    }
}
