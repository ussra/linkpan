<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $pans = $this->Query($em,$currentUser,$request->get('type'));
        $this->getPans($currentUser,$session,$pans);
        $this->getPansCount($em,$currentUser,$session);
        return $this->render('UserBundle::discover.html.twig');
    }

    ///DISCOVER
    private function Query($em,$currentUser,$type)
    {
        $pans = null;
        $query = $em->createQuery(
            'SELECT DISTINCT p FROM UserBundle:Pan p
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
        if($type == 'discover')
            $pans = $query->setMaxResults(15)->getResult();
        if($type == 'discover_all')
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
                // get boosted
                $boost = $boostRepo->findOneBy(
                    array('pan'=>$pan)
                );
                if(is_null($boost))
                    $boosted = 'NO'; else $boosted = 'YES';
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
                  'boost'=>$boosted,
                  'rate'=> array(
                      'type'=>$rating,
                      'value'=>$ratingvalue
                  )
                );
                array_push($result,$temp);
            }
        }
        $session->set('DiscoverPans',$result);
    }
    private function getPansCount($em,$currentUser,$session)
    {
        $query = $em->createQuery(
            '
                SELECT COUNT(DISTINCT p.id) FROM UserBundle:Pan p
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
        $session->set('DiscoverPans_count',intval($query->getResult()[0][1]));
    }
    ///FILETERS
    private function FilterQuery($em,$currentUser,$type)
    {
        $pans = null;
        if($type == 'Highest Price')
        {
            $query = $em->createQuery(
                'SELECT DISTINCT p FROM UserBundle:Pan p
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
                
                ORDER BY p.price DESC 
            '
            )->setParameter('user', $currentUser->getId())
                ->setParameter('type', 'Discover');
        }
        if($type == 'Lowest Price')
        {
            $query = $em->createQuery(
                'SELECT DISTINCT p FROM UserBundle:Pan p
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
                
                ORDER BY p.price ASC 
            '
            )->setParameter('user', $currentUser->getId())
                ->setParameter('type', 'Discover');
        }
        if($type == 'New in'){
            $query = $em->createQuery(
                'SELECT DISTINCT p FROM UserBundle:Pan p
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
        }
        $pans = $query->getResult();
        return $pans;
    }
    private function FilterCategory($em,$currentUser,$category){
        $pans = null;
        $query = $em->createQuery(
            '
              SELECT DISTINCT p FROM UserBundle:Pan p
              WHERE p.type = :type AND p.category = :category
              AND(
                IDENTITY(p.user) IN (
                  SELECT IDENTITY(f.userToFollow) FROM UserBundle:Follow f 
                  WHERE f.user = :user
                )
                OR 
                p.id IN (
                  SELECT IDENTITY(bp.pan) FROM UserBundle:BoostPan bp
                ) 
                OR 
                IDENTITY(p.user) = :user
              )
              AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                )
              AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
            '
        )->setParameter('type', 'Discover')
            ->setParameter('category', $category)
            ->setParameter('user', $currentUser->getId());
        $pans = $query->getResult();
        return $pans;
    }

    /**
     * @Route("/linkpan/discover/filter",name="discover_filter")
     */
    public function filterDiscoverAction(Request $request)
    {
        $filterType = $request->get('type');
        $filter = $request->get('filter');
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $session = new Session();

        if($filterType == 'sort') // Last viewed
            $result = $this->FilterQuery($em,$currentUser,$filter);

        if($filterType == 'category')
        {
            if($filter == 'Default')
                $result = $this->FilterQuery($em,$currentUser,'New in');
            else
                $result = $this->FilterCategory($em,$currentUser,$filter);
        }


        $this->getPans($currentUser,$session,$result);

        return $this->render('UserBundle::discover.html.twig');
    }


    /**
     * @Route("/linkpan/discover/discover/pan",name="discover_pan")
     */
    public function discover_panAction(Request $request)
    {
        $panRepo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $panRepo->findOneById($request->get('pan'));
        if(!is_null($pan))
        {
            // get pan average rating
            $ratingrepo = $this->getDoctrine()->getRepository('UserBundle:PanRating');
            $rating = $ratingrepo->findBy(
                array('pan'=>$pan)
            );
            $average = 0;
            if(sizeof($rating)>0)
            {
                foreach ($rating as $val)
                {
                    $average += $val->getRate();
                }
                $average = $average / sizeof($rating);
                if($average > 5)
                    $average = 5;
            }
            //Get reviews
            $userrepo = $this->getDoctrine()->getRepository('AppBundle:User');
            $prrepo = $this->getDoctrine()->getRepository('UserBundle:PanReview');
            $pr = $prrepo->findBy(
                array('pan'=>$pan)
            );
            $reviews = array();
            if(!empty($pr))
            {
                foreach ($pr as $rev)
                {
                    //Get review owner
                    $reviewOwner = $userrepo->findOneById($rev->getUser());
                    //Get owner rating
                    $ownerRate = $ratingrepo->findOneBy(
                        array('user'=>$reviewOwner,'pan'=>$pan)
                    );
                    if(!is_null($ownerRate)) $rate = $ownerRate->getRate(); else $rate = 'no rating';
                    //
                    $rtemp = array(
                        'review_owner_id'=>$reviewOwner->getId(),
                        'review_owner_first_name'=>$reviewOwner->getFirstname(),
                        'review_owner_last_name'=>$reviewOwner->getLastname(),
                        'review_owner_pan_rating'=>$rate,
                        'review_owner_image'=>$reviewOwner->getImage(),
                        'review_owner_content'=>$rev->getReview()
                    );
                    array_push($reviews,$rtemp);
                }
            }
            //
            $temp = array(
              'pan'=>$pan,
              'average'=>$average,
              'reviews'=>$reviews
            );
            $session = new Session();
            $session->set('get_pan',$temp);
            return $this->render('UserBundle::product.html.twig');
        }
        else
            return $this->forward('UserBundle:Discover:discover');
    }
}
