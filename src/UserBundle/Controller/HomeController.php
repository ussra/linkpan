<?php
namespace UserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

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
        return $this->render('UserBundle::pans.html.twig');
    }
}