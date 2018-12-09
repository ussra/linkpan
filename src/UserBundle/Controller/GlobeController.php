<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class GlobeController extends Controller
{
    /**
     * @Route("/linkpan/globe/discover",name="globe_pro_discover")
     */
    public function globe_pro_discoverAction(Request $request)
    {
        return $this->render('UserBundle::discover.html.twig');
    }
    /**
     * @Route("/linkpan/globe/filter",name="filter_by_country")
     */
    public function filterByCountryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $query = $em->createQuery(
            'SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE p.type = :type 
                AND (
                  IDENTITY(p.user) NOT IN (
                    SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                  )
                  OR 
                  IDENTITY(p.user) NOT IN (
                    SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                  ) 
                )
                AND p.origin = :origin
                ORDER BY p.id DESC 
            '
        )->setParameter('user', $currentUser->getId())
            ->setParameter('type', 'Discover')//
            ->setParameter('origin', $request->get('country'));
        $pans = $query->getResult();
        if(!is_null($pans))
        {
            $session = new Session();
            $result = array();
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
            $session->set('DiscoverPans',$result);
            $session->set('DiscoverPans_count',0);
            //
            $session->set('filters',0);
            //
            return new JsonResponse($this->generateUrl('globe_pro_discover'));
        }
        else
            return new JsonResponse('NULL');
    }
}
