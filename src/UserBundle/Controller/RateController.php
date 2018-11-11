<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\PanRating;

class RateController extends Controller
{

    /**
     * @Route("/linkpan/discover/rate_pan",name="rate_pan")
     */
    public function rate_panAction(Request $request)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneById($request->get('pan'));
        if(!is_null($pan))
        {
            $em = $this->getDoctrine()->getManager();
            $rrepo = $this->getDoctrine()->getRepository('UserBundle:PanRating');
            $pr = $rrepo->findOneBy(
              array('user'=>$currentUser,'pan'=>$pan)
            );

            if(is_null($pr))
            {
                $panrating = new PanRating();
                $panrating->setUser($currentUser);
                $panrating->setPan($pan);
                $panrating->setRate($request->get('value'));
                $em->persist($panrating);
                $em->flush();
            }
            else
            {
                $pr->setRate($request->get('value'));
                $em->persist($pr);
                $em->flush();
            }


        }

        return new JsonResponse('Rating Done');
    }
}
