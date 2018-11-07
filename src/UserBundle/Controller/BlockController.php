<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Block;

class BlockController extends Controller
{

    /**
     * @Route("/linkpan/block",name="block")
     */
    public function followAction(Request $request)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $userToBlock= $repo->findOneById($request->get('Toblock'));
        $repo = $this->getDoctrine()->getRepository('UserBundle:Block');
        $check = $repo->findOneBy(array('user'=>$currentUser,'userToBlock'=>$userToBlock));
        if(sizeof($check) != 1)
        {
            if(!is_null($userToBlock))
            {
                $block = new Block();
                $block->setUser($currentUser);
                $block->setUserToBlock($userToBlock);
                $em = $this->getDoctrine()->getManager();
                $em->persist($block);
                $em->flush();
                //remove follow both sides
                $repo = $this->getDoctrine()->getRepository('UserBundle:Follow');
                $follow = $repo->findOneBy(
                    array('user'=>$currentUser,'userToFollow'=>$userToBlock)
                );
                if(sizeof($follow)>0)
                {
                    $em->remove($follow);
                    $em->flush();
                }
                $follow = $repo->findOneBy(
                    array('user'=>$userToBlock,'userToFollow'=>$currentUser)
                );
                if(sizeof($follow)>0)
                {
                    $em->remove($follow);
                    $em->flush();
                }
                //
            }
        }
        return $this->forward('UserBundle:Profile:searchprofile',array('user'=>$currentUser->getId()));
    }
}
