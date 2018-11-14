<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\PanLastViewed;

class DiscoverController extends Controller
{
    /**
     * @Route("/linkpan/discover/product",name="view_product")
     */
    public function discoverProductAction(Request $request)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneById($request->get('product'));
        if(!is_null($pan))
        {
            //Get average rating
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
            //Get Reviews
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
              'product_id'=>$pan->getId(),
              'product_name'=>$pan->getName(),
               'product_other_name'=>$pan->getOthername(),
                'product_image'=>$pan->getImage(),
               'product_average_rating'=>$average,
                'product_owner_id'=>$currentUser->getId(),
                'product_owner_first_name'=>$currentUser->getFirstname(),
                'product_owner_last_name'=>$currentUser->getLastname(),
                'prodcut_description'=>$pan->getDescription(),
                'product_category'=>$pan->getCategory(),
                'product_price'=>$pan->getPrice(),
                'product_reviews'=>array_reverse($reviews)
            );

            // save in last viewed
            $lastvrepo = $this->getDoctrine()->getRepository('UserBundle:PanLastViewed');
            $pl = $lastvrepo->findOneBy(
              array('user'=>$currentUser,'pan'=>$pan)
            );

            if(is_null($pl))
            {
                $plastviewed = new PanLastViewed();
                $plastviewed->setUser($currentUser);
                $plastviewed->setPan($pan);
                $em = $this->getDoctrine()->getManager();
                $em->persist($plastviewed);
                $em->flush();
            }

            //

            $session = new Session();
            $session->set('selected_product',$temp);
            return $this->render('UserBundle::product.html.twig');
        }
        else
        {
            echo '<script language="javascript">alert("You cannot access this pan now , can you try again please? !")</script>';
            return $this->forward('UserBundle:Home:discover');
        }

    }


    private function getPansResult($result,$currentUser)
    {
        $pans = array();
        if(sizeof($result)>0)
        {
            $repo = $this->getDoctrine()->getRepository('AppBundle:User');
            $brepo = $this->getDoctrine()->getRepository('UserBundle:BoostPan');
            $prrepo = $this->getDoctrine()->getRepository('UserBundle:PanRating');
            foreach ($result as $val)
            {
                //Get if the pan is boosted
                $boost = $brepo->findOneBy(
                    array('pan'=>$val)
                );
                if(!is_null($boost)) $type = 'boost'; else $type ='simple';
                //Get pan owner
                $owner = $repo->findOneById($val->getUser());
                //Get pan rating
                $ratingvalue = '0';
                if($currentUser->getId() != $owner->getId())
                {
                    $ratresult = $prrepo->findOneBy(
                        array('user'=>$this->getUser(),'pan'=>$val)
                    );
                    if(!is_null($ratresult))
                    {
                        $rating = 'fixed';
                        $ratingvalue = $ratresult->getRate();
                    }
                    else
                        $rating ='rate';
                }
                else
                    $rating ='ERR';
                //
                $temp = array(
                    'pan_id'=>$val->getId(),
                    'pan_name'=>$val->getName(),
                    'pan_other_name'=>$val->getOthername(),
                    'pan_description'=>$val->getDescription(),
                    'pan_image'=>$val->getImage(),
                    'pan_price'=>$val->getPrice(),
                    'pan_category'=>$val->getCategory(),
                    'pan_owner'=>$owner,
                    'pan_boosted'=>$type,
                    'pan_rating'=>$rating,
                    'pan_rating_value'=>$ratingvalue
                );
                array_push($pans,$temp);
            }
        }

        //
        return $pans;
    }


    /**
     * @Route("/linkpan/discover",name="discover")
     */
    public function discoverAction()
    {
        $currentUser = $this->getUser();
        //get pans
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT DISTINCT p
            FROM UserBundle:Pan p
            WHERE p.user = :user
            OR IDENTITY(p.user) = (
              SELECT IDENTITY(f.userToFollow)
              FROM UserBundle:Follow f 
              WHERE f.user = :user
            )
            OR p.id IN (
              SELECT IDENTITY(bp.pan) 
              FROM UserBundle:BoostPan bp 
            )
            AND p.type = :typepan
            '
        )
            ->setParameter('user', $this->getUser())
            ->setParameter('typepan', 'Discover');
        $result = $query->getResult();
        $pans = $this->getPansResult($result,$currentUser);
        $session = new Session();
        $session->set('discover_pans',array_reverse($pans));
        return $this->render('UserBundle::discover.html.twig');
    }


    /**
     * @Route("/linkpan/discover/discover_filter",name="discover_filter")
     */
    public function discover_filterAction(Request $request)
    {
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if($request->get('obj') == 'New in') {
            $query = $em->createQuery(
                'SELECT DISTINCT p
            FROM UserBundle:Pan p
            WHERE p.user = :user
            OR IDENTITY(p.user) = (
              SELECT IDENTITY(f.userToFollow)
              FROM UserBundle:Follow f 
              WHERE f.user = :user
            )
            OR p.id IN (
              SELECT IDENTITY(bp.pan) 
              FROM UserBundle:BoostPan bp 
            )
            AND p.type = :typepan
            '
            )
                ->setParameter('user', $this->getUser())
                ->setParameter('typepan', $request->get('type'));
        }

        if($request->get('obj') == 'Highest Price') {
            $query = $em->createQuery(
                'SELECT DISTINCT p
            FROM UserBundle:Pan p
            WHERE p.user = :user
            OR IDENTITY(p.user) = (
              SELECT IDENTITY(f.userToFollow)
              FROM UserBundle:Follow f 
              WHERE f.user = :user
            )
            OR p.id IN (
              SELECT IDENTITY(bp.pan) 
              FROM UserBundle:BoostPan bp 
            )
            AND p.type = :typepan
            ORDER BY p.price ASC
            '
            )
                ->setParameter('user', $this->getUser())
                ->setParameter('typepan', $request->get('type'));
        }

        if($request->get('obj') == 'Lowest Price')
        {
            $query = $em->createQuery(
                'SELECT DISTINCT p
            FROM UserBundle:Pan p
            WHERE p.user = :user
            OR IDENTITY(p.user) = (
              SELECT IDENTITY(f.userToFollow)
              FROM UserBundle:Follow f 
              WHERE f.user = :user
            )
            OR p.id IN (
              SELECT IDENTITY(bp.pan) 
              FROM UserBundle:BoostPan bp 
            )
            AND p.type = :typepan
            ORDER BY p.price DESC '
            )
                ->setParameter('user', $currentUser->getId())
                ->setParameter('typepan', $request->get('type'));
        }

        if($request->get('obj') == 'Last Viewed')
        {
            $query = $em->createQuery(
                'SELECT p
                FROM UserBundle:Pan p
                WHERE
                  p.type = :typepan and p.id IN (
                  SELECT IDENTITY(lastv.pan) FROM UserBundle:PanLastViewed lastv
                  WHERE lastv.user = :user
                )
                '
            )
                ->setParameter('user', $currentUser->getId())
            ->setParameter('typepan', $request->get('type'));

        }

        $pans = $query->getResult();
        $data = $this->getPansResult($pans,$currentUser);
        $session = new Session();
        if($request->get('type') == 'Discover')
        {
            $session->set('discover_pans',array_reverse($data));
            return new JsonResponse($this->generateUrl('discover_view'));
        }
        if($request->get('type') == 'Dockies')
        {
            $session->set('dockies_pans',array_reverse($data));
            return new JsonResponse($this->generateUrl('dockies_pans'));
        }

    }

    /**
     * @Route("/linkpan/discover/discover_by_group",name="discover_by_group")
     */
    public function discover_by_groupAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        /**if($request->get('obj') == 'Default')

        $products = $repo->findBy(
            array('category' => $request->get('obj'),'type'=>$request->get('type'))
        );
        $result = $this->getPansResult($products,$this->getUser());
        $session = new Session();**/

        return new JsonResponse('');
    }

    /**
     * @Route("/linkpan/dockies_pans",name="dockies_pans")
     */
    public function dockies_pansAction()
    {
        return $this->render('UserBundle::dockies.html.twig');
    }

    /**
     * @Route("/linkpan/dockies",name="dockies")
     */
    public function dockiesAction()
    {
        $currentUser = $this->getUser();
        //get pans
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT DISTINCT p
            FROM UserBundle:Pan p
            WHERE  p.type = :typepan
            '
        )
            ->setParameter('typepan', 'Dockies');
        $result = $query->getResult();
        $pans = $this->getPansResult($result,$currentUser);
        $session = new Session();
        $session->set('dockies_pans',array_reverse($pans));
        return $this->render('UserBundle::dockies.html.twig');
    }
}
