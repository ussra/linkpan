<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class HomeController extends Controller
{
    /**
     * @Route("/linkpan/home",name="home")
     */
    public function indexAction()
    {
        return $this->render('UserBundle::userbase.html.twig');
    }

    /**
     * @Route("/linkpan/setting",name="setting")
     */
    public function settingAction(Request $request)
    {
        return $this->render('UserBundle::setting.html.twig');
    }

}
