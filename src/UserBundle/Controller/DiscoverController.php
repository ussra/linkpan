<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
class DiscoverController extends Controller
{

    private function  getResult($result,$currentUser)
    {
        $pans = array();
        if(!empty($result))
        {

            $repo = $this->getDoctrine()->getRepository('UserBundle:BoostPan');
            $userrepo = $this->getDoctrine()->getRepository('AppBundle:User');
            $ratingrepo = $this->getDoctrine()->getRepository('UserBundle:PanRating');
            foreach ($result as $pan)
            {
                //get if pan boosted
                $bp = $repo->findOneBy(
                    array('pan'=>$pan)
                );
                if(!is_null($bp))
                    $boosted = 'yes'; else $boosted ='no';
                //get pan owner
                $owner = $userrepo->findOneById($pan->getUser());
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
                    'pan_id'=>$pan->getId(),
                    'pan_image'=>$pan->getImage(),
                    'pan_name'=>$pan->getName(),
                    'pan_other_name'=>$pan->getOthername(),
                    'pan_price'=>$pan->getPrice(),
                    'pan_boosted'=>$boosted,
                    'pan_owner_id'=>$owner->getId(),
                    'pan_rating'=>$rating,
                    'pan_rating_value'=>$ratingvalue
                );
                array_push($pans,$temp);
            }
        }
        return $pans;
    }

    private function getPans($type)
    {
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            '
                SELECT DISTINCT p FROM UserBundle:Pan p
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

        if($type == 'first')
            $result =  $query->setMaxResults(10)->getResult();

        if($type == 'more')
            $result =  $query->getResult();


        $pans = $this->getResult($result,$currentUser);
        $session = new Session();
        $session->set('discover_pans',$pans);
        // count
        $query = $em->createQuery(
            '
                SELECT count(DISTINCT p.id)  FROM UserBundle:Pan p
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
        $session->set('Discover_count',$query->getResult()[0][1]);

    }

    /**
     * @Route("/linkpan/discover",name="discover_pans")
     */
    public function redirectDiscover()
    {
        return $this->render('UserBundle::discover.html.twig');
    }



    /**
     * @Route("/linkpan/discover/more_pans",name="more_pans")
     */
    public function pensmoreAction()
    {
        $this->getPans('more');
        return $this->render('UserBundle::discover.html.twig');
    }

    /**
     * @Route("/linkpan/discover/discover_filter",name="discover_filter")
     */
    public function filterAction(Request $request)
    {
        $currentUser = $this->getUser();
        $filter = $request->get('filter');
        if($filter == 'New in')
            $this->getPans('first');
        else
        {
            $em = $this->getDoctrine()->getManager();
            // Last Viewed
            if($filter == 'Last Viewed')
            {
                $query = $em->createQuery(
                    'SELECT p
                FROM UserBundle:Pan p
                WHERE p.id IN (
                  SELECT IDENTITY(lastv.pan) FROM UserBundle:PanLastViewed lastv
                  WHERE IDENTITY(lastv.user) = :user
                )
                AND p.type = :type
                '
                )
                    ->setParameter('user', $currentUser->getId())
                    ->setParameter('type', 'Discover');

                $countQuery = $em->createQuery(
                    'SELECT count(DISTINCT p.id)
                FROM UserBundle:Pan p
                WHERE p.id IN (
                  SELECT IDENTITY(lastv.pan) FROM UserBundle:PanLastViewed lastv
                  WHERE IDENTITY(lastv.user) = :user
                ) AND p.type = :type
                '
                )
                    ->setParameter('user', $currentUser->getId())
                    ->setParameter('type', 'Discover');
            }
            //Highest Price
            if($filter == 'Highest Price')
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
                )
                    ->setParameter('user', $currentUser->getId())
                    ->setParameter('type', 'Discover');
                // count
                $countQuery = $em->createQuery(
                    '
                    SELECT count(DISTINCT p.id)  FROM UserBundle:Pan p
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
                '
                )->setParameter('user', $currentUser->getId())
                    ->setParameter('type', 'Discover');
            }
            //LOWEST PRICE
            if($filter == 'Lowest Price')
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
                )
                    ->setParameter('user', $currentUser->getId())
                    ->setParameter('type', 'Discover');
                // count
                $countQuery = $em->createQuery(
                    '
                    SELECT count(DISTINCT p.id)  FROM UserBundle:Pan p
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
                '
                )->setParameter('user', $currentUser->getId())
                    ->setParameter('type', 'Discover');
            }

            // FINAL RESULT
            $pans = $query->getResult();
            $pans = $this->getResult($pans,$currentUser);
            $session = new Session();
            $session->set('discover_pans',$pans);
            // count
            $session->set('Discover_count',$countQuery->getResult()[0][1]);
        }

        return new JsonResponse($this->generateUrl('discover_pans'));
    }

    /**
     * @Route("/linkpan/discover/discover_filter_category",name="discover_filter_category")
     */
    public function discoverfiltercategoryAction(Request $request)
    {
        $category = $request->get('categ');
        if($category == 'Default')
            $this->getPans('first');
        else
        {
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();


            $query = $em->createQuery(
                '
                SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE IDENTITY(p.user) = :user
                AND p.type = :type
                AND p.category = :category
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
                ->setParameter('type', 'Discover')
                ->setParameter('category', $category);

            $result = $query->getResult();

            $pans = $this->getResult($result,$currentUser);
            $session = new Session();
            $session->set('discover_pans',$pans);
            // count
            $query = $em->createQuery(
                '
                SELECT count(DISTINCT p.id)  FROM UserBundle:Pan p
                WHERE IDENTITY(p.user) = :user
                AND p.type = :type
                AND p.category = :category
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
                ->setParameter('type', 'Discover')
                ->setParameter('category', $category);
            $session->set('Discover_count',$query->getResult()[0][1]);
        }

        return new JsonResponse($this->generateUrl('discover_pans'));
    }
}
