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


    /**
     * @Route("/linkpan/home",name="home")
     */
    public function indexAction()
    {
        $session = new Session();
        $currentUser = $this->getUser();
        // Membership
        $this->getMembership($session,$currentUser);
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
}