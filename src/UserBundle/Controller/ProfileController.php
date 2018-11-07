<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
class ProfileController extends Controller
{
    /**
     * @Route("/linkpan/profile",name="profile")
     */
    public function profileAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $repo->findOneById($request->get('user'));
        $temp = array(
          'user_id'=>$user->getId(),
          'user_first_name'=>$user->getFirstname(),
          'user_last_name'=>$user->getLastname(),
          'user_company_name'=>$user->getCompanyName(),
          'user_image'=>$user->getImage(),
        );
        $session = new Session();
        $session->set('user_info',$temp);
        return $this->render('UserBundle::profile.html.twig');
    }
}
