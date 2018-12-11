<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{


    /**
     * @Route("/login_check", name="security_login_check")
     */
    public function loginCheckAction()
    {

    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        /*$currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $currentUser->setIsActive(false);
        $em->persist($currentUser);
        $em->flush();
        $this->get('security.token_storage')->setToken(null);
        $this->get('session')->clear();
        return $this->render('PublicBundle::signin.html.twig');*/
    }
}