<?php

namespace AppBundle\Controller;

use AppBundle\Lib\BaseController;
use AppBundle\Model\Email;
use AppBundle\Model\Property;
use AppBundle\Model\Newsletter;
use AppBundle\Model\Logo;
use AppBundle\Model\SocialMedia;
use AppBundle\Model\Category;
use AppBundle\Model\Product;
use Pimcore\Mail;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\Url;

class ProductController extends BaseController{

    /**
     * @Route("/product", name="product")
     * @param Request $request
     * @param Session $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function indexAction(Request $request, Session $session){

        $logo = Logo::getByName("logo")->current();
        $this->view->logo = $logo;
        $socials = SocialMedia::getList();
        $this->view->socials = $socials;

        //token for email
        $token = $request->query->get('token');

        $categories = $request->get('categories');

        $valid = $request->get('valid');

        $propertyIds = [];



        if($categories){

            //change shown to 0, if the user can go back to leagues
            foreach($categories as &$type){
                if($type["shown"] == 1){
                    $type["shown"] = 0;
                    $session->set('categories', $categories);
                }
            }

            foreach($categories as $category){

                $id = $category["id"];
                $sessionCategories[] = \Pimcore\Model\DataObject\Category::getById($id)->getName();

                if($category["propertyContent"]){
                    foreach($category["propertyContent"] as $content){
                        if($content !== $category["id"]){
                            $propertyIds[] = $content;
                        }
                    }
                }
            }
        }

        //if the user visit this action via email
        if($token){
            $customer = Email::getByToken($token,1);
            $customerSelected = $customer->getProperties();
            foreach($customerSelected as $selected){
                $propertyIds[] = $selected->getId();
                $sessionCategories[] = $selected->getCategory()->getName();
            }
        }

        if($propertyIds){

            $productArray = [];
            $result = [];
            $combinations = [];
            $productsFromProperty = [];

            foreach($propertyIds as $propertyId){

                $backendProperty = \Pimcore\Model\DataObject\Property::getById($propertyId);
                $props[] = $backendProperty;

                $products = $backendProperty->getProduct();

                foreach($products as $product){

                    if(!array_key_exists($product->getName(),$productArray)){
                        $productArray[$product->getName()] = $product->getName();

                        $providerId = $product->getId();

                        $leagueList = new \Pimcore\Model\DataObject\Property\Listing();
                        $leagueList->setCondition("product like '%,object|".$providerId.",%'");
                        $leagueListIds = $leagueList->loadIdList();

                        $providerSports = $product->getCategories();

                        $sports = [];

                        foreach($providerSports as $providerSport){

                            if(in_array($providerSport->getName(),$sessionCategories) && !in_array($providerSport->getName(),$sports)){

                                $sports[] = $providerSport->getName();
                            }
                        }

                        //Important for backend!!! $anzahlSportarten is used in product price!!!
                        $count = 0;
                        foreach($leagueListIds as $leagueListId){
                            if(in_array($leagueListId,$propertyIds)){
                                $count++;
                            }
                        }

                        $anzahlSportarten = $count;

                        $price = 0;
                        eval("\$price = ".$product->getPrice().";");
                        $product->setPrice($price);


                        //part for combinations in product
                        $arrayKey  = implode(", ",$sports);

                        if($product->getIsCombination() && !empty($product->getCombinations())){
                            if(!in_array($product,$combinations)){
                                $combinations[] = $product;
                            }
                        }else{
                            $productsFromProperty[] = $product->getName();
                            $result[$product->getId()]=$product;
                        }
                    }
                }

                if(!empty($combinations)){
                    $combis = [];

                    foreach($combinations as $combination){
                        $getCombinations = $combination->getCombinations();

                        foreach($getCombinations as $combi){
                            $combis[] = $combi->getName();
                        }

                        $intersect = array_intersect($combis,$productsFromProperty);

                        if(count($intersect) === count($combis)){

                            foreach ($getCombinations as $getCombination){

                                $combinationProvider = $getCombination;

                                unset($result[$combinationProvider->getId()]);
                                $result[$combination->getId()]=$combination;
                            }
                        }
                    }
                }
            }
        }else{
            if($valid == true){
               // return $this->redirectToRoute('league',array(
                return $this->redirectToRoute('props',array(
                    'categories' => $categories,
                    'valid' => $valid
                ));
            }
        }

        $this->view->result = $result;
        $this->view->props = $props;
        $this->view->categories = $categories;
        $this->view->countLeague = count($propertyIds);


        $email = $request->get('email');
        $isSend = $request->get('sendEmail');
//        $isChoiceEmail = $request->get('choicesMail-check');
        $emailAdress = $request->get('newsletter-email');
        $newsletter = $request->get('newsletter-check');
//        $isReadData = $request->get('readData-check');

        $isReadData = true;
        $esporttypes = $request->get('categories');

        if($isSend){

            foreach($esporttypes as $type){
                foreach($type["propertyContent"] as $key => $value){
                    if($key !== "category"){
                        $ebackendLeague[] = Property::getById($key);
                    }
                }
            }

            if($isReadData || $email){
                $token = $this->createToken($emailAdress);
                if($email || ($emailAdress && !$newsletter)){

                    if($email){
                        $emailAdress = $email;
                    }

                    $this->createEmailObject($emailAdress,$token,$ebackendLeague);

                    $params = array('token' => "https://www.pickoutsports.de/product?token=".$token);

                    $mail = new \Pimcore\Mail();
                    if ($mail->getSubject()) {
                        $mail->setSubject($mail->getSubject());
                    }
                    $mail->addTo($emailAdress);
                    $mail->setDocument('/Email/emailTemplate');
                    $mail->setParams($params);

                    //add headers to avoid the mail direction to 'spam'/ 'junk' folder
                    $this->setHeaderAndSendMail($mail,$emailAdress);

                    $this->addFlash('success', 'Deine E-Mail wurde erfolgreich versendet. (evtl. im Spam-Ordner gelandet)');
                }

                if($newsletter && $emailAdress){

                    $this->createNewsletter($emailAdress,$token);

                    $nparams = array('token' => "https://www.pickoutsports.de/newsletter?token=".$token);

                    $mail = new \Pimcore\Mail();
                    if ($mail->getSubject()) {
                        $mail->setSubject($mail->getSubject());
                    }
                    $mail->addTo($emailAdress);
                    $mail->setDocument('/Email/newsletterTemplate');
                    $mail->setParams($nparams);

                    //add headers to avoid the mail direction to 'spam'/ 'junk' folder
                    $this->setHeaderAndSendMail($mail,$emailAdress);

                    $this->createEmailObject($emailAdress,$token,$ebackendLeague);

                    $params = array('token' => "https://www.pickoutsports.de/product?token=".$token);

                    $mail = new \Pimcore\Mail();
                    if ($mail->getSubject()) {
                        $mail->setSubject($mail->getSubject());
                    }
                    $mail->addTo($emailAdress);
                    $mail->setDocument('/Email/emailTemplate');
                    $mail->setParams($params);

                    $this->setHeaderAndSendMail($mail,$emailAdress);

                    $this->addFlash('success', 'Deine E-Mail wurde erfolgreich versendet. Bitte Deine E-Mail bestätigen. (evtl. im Spam-Ordner gelandet)');
                }
            }else{
                $this->addFlash('warning','Du hast vergessen die Datenschutzerklärung zu akzeptieren.');
            }
        }
    }

    /**
     * @param $emailAddress
     * @return string
     */
    private function createToken($emailAddress){
        return md5($emailAddress.uniqid());
    }

    /**
     * @param $emailAdress
     * @param $token
     * @param $nebackendLeague
     * @throws \Exception
     */
    private function createEmailObject($emailAdress,$token,$nebackendLeague){
        $saveMail = new Email();
        $saveMail->setParentId(150);
        $saveMail->setToken($token);
        $saveMail->setEmail($emailAdress);
        $saveMail->setLeagues($nebackendLeague);
        $saveMail->setKey(\Pimcore\File::getValidFilename($emailAdress).uniqid());
        $saveMail->setPublished(true);

        $saveMail->save();
    }

    /**
     * @param $emailAdress
     * @throws \Exception
     */
    private function createNewsletter($emailAdress,$ntoken){
        $newsletter = new Newsletter();
        $newsletter->setParentId(477);

        $newsletter->setToken($ntoken);

        $newsletter->setEmail($emailAdress);
        $newsletter->setKey(\Pimcore\File::getValidFilename($emailAdress).uniqid());
        $newsletter->setPublished(true);
        $newsletter->save();
    }

    /**
     * @param Mail $mail
     * @param $emailAdress
     */
    private function setHeaderAndSendMail(Mail $mail,$emailAdress){
        //add headers to avoid the mail direction to 'spam'/ 'junk' folder
        $headers = $mail->getHeaders();
        $messageid = time() .'-' . md5('info@pickoutspots.de' . $emailAdress) . '@'.substr(strrchr('info@pickoutspots.de', "@"), 1);
        $headers->addIdHeader('Message-ID', $messageid);
        $headers->addTextHeader('MIME-Version', '1.0');
        $headers->addTextHeader('X-Mailer', 'PHP v' . phpversion());
        $headers->addParameterizedHeader('Content-type', 'text/html', ['charset' => 'utf-8']);
        $headers->addTextHeader('Content-Transfer-Encoding', 'quoted-printable');

        $mail->send();
    }

}
