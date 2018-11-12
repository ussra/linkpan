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
        //Get Followers
        $followers = array();
        $flresult = $frepo->findBy(
          array('userToFollow'=>$user)
        );
        if(sizeof($flresult)>0)
        {
            foreach ($flresult as $value)
            {
                $follower = $repo->findOneById($value->getUser());
                $tempf = array(
                   'follower_id'=>$follower->getId(),
                   'follower_first_name'=>$follower->getFirstname(),
                    'follower_last_name'=>$follower->getLastname(),
                    'follower_image'=>$follower->getImage(),
                    'follower_company_name'=>$follower->getCompanyName()
                );
                array_push($followers,$tempf);
            }
        }
        //Get Following
        $following = array();
        $fogresult =$frepo->findBy(
            array('user'=>$user)
        );
        if(sizeof($fogresult)>0)
        {
            foreach ($fogresult as $value)
            {
                $follg= $repo->findOneById($value->getUserToFollow());
               $tempfg = array(
                    'follower_id'=>$follg->getId(),
                   'follower_first_name'=>$follg->getFirstname(),
                   'follower_last_name'=>$follg->getLastname(),
                   'follower_image'=>$follg->getImage(),
                   'follower_company_name'=>$follg->getCompanyName()
                );

                array_push($following,$tempfg);

            }
        }
        //Get membership
        $mrepo = $this->getDoctrine()->getRepository('UserBundle:Membership');
        $result = $mrepo->findOneBy(array('user'=>$user));
        if(!is_null($result))
            $membership  = 'dockies';
        else
            $membership  = 'simple';
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
          'follow_result'=>$type_result,
          'followers'=>$followers,
          'following'=>$following,
          'user_membership'=>$membership
        );

        $session = new Session();
        $session->set('user_info',$temp);
        return new JsonResponse($this->generateUrl('profile'));
    }
}
