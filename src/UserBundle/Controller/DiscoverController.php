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
     * @Route("{_locale}/linkpan/discover",name="discover_pans")
     */
    public function redirectDiscoverAction()
    {
        return $this->render('UserBundle::discover.html.twig');
    }



    /**
     * @Route("{_locale}/linkpan/discover/more_pans",name="more_pans")
     */
    public function pensmoreAction()
    {
        $this->getPans('more');
        return $this->render('UserBundle::discover.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/discover/discover_filter",name="discover_filter")
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
     * @Route("{_locale}/linkpan/discover/discover_filter_category",name="discover_filter_category")
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


    /**
     * @Route("{_locale}/linkpan/discover/product",name="view_product")
     */
    public function view_productAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneById($request->get('product'));
        if(!is_null($pan))
        {
            $session = new Session();
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
            // get product reviews
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
                'product'=>$pan,
                'average'=>$average,
                'reviews'=>$reviews
            );
            $session->set('selected_product',$temp);
            return $this->render('UserBundle::product.html.twig');
        }
        else
        {

        }
    }
}
