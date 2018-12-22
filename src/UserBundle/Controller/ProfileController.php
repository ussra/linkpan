<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
class ProfileController extends Controller
{
    /**
     * @Route("/linkpan/profile",name="profile")
     */
    public function profileAction(Request $request)
    {
        return $this->render('UserBundle::profile.html.twig');
    }

    /**
     * @Route("/linkpan/search_profile",name="search_profile")
     */
    public function searchprofileAction(Request $request)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $repo->findOneById($request->get('user'));
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            '
                SELECT b FROM UserBundle:Block b WHERE 
                IDENTITY(b.user) =:currentUser AND IDENTITY(b.userToBlock) = :otherUser
                OR 
                IDENTITY(b.user) =:otherUser AND IDENTITY(b.userToBlock) = :currentUser
            '
        )->setParameter('currentUser', $currentUser->getId())
        ->setParameter('otherUser', $user);
        $blockResult = $query->getResult();
        if(!empty($blockResult)){
            echo '<script language="javascript">alert("You cannot follow this user !")</script>';
            //return new JsonResponse('BLOCK');
        }
        else
        {
            // check follow
            $frepo = $this->getDoctrine()->getRepository('UserBundle:Follow');
            $type = $frepo->findOneBy(
                array('user'=>$this->getUser(),'userToFollow'=>$user)
            );
            if(is_null($type))
                $type_result = 'follow';
            else
                $type_result = 'unfollow';
            //Get Followers
            $followers = array();
            $flresult = $frepo->findBy(
                array('userToFollow'=>$user)
            );
            if(sizeof($flresult)>0)
            {
                foreach ($flresult as $value)
                {
                    $follower = $repo->findOneById($value->getUser());
                    $tempf = array(
                        'follower_id'=>$follower->getId(),
                        'follower_first_name'=>$follower->getFirstname(),
                        'follower_last_name'=>$follower->getLastname(),
                        'follower_image'=>$follower->getImage(),
                        'follower_company_name'=>$follower->getCompanyName()
                    );
                    array_push($followers,$tempf);
                }
            }
            //Get Following
            $following = array();
            $fogresult =$frepo->findBy(
                array('user'=>$user)
            );
            if(sizeof($fogresult)>0)
            {
                foreach ($fogresult as $value)
                {
                    $follg= $repo->findOneById($value->getUserToFollow());
                    $tempfg = array(
                        'follower_id'=>$follg->getId(),
                        'follower_first_name'=>$follg->getFirstname(),
                        'follower_last_name'=>$follg->getLastname(),
                        'follower_image'=>$follg->getImage(),
                        'follower_company_name'=>$follg->getCompanyName()
                    );

                    array_push($following,$tempfg);

                }
            }
            //Get membership
            $mrepo = $this->getDoctrine()->getRepository('UserBundle:Membership');
            $result = $mrepo->findOneBy(array('user'=>$user));
            if(!is_null($result))
                $membership  = 'dockies';
            else
                $membership  = 'simple';
            //
            $temp = array(
                'user_id'=>$user->getId(),
                'user_first_name'=>$user->getFirstname(),
                'user_last_name'=>$user->getLastname(),
                'user_company_name'=>$user->getCompanyName(),
                'user_image'=>$user->getImage(),
                'user_adress'=>$user->getAdress(),
                'user_website'=>$user->getWebsite(),
                'user_email'=>$user->getEmail(),
                'follow_result'=>$type_result,
                'followers'=>$followers,
                'following'=>$following,
                'user_membership'=>$membership
            );

            $session = new Session();
            $session->set('user_info',$temp);

            $this->getCountFollow($session);
            $this->recentActivities($session,$user);

        }
        return $this->render('UserBundle::profile.html.twig');

    }

    private function getCountFollow($session)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Follow');
        //Following
        $followingdata = $repo->findBy(
            array('user'=>$currentUser)
        );
        if(sizeof($followingdata)>0) $following = sizeof($followingdata); else $following = 0;
        $session->set('following',$following);
        //Followers
        $folllowersdata = $repo->findBy(
            array('userToFollow'=>$currentUser)
        );
        if(sizeof($folllowersdata)>0) $followers = sizeof($folllowersdata); else $followers = 0;
        $session->set('followers',$followers);
    }


    private function recentActivities($session,$user){
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT d FROM UserBundle:ObjectShare d 
            Where d.user = :currentUser  ORDER BY d.id DESC  ')
            ->setParameter('currentUser',$user);
        $result = $query->getResult();
        $shares = array();
        if(sizeof($result)>0)
        {
            $repo = $this->getDoctrine()->getRepository('UserBundle:Post');
            $panRepo = $this->getDoctrine()->getRepository('UserBundle:Pan');
            $groupPostRepo = $this->getDoctrine()->getRepository('UserBundle:GroupPost');
            $imageRepo = $this->getDoctrine()->getRepository('UserBundle:PostImage');
            $GroupimageRepo = $this->getDoctrine()->getRepository('UserBundle:GroupPostImage');
            foreach ($result as $item)
            {
                if($item->getType() == 'post')
                {
                    $post = $repo->findOneById($item->getObjectId());

                    if(!is_null($post))
                    {
                        $images = $imageRepo->findBy(
                            array('post'=>$post)
                        );
                        $imgResult = array();
                        if(!is_null($images))
                        {
                            foreach ($images as $img)
                                array_push($imgResult,$img->getImage());
                        }
                        $temp = array(
                            'type'=> 'post',
                            'object'=> $post,
                            'images'=>$imgResult
                        );
                        array_push($shares,$temp);
                    }
                }
                if($item->getType() == 'pan')
                {
                    $pan = $panRepo->findOneById($item->getObjectId());

                    if(!is_null($pan))
                    {
                        $temp = array(
                            'type'=> 'pan',
                            'object'=>$pan
                        );
                        array_push($shares,$temp);
                    }
                }
                else
                {
                    $post = $groupPostRepo->findOneById($item->getObjectId());

                    if(!is_null($post))
                    {
                        $images = $GroupimageRepo->findBy(
                            array('groupPost'=>$post)
                        );
                        $imgResult = array();
                        if(!is_null($images))
                        {
                            foreach ($images as $img)
                                array_push($imgResult,$img->getImage());
                        }
                        $temp = array(
                            'type'=> 'Group post',
                            'object'=>$post,
                            'images'=>$imgResult
                        );
                        array_push($shares,$temp);
                    }
                }
            }
        }

        $session->set('profile_recent_activities',$shares);
    }



    /**
     * @Route("/linkpan/search_profile/delete_recent_activity",name="delete_recent_activity")
     */
    public function delete_recent_activityAction(Request $request)
    {
        //object
        $repo = $this->getDoctrine()->getRepository('UserBundle:ObjectShare');
        $object = $repo->findOneById($request->get('object'));
        if(!is_null($object))
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($object);
            $em->flush();
        }
        return new JsonResponse('DONE');
    }
}
