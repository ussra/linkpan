<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\ObjectShare;
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
        $objectShare = new ObjectShare();
        $objectShare->setUser($this->getUser());
        $objectShare->setType('pan');
        $objectShare->setObjectId($request->get('pan'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($objectShare);
        $em->flush();
        return new JsonResponse('Done');
    }
}
