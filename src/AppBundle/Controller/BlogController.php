<?php

namespace AppBundle\Controller;

use AppBundle\Lib\BaseController;
use Pimcore\Model\DataObject\Blog;
use Pimcore\Model\DataObject\Newsletter;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class BlogController extends BaseController{

    /**
     * @Route("/blog", name="blog")
     * @param Request $request
     * @throws \Exception
     */
    public function indexAction(Request $request){

        // get List of Blogs and sort by asc
        $blogs = Blog::getList();
        $blogs->setOrderKey("sort");
        $blogs->setOrder("asc");

        // transfer blog data to view
        $this->view->blogs=$blogs;
    }

    /**
     * @Route("/blog/details/{key}", name="blog_details")
     * @param Request $request
     * @param string $key
     * @throws \Exception
     */
    public function detailsAction(Request $request, string $key){

        // rawurldecode, so that pimcore can read key
        $key = rawurldecode($key);

        //to get blog from backend folder
        $blog = Blog::getByPath('/Blog/' . $key);

        //send data to view
        $this->view->blog=$blog;

        //if sendEmail
        if(isset($_POST['sendEmail'])){

            //set parameters
            $emailAdress = $request->get('newsletter-email');
            $newsletter = $request->get('newsletter-check');
            $isReadData = $request->get('readData-check');


            //check which condition
            if($this->checkCondition($emailAdress,$newsletter,$isReadData)){
                $this->sendEmail($emailAdress);
                $this->addFlash('success', 'Deine E-Mail wurde erfolgreich versendet. Bitte Deine E-Mail bestätigen. (evtl. im Spam-Ordner gelandet)');
            }else{
                $this->addFlash('warning','Du hast das Formular nicht vollständig ausgefüllt');
            }
        }
    }

    /**
     * @param null $emailAdress
     * @param null $newsletter
     * @param null $isReadData
     * @return bool
     */
    private function checkCondition($emailAdress=null, $newsletter=null,$isReadData=null){

        $success = false;

        if($isReadData && $emailAdress && $newsletter){
            $success = true;
        }

        return $success;
    }

    /**
     * @param $emailAdress
     * @throws \Exception
     */
    private function sendEmail($emailAdress){
        //create Newsletter object
        $newsletter = new Newsletter();
        $newsletter->setParentId(477);

        $ntoken = md5($emailAdress.uniqid());

        $newsletter->setToken($ntoken);

        $newsletter->setEmail($emailAdress);
        $newsletter->setKey(\Pimcore\File::getValidFilename($emailAdress).uniqid());
        $newsletter->setPublished(true);
        $newsletter->save();

        //set params for Email Template in Pimcore Backend (Document)
        $nparams = array('token' => "https://www.pickoutsports.de/newsletter?token=".$ntoken);

        $nmail = new \Pimcore\Mail();
        if ($nmail->getSubject()) {
            $nmail->setSubject($nmail->getSubject());
        }
        $nmail->addTo($emailAdress);
        $nmail->setDocument('/Email/newsletterTemplate');
        $nmail->setParams($nparams);

        //add headers to avoid the mail direction to 'spam'/ 'junk' folder
        $nheaders = $nmail->getHeaders();
        $nmessageid = time() .'-' . md5('info@pickoutspots.de' . $emailAdress) . '@'.substr(strrchr('info@pickoutspots.de', "@"), 1);
        $nheaders->addIdHeader('Message-ID', $nmessageid);
        $nheaders->addTextHeader('MIME-Version', '1.0');
        $nheaders->addTextHeader('X-Mailer', 'PHP v' . phpversion());
        $nheaders->addParameterizedHeader('Content-type', 'text/html', ['charset' => 'utf-8']);
        $nheaders->addTextHeader('Content-Transfer-Encoding', 'quoted-printable');

        $nmail->send();
    }
}
