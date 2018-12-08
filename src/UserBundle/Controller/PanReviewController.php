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
            $date = date("m/d/Y h:i:s ", time());
            $pr->setCreationDate($date);
            $em = $this->getDoctrine()->getManager();
            $em->persist($pr);
            $em->flush();
            // Get rating
            $Ratingrepo = $this->getDoctrine()->getRepository('UserBundle:PanRating');
            $obj = $Ratingrepo->findOneBy(
                array('pan'=>$pan,'user'=>$this->getUser())
            );
            if(is_null($obj))
                return new JsonResponse('Done');
            else
                return new JsonResponse($obj->getRate());
        }
        else
            return new JsonResponse('ERR');

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
