<?php

namespace PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
class LinkpanController extends Controller
{

    private function GetNewpans(){
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $query = $em->createQuery(
            '
                SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE p.type = :type 
                ORDER BY p.id DESC
            '
        )->setParameter('type', 'Discover');
        $pans = $query->setMaxResults(6)->getResult();
        $session->set('DiscoverPans',$pans);
    }
    /**
     * @Route("{_locale}/linkpan",name="linkpan")
     */
    public function indexAction()
    {
        // get pans
        $this->GetNewpans();
        //
        return $this->render('::base.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/signin",name="signin")
     */
    public function signinAction()
    {
        return $this->render('PublicBundle::signin.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/signup",name="signup")
     */
    public function signupAction()
    {
        return $this->render('PublicBundle::signup.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/terms",name="terms")
     */
    public function termsAction()
    {
        return $this->render('PublicBundle::terms.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/privacy",name="privacy")
     */
    public function privacyAction()
    {
        return $this->render('PublicBundle::privacy.html.twig');
    }


    /**
     * @Route("{_locale}/linkpan/faqs",name="faqs")
     */
    public function faqsAction()
    {
        return $this->render('PublicBundle::faqs.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/contact",name="contact")
     */
    public function contactAction()
    {
        return $this->render('PublicBundle::contactus.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/contactus",name="contactus")
     */
    public function contactusAction(Request $request)
    {
        //send email
        $message = \Swift_Message::newInstance()
            ->setSubject('Linkpan Contact : '.$request->get('reason'))
            ->setFrom('linkpandemo@gmail.com')
            ->setTo('mariamaital98@gmail.com')
            ->setBody('Description :  '.$request->get('description').' , FROM : '.$request->get('fullname').
                ' , Email: '.$request->get('email').', Phone : '.$request->get('phone').
                ', Company : '.$request->get('company'));
        $mailer = $this->get('mailer');
        $mailer->send($message);
        $spool = $mailer->getTransport()->getSpool();
        $transport = $this->get('swiftmailer.transport.real');
        $spool->flushQueue($transport);

        return $this->render('PublicBundle::contactus.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/about",name="about")
     */
    public function aboutAction()
    {
        return $this->render('PublicBundle::aboutus.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/forget_password",name="forget_password")
     */
    public function forget_passwordAction()
    {
        return $this->render('PublicBundle::forgetpassword.html.twig');
    }
}
