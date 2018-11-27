<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DockiesController extends Controller
{

    private function getPans($query,$currentUser)
    {

        $result = $query->getresult();
        $ratingrepo = $this->getDoctrine()->getRepository('UserBundle:PanRating');
        $pans = array();
        foreach ($result as $pan)
        {
            //get pan rating
            $ratingvalue = 0;
            $rt  = $ratingrepo->findOneBy(
                array('user'=>$currentUser,'pan'=>$pan)
            );
            if(!is_null($rt))
            {
                $rating = 'fixed';
                $ratingvalue = $rt->getRate();
            }
            else
                $rating ='rate';
            //
            $temp = array(
                $pan,
                'pan_rating'=> $rating,
                'pan_rating_value'=> $ratingvalue
            );
            array_push($pans,$temp);
        }

        return $pans;
    }
    /**
     * @Route("{_locale}/linkpan/dockies",name="dockies")
     */
    public function redirectDockiesAction()
    {

        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            '
                SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE  p.type = :type
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                )
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
                
                ORDER BY p.id DESC
            '
        )->setParameter('user', $currentUser->getId())
            ->setParameter('type', 'Dockies');
        $result = $this->getPans($query,$currentUser);
        $session = new Session();
        $session->set('dockies_pans',array_reverse($result));
        return $this->render('UserBundle::dockies.html.twig');
    }


}
