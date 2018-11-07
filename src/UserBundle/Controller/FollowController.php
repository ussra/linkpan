<?php

namespace UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use UserBundle\Entity\Follow;
use Symfony\Component\HttpFoundation\Request;

class FollowController extends Controller
{
    /**
     * @Route("/linkpan/follow",name="follow")
     */
    public function indexAction(Request $request)
    {

        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $userToFollow = $repo->findOneById($request->get('tofollow'));
        if(!is_null($userToFollow))
        {
            //check
            $followrepo = $this->getDoctrine()->getRepository('UserBundle:Follow');
            $check = $followrepo->findOneBy(
                array('user'=>$this->getUser(),'userToFollow'=>$userToFollow)
            );
            if(is_null($check))
            {
                $follow = new Follow();
                $follow->setUser($this->getUser());
                $follow->setUserToFollow($userToFollow);
                $em = $this->getDoctrine()->getManager();
                $em->persist($follow);
                $em->flush();
            }
        }

        return $this->forward('UserBundle:Profile:profile',array('user'=>$request->get('tofollow')));
    }
}
