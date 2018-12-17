<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\BoostPan;

class BoostPanController extends Controller
{
    /**
     * @Route("{_locale}/linkpan/boost_pan",name="boost_pan")
     */
    public function boost_panAction(Request $request)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneById($request->query->get('pan'));
        if(!is_null($pan))
        {
            $boostrepo = $this->getDoctrine()->getRepository('UserBundle:BoostPan');
            $boost = $boostrepo->findOneBy(
                array('pan'=>$pan)
            );
            if(empty($boost))
            {
                if(empty($currentUser->getStripeId()))
                {
                    echo '<script language="javascript">alert("please first you must set your billing method")</script>';
                    return $this->forward('UserBundle:Home:boost_posts');
                }
                else
                {
                    $stripeClient = $this->get('flosch.stripe.client');
                    $boosting = $stripeClient->subscribeExistingCustomerToPlan($currentUser->getStripeId(), 'plan_DnycNYlEukd8cq',null);
                    $boostPan = new BoostPan();
                    $boostPan->setBoostId($boosting['id']);
                    $boostPan->setPan($pan);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($boostPan);
                    $em->flush();
                    echo '<script language="javascript">alert("pan is boosted")</script>';
                    return $this->forward('UserBundle:Home:pans');
                }
            }
        }
        else
        {
            echo '<script language="javascript">alert("please , retry again")</script>';
            return $this->forward('UserBundle:Home:pans');
        }
        return $this->forward('UserBundle:Home:pans');
    }

    /**
     * @Route("{_locale}/linkpan/remove_boost_pan",name="remove_boost_pan")
     */
    public function remove_boost_panAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneById($request->query->get('pan'));
        if(!is_null($pan))
        {
            $boostrepo = $this->getDoctrine()->getRepository('UserBundle:BoostPan');
            $boost = $boostrepo->findOneBy(
                array('pan'=>$pan)
            );

            if(!is_null($boost))
            {
                $stripeClient = $this->get('my.stripe.client');
                $sub = $stripeClient->retrieveSub($boost->getBoostId());
                $sub->cancel();
                $em = $this->getDoctrine()->getManager();
                $em->remove($boost);
                $em->flush();
                echo '<script language="javascript">alert("Boost removed for the pan you selected")</script>';
                return $this->forward('UserBundle:Home:pans');
            }
            else
            {
                echo '<script language="javascript">alert("please , retry again")</script>';
                return $this->forward('UserBundle:Home:pans');
            }
        }
    }
}
