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


    private function getPosts($session,$currentUser)
    {
        $posts = array();
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
            )'
        )->setParameter('user', $currentUser);
        $result =  $query->getResult();
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
                            'comment_content'=>$comment->getComment()
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

        $session->set('Homeposts',array_reverse($posts));
    }


    /**
     * @Route("/linkpan/home",name="home")
     */
    public function indexAction()
    {
        $session = new Session();
        $currentUser = $this->getUser();
        // Membership
        $this->getMembership($session,$currentUser);
        // posts
        $this->getPosts($session,$currentUser);
        //
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
     * @Route("/linkpan/terms",name="terms")
     */
    public function termsAction()
    {
        return $this->render('UserBundle::terms.html.twig');
    }

    /**
     * @Route("/linkpan/privacy",name="privacy")
     */
    public function privacyAction()
    {
        return $this->render('UserBundle::privacy.html.twig');
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
        $session = new Session();
        $session->set('discover_pans',array_reverse($pans));
        return $this->render('UserBundle::discover.html.twig');
    }


    /**
     * @Route("/linkpan/groups",name="groups")
     */
    public function groupsAction()
    {
        $session = new Session();
        $currentUser = $this->getUser();
        //Get user Groups
        $user_groups = array();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $userGroups = $repo->findBy(
          array('user'=>$currentUser)
        );
        if(sizeof($userGroups)>0)
        {
            foreach ($userGroups as $group)
            {
                $temp = array(
                    'group_id'=>$group->getId(),
                    'group_name'=>$group->getName(),
                    'group_image'=>$group->getImage(),
                );
                array_push($user_groups,$temp);
            }
        }
        $session->set('user_groups',$user_groups);
        //
        return $this->render('UserBundle::groups.html.twig');
    }
}