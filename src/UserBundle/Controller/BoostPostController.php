<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\BoostPost;
use UserBundle\UserBundle;

class BoostPostController extends Controller
{
    /**
     * @Route("{_locale}/linkpan/boost_post",name="boost_post")
     */
    public function boost_postAction(Request $request)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Post');
        $post = $repo->findOneById($request->query->get('post'));
        if(!is_null($post))
        {
            $boostrepo = $this->getDoctrine()->getRepository('UserBundle:BoostPost');
            $boost = $boostrepo->findOneBy(
                array('post'=>$post)
            );
            if(is_null($boost))
            {
                //check user s billing
                if(is_null($currentUser->getStripeId()))
                {
                    echo '<script language="javascript">alert("please first you must set your billing method")</script>';
                    return $this->forward('UserBundle:Home:boost_posts');
                }
                else
                {
                    $stripeClient = $this->get('flosch.stripe.client');
                    $boosting = $stripeClient->subscribeExistingCustomerToPlan($currentUser->getStripeId(), 'plan_Dm5nsflHbdvNDu',null);
                    $boost = new BoostPost();
                    $boost->setBoostId($boosting['id']);
                    $boost->setPost($post);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($boost);
                    $em->flush();
                    echo '<script language="javascript">alert("post is boosted")</script>';
                    return $this->forward('UserBundle:Home:boost_posts');
                }
            }
            else
            {
                echo '<script language="javascript">alert("Sorry,we cannot boost this post now please retry again")</script>';
                return $this->forward('UserBundle:Home:boost_posts');
            }
        }
        else
        {
            echo '<script language="javascript">alert("please , retry again")</script>';
            return $this->forward('UserBundle:Home:boost_posts');
        }


    }


    /**
     * @Route("{_locale}/linkpan/remove_boost_post",name="remove_boost_post")
     */
    public function remove_boost_postAction(Request $request)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Post');
        $post = $repo->findOneById($request->query->get('post'));
        if(!is_null($post))
        {
            $boostrepo = $this->getDoctrine()->getRepository('UserBundle:BoostPost');
            $boost = $boostrepo->findOneBy(
                array('post'=>$post)
            );
            if(!is_null($boost))
            {
                $stripeClient = $this->get('my.stripe.client');
                // retreive sub
                $sub = $stripeClient->retrieveSub($boost->getBoostId());
                $sub->cancel();
                //
                $em = $this->getDoctrine()->getManager();
                $em->remove($boost);
                $em->flush();
                echo '<script language="javascript">alert("Boost removed for the poste you selected")</script>';
                return $this->forward('UserBundle:Home:boost_posts');
            }
            else
            {
                return $this->forward('UserBundle:Home:boost_posts');
            }
        }
        else
        {
            return $this->forward('UserBundle:Home:boost_posts');
        }
    }
}
