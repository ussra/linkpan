<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DockiesController extends Controller
{
    /**
     * @Route("/linkpan/dockies",name="dockies")
     */
    public function discoverAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $session = new Session();
        $pans = $this->Query($em,$currentUser,$request->get('type'));
        $this->getPans($currentUser,$session,$pans);
        $this->getPansCount($em,$currentUser,$session);
        return $this->render('UserBundle::dockies.html.twig');
    }

    private function Query($em,$currentUser,$type)
    {
        $pans = null;
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
        if($type == 'dockies')
            $pans = $query->setMaxResults(15)->getResult();
        if($type == 'dockies_all')
            $pans = $query->getResult();
        return $pans;
    }
    private function getPans($currentUser,$session,$pans){
        $result = array();
        if(!is_null($pans))
        {
            $boostRepo = $this->getDoctrine()->getRepository('UserBundle:BoostPan');
            $rateRepo = $this->getDoctrine()->getRepository('UserBundle:PanRating');
            foreach ($pans as $pan)
            {
                // get rating
                $ratingvalue = 0;
                $rt  = $rateRepo->findOneBy(
                    array('user'=>$currentUser,'pan'=>$pan)
                );
                if(!is_null($rt))
                {
                    $rating = 'fixed';
                    $ratingvalue = $rt->getRate();
                }
                else
                    $rating ='rate';
                // RESULT
                $temp = array(
                    'pan'=>$pan,
                    'rate'=> array(
                        'type'=>$rating,
                        'value'=>$ratingvalue
                    )
                );
                array_push($result,$temp);
            }
        }
        $session->set('DockiesPans',$result);
    }
    private function getPansCount($em,$currentUser,$session)
    {
        $query = $em->createQuery(
            '
                SELECT COUNT(DISTINCT p.id ) FROM UserBundle:Pan p
                WHERE  p.type = :type
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                )
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
                
            '
        )->setParameter('user', $currentUser->getId())
            ->setParameter('type', 'Dockies');
        $session->set('DockiesPans_count',intval($query->getResult()[0][1]));
    }

    /**
     * @Route("/linkpan/dockies/filter",name="dockies_filter")
     */
    public function filterDiscoverAction(Request $request)
    {
        $filterType = $request->get('type');
        $filter = $request->get('filter');
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $session = new Session();
        if($filterType == 'sort')
            $result = $this->FilterQuery($em,$currentUser,$filter);
        if($filterType == 'category')
        {
            if($filter == 'Default')
                $result = $this->FilterQuery($em,$currentUser,'New in');
            else
                $result = $this->FilterCategory($em,$currentUser,$filter);
        }

        $this->getPans($currentUser,$session,$result);
        return $this->render('UserBundle::dockies.html.twig');
    }

    ///FILETERS
    private function FilterQuery($em,$currentUser,$type)
    {
        $pans = null;
        if($type == 'New in'){
            $query = $em->createQuery(
                'SELECT DISTINCT p FROM UserBundle:Pan p
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
        }
        if($type == 'Highest Price')
        {
            $query = $em->createQuery(
                'SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE  p.type = :type
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                )
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
                
                ORDER BY p.price DESC
            '
            )->setParameter('user', $currentUser->getId())
                ->setParameter('type', 'Dockies');
        }
        if($type == 'Lowest Price')
        {
            $query = $em->createQuery(
                'SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE  p.type = :type
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                )
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
                
                ORDER BY p.price ASC 
            '
            )->setParameter('user', $currentUser->getId())
                ->setParameter('type', 'Dockies');
        }

        $pans = $query->getResult();
        return $pans;
    }
    private function FilterCategory($em,$currentUser,$category){
        $pans = null;
        $query = $em->createQuery(
            '
              SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE  p.type = :type AND p.category = :category
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                )
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
                
                ORDER BY p.id DESC
            '
        )->setParameter('type', 'Dockies')
            ->setParameter('category', $category)
            ->setParameter('user', $currentUser->getId());
        $pans = $query->getResult();
        return $pans;
    }

    /**
     * @Route("/linkpan/dockies/pan",name="dockies_pan")
     */
    public function dockies_panAction(Request $request)
    {
        $panRepo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $panRepo->findOneById($request->get('pan'));
        if(!is_null($pan))
        {
            return $this->render('UserBundle::dockies_pan.html.twig');
        }
        else
            return $this->forward('UserBundle:Discover:discover');

    }
}
