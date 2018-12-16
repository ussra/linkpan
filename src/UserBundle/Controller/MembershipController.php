<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Membership;

class MembershipController extends Controller
{
    /**
     * @Route("/linkpan/change_membership",name="change_membership")
     */
    public function changemembershipAction(Request $request)
    {
        $currentUser = $this->getUser();
        if(is_null($currentUser->getStripeId()) || $currentUser->getStripeId() == '')
        {
            echo '<script language="javascript">alert("please first you must set your billing method")</script>';
            return $this->forward('UserBundle:Home:index');
        }
        else
        {
            //check if there s  a membership
            $mrepo = $this->getDoctrine()->getRepository('UserBundle:Membership');
            $membership = $mrepo->findOneBy(
                array('user'=>$currentUser)
            );
            if(is_null($membership))
            {
                $type = $request->get('dockiestype');
                $stripeClient = $this->get('flosch.stripe.client');

                $plan = null;
                if($type == 'monthly')
                    $plan = $stripeClient->subscribeExistingCustomerToPlan($currentUser->getStripeId(), 'plan_Dm6jUqTK31pugh',null);
                if($type == '6 months')
                    $plan = $stripeClient->subscribeExistingCustomerToPlan($currentUser->getStripeId(), 'plan_Dm6my4r0YqSCZq',null);
                if($type == 'annually')
                    $plan = $stripeClient->subscribeExistingCustomerToPlan($currentUser->getStripeId(), 'plan_Dm6jLrBxxv0b4D',null);

                if(!is_null($plan))
                {
                    $membership = new Membership();
                    $membership->setPlanId($plan['id']);
                    $membership->setUser($currentUser);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($membership);
                    $em->flush();
                    echo '<script language="javascript">alert("Membership Updated")</script>';
                    return $this->forward('UserBundle:Home:index');
                }
                else
                {
                    echo '<script language="javascript">alert("Can you try another time , Thank you")</script>';
                    return $this->forward('UserBundle:Home:index');
                }

            }
            else
            {
                echo '<script language="javascript">alert("You already have a membership !!")</script>';
                return $this->forward('UserBundle:Home:index');
            }
        }

    }


    /**
     * @Route("/linkpan/downgrade_membership",name="downgrade_membership")
     */
    public function downgrademembershipAction(Request $request)
    {
        $currentUser = $this->getUser();
        // check if the user has a membership
        $mrepo = $this->getDoctrine()->getRepository('UserBundle:Membership');
        $membership = $mrepo->findOneBy(
            array('user'=>$currentUser)
        );
        if(!is_null($membership))
        {
            //downgrade the membership to simple
            $stripeClient = $this->get('my.stripe.client');
            $sub = $stripeClient->retrieveSub($membership->getPlanId());
            $sub->cancel();
            // remove from data
            $em = $this->getDoctrine()->getManager();
            $em->remove($membership);
            $em->flush();
        }
        return $this->forward('UserBundle:Home:index');
    }
}
