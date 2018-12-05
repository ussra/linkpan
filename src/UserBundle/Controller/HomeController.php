<?php
namespace UserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{

    private function getMembership($session,$currentUser)
    {

        $mrepo = $this->getDoctrine()->getRepository('UserBundle:Membership');
        $membership = $mrepo->findOneBy(
            array('user'=>$currentUser)
        );
        if(sizeof($membership)==1) $membership = 'dockies'; else $membership = 'simple';
        $session->set('membership',$membership);
    }


    private function getCountFollow()
    {
        $session = new Session();
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

    private function Homeresult($result)
    {
        //set number of followers - following
        $this->getCountFollow();
        //
        $posts = array();
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        if(sizeof($result)>0)
        {
            $repo = $this->getDoctrine()->getRepository('AppBundle:User');
            $imagesrepo = $this->getDoctrine()->getRepository('UserBundle:PostImage');
            $boostedrepo = $this->getDoctrine()->getRepository('UserBundle:BoostPost');
            $likerepo = $this->getDoctrine()->getRepository('UserBundle:PostLike');
            $commentrepo = $this->getDoctrine()->getRepository('UserBundle:PostComment');
            foreach ($result as $post)
            {
                $owner = $repo->findOneById($post->getUser());
                //Get images
                $result = $imagesrepo->findBy(
                    array('post'=>$post)
                );
                $images = array();
                if(!empty($result))
                {
                    foreach ($result as $img)
                    {
                        array_push($images,$img->getImage());
                    }
                }
                //Get if boosted or not
                $boosted = $boostedrepo->findOneBy(
                    array('post'=>$post)
                );
                if(is_null($boosted)) $bType = 'not'; else $bType = 'boosted';
                // Get if liked or not by current user
                $liked = $likerepo->findOneBy(
                    array('user'=>$currentUser,'post'=>$post)
                );
                if(is_null($liked)) $like = 'like'; else $like = 'dislike';
                //Get count likes
                $query = $em->createQuery(
                    'SELECT l
                     FROM UserBundle:PostLike l 
                     WHERE l.post = :post
                     AND  l.user != :user'
                )->setParameter('post', $post)
                    ->setParameter('user', $currentUser);
                $likes = $query->getResult();
                // Get post comments
                $comments = array();
                $commresult = $commentrepo->findBy(
                    array('post'=>$post)
                );
                if(sizeof($commresult)>0)
                {
                    foreach ($commresult as $comment)
                    {
                        $commentOwner = $repo->findOneById($comment->getUser());
                        $temp = array(
                            'comment_owner_id'=>$commentOwner->getId(),
                            'comment_owner_image'=>$commentOwner->getImage(),
                            'comment_owner_first_name'=>$commentOwner->getFirstname(),
                            'comment_owner_last_name'=>$commentOwner->getLastname(),
                            'comment_content'=>$comment->getComment(),
                            'comment_id'=>$comment->getId()
                        );
                        array_push($comments,$temp);
                    }
                }
                //
                $temp = array(
                    'id'=>$post->getId(),
                    'owner_id'=>$owner->getId(),
                    'owner_first_name'=>$owner->getFirstname(),
                    'owner_last_name'=>$owner->getLastname(),
                    'owner_company_name'=>$owner->getCompanyName(),
                    'owner_adress'=>$owner->getAdress(),
                    'owner_image'=>$owner->getImage(),
                    'post_creation_date'=>$post->getCreationDate(),
                    'post_content'=>$post->getContent(),
                    'post_video'=>$post->getVideo(),
                    'post_images'=>$images,
                    'post_boosted'=>$bType,
                    'post_like_type'=>$like,
                    'likes_count'=>sizeof($likes),
                    'post_comments'=>$comments
                );
                array_push($posts,$temp);
            }
        }
        return $posts ;
    }



    /**
     * @Route("/linkpan/home/news",name="home_news")
     */
    public function home_newsAction()
    {
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT count(DISTINCT p.id) 
            FROM UserBundle:Post p 
            WHERE p.user = :user
            OR IDENTITY(p.user) IN (SELECT IDENTITY(f.userToFollow) FROM 
            UserBundle:Follow f 
            WHERE f.user = :user)
            OR p.id IN (
              SELECT IDENTITY(bp.post) FROM UserBundle:BoostPost bp
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
            ) 
            ORDER BY p.id DESC 
            '
        )->setParameter('user', $currentUser);
        return new JsonResponse($query->getResult()[0][1]);
    }

    /**
     * @Route("/linkpan/home/more_posts",name="more_posts")
     */
    public function postsmoreAction()
    {
        $session = new Session();
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT DISTINCT p 
            FROM UserBundle:Post p 
            WHERE p.user = :user
            OR IDENTITY(p.user) IN (SELECT IDENTITY(f.userToFollow) FROM 
            UserBundle:Follow f 
            WHERE f.user = :user)
            OR p.id IN (
              SELECT IDENTITY(bp.post) FROM UserBundle:BoostPost bp
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
            ) 
            ORDER BY p.id DESC 
            '
        )->setParameter('user', $currentUser);//
        $result =  $query->getResult();
        $posts = $this->Homeresult($result);
        $session->set('Homeposts',$posts);
        // count
        $query = $em->createQuery(
            'SELECT count(DISTINCT p.id) 
            FROM UserBundle:Post p 
            WHERE p.user = :user
            OR IDENTITY(p.user) IN (SELECT IDENTITY(f.userToFollow) FROM 
            UserBundle:Follow f 
            WHERE f.user = :user)
            OR p.id IN (
              SELECT IDENTITY(bp.post) FROM UserBundle:BoostPost bp
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
            ) 
            ORDER BY p.id DESC 
            '
        )->setParameter('user', $currentUser);
        $session->set('Homeposts_count',$query->getResult()[0][1]);
        //

        //
        return $this->render('UserBundle::userbase.html.twig');
    }


    /**
     * @Route("/linkpan/home",name="home")
     */
    public function indexAction()
    {
        $session = new Session();
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $currentUser->setIsActive(true);
        $em->persist($currentUser);
        $em->flush();
        // Membership
        $this->getMembership($session,$currentUser);
        // count
        $query = $em->createQuery(
            'SELECT count(DISTINCT p.id) 
            FROM UserBundle:Post p 
            WHERE p.user = :user
            OR IDENTITY(p.user) IN (SELECT IDENTITY(f.userToFollow) FROM 
            UserBundle:Follow f 
            WHERE f.user = :user)
            OR p.id IN (
              SELECT IDENTITY(bp.post) FROM UserBundle:BoostPost bp
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
            ) 
            ORDER BY p.id DESC 
            '
        )->setParameter('user', $currentUser);
        $session->set('Homeposts_count',$query->getResult()[0][1]);
        // posts
        $query = $em->createQuery(
            'SELECT DISTINCT p 
            FROM UserBundle:Post p 
            WHERE p.user = :user
            OR IDENTITY(p.user) IN (SELECT IDENTITY(f.userToFollow) FROM 
            UserBundle:Follow f 
            WHERE f.user = :user)
            OR p.id IN (
              SELECT IDENTITY(bp.post) FROM UserBundle:BoostPost bp
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
            )
            AND IDENTITY(p.user) NOT IN (
              SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
            ) 
            ORDER BY p.id DESC 
            '
        )->setParameter('user', $currentUser);
        $result =  $query->setMaxResults(5)->getResult();
        $posts = $this->Homeresult($result);
        $session->set('Homeposts',$posts);
        return $this->render('UserBundle::userbase.html.twig');
    }

    /**
     * @Route("/linkpan/setting",name="setting")
     */
    public function settingAction(Request $request)
    {
        return $this->render('UserBundle::setting.html.twig');
    }

    /**
     * @Route("/linkpan/business_solutions",name="business_solutions")
     */
    public function business_solutionsAction()
    {
        return $this->render('UserBundle::businessSolutions.html.twig');
    }

    /**
     * @Route("/linkpan/boost_posts",name="boost_posts")
     */
    public function boost_postsAction()
    {
        $session = new Session();
        //get all posts
        $repo = $this->getDoctrine()->getRepository('UserBundle:Post');
        $posts = array();
        $result = $repo->findBy(
          array('user'=>$this->getUser())
        );
        if(sizeof($result)>0)
        {
            $boostrepo = $this->getDoctrine()->getRepository('UserBundle:BoostPost');
            foreach ($result as $post)
            {
                // get boost type
                $boost = $boostrepo->findOneBy(
                  array('post'=>$post)
                );
                if(is_null($boost))
                    $boostType = 'Boost';
                else
                    $boostType = 'Remove Boost';
                //
                $temp = array(
                  'post_id'=>$post->getId(),
                  'post_content'=>$post->getContent(),
                  'post_boost_type'=>$boostType
                );
                array_push($posts,$temp);
            }
            $session->set('posts',$posts);
        }
        //
        return $this->render('UserBundle::boostPost.html.twig');
    }


    /**
     * @Route("/linkpan/pans",name="pans")
     */
    public function pansAction()
    {
        // get user pans
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pans = array();
        $result = $repo->findBy(
          array('user'=>$this->getUser())
        );
        if(sizeof($result)>0)
        {
            $boostrepo = $this->getDoctrine()->getRepository('UserBundle:BoostPan');
            foreach ($result as $pan)
            {
                //get boost type
                $boost = $boostrepo->findOneBy(
                    array('pan'=>$pan)
                );
                if(is_null($boost))
                    $boostType = 'Boost';
                else
                    $boostType = 'Remove Boost';
                //
                $temp = array(
                  'pan_id'=>$pan->getId(),
                  'pan_name'=>$pan->getName(),
                  'pan_origine'=>$pan->getOrigin(),
                  'pan_av'=>$pan->getAvailability(),
                  'pan_quantity'=>$pan->getQuentity(),
                  'pan_category'=>$pan->getCategory(),
                  'pan_description'=>$pan->getDescription(),
                  'pan_creation_date'=>$pan->getCreationDate(),
                  'type'=>$pan->getType(),
                  'pan_boost_type'=>$boostType
                );
                array_push($pans,$temp);
            }
        }
        $session = new Session();
        $session->set('boost_pans',array_reverse($pans));
        //
        return $this->render('UserBundle::pans.html.twig');
    }



    /**
     * @Route("/linkpan/globe",name="globe")
     */
    public function globeAction()
    {
        return $this->render('UserBundle::globe.html.twig');
    }

}