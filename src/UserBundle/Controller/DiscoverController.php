<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DiscoverController extends Controller
{
    /**
     * @Route("/linkpan/discover",name="discover")
     */
    public function discoverAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $session = new Session();
        $this->getPansCount($em,$currentUser,$session);
        return $this->render('UserBundle::discover.html.twig');
    }


    private function getPansCount($em,$currentUser,$session)
    {
        $query = $em->createQuery(
            '
                SELECT DISTINCT p.id FROM UserBundle:Pan p
                WHERE IDENTITY(p.user) = :user
                AND p.type = :type
                OR 
                IDENTITY(p.user) IN (
                  SELECT IDENTITY(f.userToFollow) FROM UserBundle:Follow f 
                  WHERE f.user = :user
                )
                OR 
                p.id IN (
                  SELECT IDENTITY(bp.pan) FROM UserBundle:BoostPan bp
                ) 
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                )
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
                
                ORDER BY p.id DESC
            '
        )->setParameter('user', $currentUser->getId())
            ->setParameter('type', 'Discover');
        $session->set('DiscoverPans_count',$query->getResult());
    }
}
