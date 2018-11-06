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
}
