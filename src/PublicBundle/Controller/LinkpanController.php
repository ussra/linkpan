<?php

namespace PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class LinkpanController extends Controller
{
    /**
     * @Route("/linkpan",name="linkpan")
     */
    public function indexAction()
    {
        return $this->render('::base.html.twig');
    }

    /**
     * @Route("/linkpan/signin",name="signin")
     */
    public function signinAction()
    {
        return $this->render('PublicBundle::signin.html.twig');
    }

    /**
     * @Route("/linkpan/signup",name="signup")
     */
    public function signupAction()
    {
        return $this->render('PublicBundle::signup.html.twig');
    }

    /**
     * @Route("/linkpan/terms",name="terms")
     */
    public function termsAction()
    {
        return $this->render('PublicBundle::terms.html.twig');
    }

    /**
     * @Route("/linkpan/privacy",name="privacy")
     */
    public function privacyAction()
    {
        return $this->render('PublicBundle::privacy.html.twig');
    }
}
