<?php

namespace UserBundle\Controller;

use Doctrine\ORM\Persisters\Entity\JoinedSubclassPersister;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
class SearchController extends Controller
{
    /**
     * @Route("/linkpan/setting/search",name="search")
     */
    public function searchAction(Request $request)
    {
        $users = array();
        $user = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $result = $repo->createQueryBuilder('u')
            ->where('u.id != :id')
            ->andWhere("CONCAT(u.firstname ,' ', u.lastname) LIKE :filter")
            ->andWhere("u.id not in (select identity(b.userToBlock) from UserBundle:Block b where b.user = :id)")
            ->andWhere("u.id not in (select identity(b2.user) from UserBundle:Block b2 where b2.userToBlock = :id)")
            ->setParameter('id',$user->getId() )
            ->setParameter('filter', '%' . $request->get('searchinput') . '%')
            ->getQuery()
            ->getResult();
        if(sizeof($result)>0)
        {
            $followrepo = $this->getDoctrine()->getRepository('UserBundle:Follow');
            foreach ($result as $val)
            {
                //Get follow type
                $type = $followrepo->findOneBy(
                  array('user'=>$user,'userToFollow'=>$val)
                );
                if(is_null($type))
                    $type_result = 'follow';
                else
                    $type_result = 'unfollow';
                //
                $temp = array(
                    'user_id'=>$val->getId(),
                    'user_first_name'=>$val->getFirstName(),
                    'user_last_name'=>$val->getLastName(),
                    'user_image'=>$val->getImage(),
                    'user_company_name'=>$val->getCompanyName(),
                    'user_type_follow'=>$type_result
                );
                array_push($users,$temp);
            }
        }
        $session = new Session();
        $session->set('users',$users);
        return $this->render('UserBundle::search.html.twig');
    }
}
