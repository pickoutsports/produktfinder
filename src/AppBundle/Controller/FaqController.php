<?php

namespace AppBundle\Controller;

use AppBundle\Lib\BaseController;
use Pimcore\Model\DataObject\Blog;
use Pimcore\Model\DataObject\FAQ;
use Pimcore\Model\DataObject\Question;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class FaqController extends BaseController{

    /**
     * @Route("/faq", name="faq")
     * @param Request $request
     * @throws \Exception
     */
    public function indexAction(Request $request){

        $faqs = FAQ::getList();
        $faqs->setOrderKey("sort");
        $faqs->setOrder("asc");
        $this->view->faqs=$faqs;

    }

    /**
     * @Route("/faq/saveFaq", name="faq_saveFaq")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function saveFaqAction(Request $request){

        $email = $request->get('sendEmail');
        $feedback = $request->get('feedback');
        $question = $request->get('question');

        if($email){
            $faq = new Question();
            $faq->setParentId(276);
            $faq->setQuestion($question);
            $faq->setFeedback($feedback);
            $faq->setEmail($email);
            $faq->setKey(\Pimcore\File::getValidFilename($email).uniqid());
            $faq->setPublished(true);
            $faq->save();

            //send mail to user
            $mail = new \Pimcore\Mail();
            $mail->addTo($email);
            $mail->setDocument('/email/faqEmail');

            //add headers to avoid the mail direction to 'spam'/ 'junk' folder
            $headers = $mail->getHeaders();
            $headers->addTextHeader('MIME-Version', '1.0');
            $headers->addTextHeader('Content-Type', 'text/html');
            $headers->addTextHeader('Content-Transfer-Encoding', '8bit');
            $headers->addTextHeader('X-Mailer:', 'PHP/'.phpversion());

            $mail->send();

            //send mail to pickoutsports
            $mail = new \Pimcore\Mail();
            $mail->setDocument('/email/faqEmailPos');
            $mail->setFrom($email);

            $mail->setParams([
                'feedback' => $feedback,
                'email' => $email
            ]);

            //add headers to avoid the mail direction to 'spam'/ 'junk' folder
            $headers = $mail->getHeaders();
            $headers->addTextHeader('MIME-Version', '1.0');
            $headers->addTextHeader('Content-Type', 'text/html');
            $headers->addTextHeader('Content-Transfer-Encoding', 'quoted-printable');
            $headers->addTextHeader('X-Mailer:', 'PHP/'.phpversion());

            $mail->send();
        }

        return $this->redirectToRoute('faq');
    }

}
