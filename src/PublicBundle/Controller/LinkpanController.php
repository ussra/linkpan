<?php

namespace PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\Session;

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
}
