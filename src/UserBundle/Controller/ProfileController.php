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
        return $this->render('UserBundle::profile.html.twig');
    }

    /**
     * @Route("/linkpan/search_profile",name="search_profile")
     */
    public function searchprofileAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $repo->findOneById($request->get('user'));
        // check follow
        $frepo = $this->getDoctrine()->getRepository('UserBundle:Follow');
        $type = $frepo->findOneBy(
            array('user'=>$this->getUser(),'userToFollow'=>$user)
        );
        if(is_null($type))
            $type_result = 'follow';
        else
            $type_result = 'unfollow';
        //
        $temp = array(
          'user_id'=>$user->getId(),
          'user_first_name'=>$user->getFirstname(),
          'user_last_name'=>$user->getLastname(),
          'user_company_name'=>$user->getCompanyName(),
          'user_image'=>$user->getImage(),
          'user_adress'=>$user->getAdress(),
          'user_website'=>$user->getWebsite(),
          'user_email'=>$user->getEmail(),
          'follow_result'=>$type_result
        );
        $session = new Session();
        $session->set('user_info',$temp);
        return new JsonResponse($this->generateUrl('profile'));
    }
}
