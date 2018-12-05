<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\PanReview;
use UserBundle\Entity\PanShare;
use UserBundle\Entity\PostShare;

class PanReviewController extends Controller
{

    /**
     * @Route("/linkpan/discover/product/review",name="review_pan")
     */
    public function pan_reviewAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneById($request->get('pan'));
        if(!is_null($pan))
        {
            $pr = new PanReview();
            $pr->setPan($pan);
            $pr->setUser($this->getUser());
            $pr->setReview($request->get('review'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($pr);
            $em->flush();

        }
        return new JsonResponse('Done');

    }

    /**
     * @Route("/linkpan/discover/product/pan_share",name="pan_share")
     */
    public function pan_shareAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneById($request->get('pan'));
        if(!is_null($pan))
        {
            $user = $this->getUser();
            //check if shared
            $repo = $this->getDoctrine()->getRepository('UserBundle:PanShare');
            $ps = $repo->findOneBy(
              array('pan'=>$pan,'user'=>$user)
            );
            if(is_null($ps))
            {
                $panShare = new PanShare();
                $panShare->setPan($pan);
                $panShare->setUser($user);
                $em = $this->getDoctrine()->getManager();
                $em->persist($panShare);
                $em->flush();
            }
        }
        return new JsonResponse('Done');
    }
}
