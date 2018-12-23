<?php

namespace PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
class LinkpanController extends Controller
{

    private function GetNewpans(){
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $query = $em->createQuery(
            '
                SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE p.type = :type 
                ORDER BY p.id DESC
            '
        )->setParameter('type', 'Discover');
        $pans = $query->setMaxResults(15)->getResult();
        $session->set('DiscoverPans',$pans);
    }

    /**
     * @Route("{_locale}/linkpan",name="linkpan")
     */
    public function indexAction()
    {
        // get pans
        $this->GetNewpans();
        //
        return $this->render('::base.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/signin",name="signin")
     */
    public function signinAction()
    {
        return $this->render('PublicBundle::signin.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/signup",name="signup")
     */
    public function signupAction()
    {
        return $this->render('PublicBundle::signup.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/terms",name="terms")
     */
    public function termsAction()
    {
        return $this->render('PublicBundle::terms.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/privacy",name="privacy")
     */
    public function privacyAction()
    {
        return $this->render('PublicBundle::privacy.html.twig');
    }


    /**
     * @Route("{_locale}/linkpan/faqs",name="faqs")
     */
    public function faqsAction()
    {
        return $this->render('PublicBundle::faqs.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/contact",name="contact")
     */
    public function contactAction()
    {
        return $this->render('PublicBundle::contactus.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/contactus",name="contactus")
     */
    public function contactusAction(Request $request)
    {
        //send email
        $message = \Swift_Message::newInstance()
            ->setSubject('Linkpan Contact : '.$request->get('reason'))
            ->setFrom('linkpandemo@gmail.com')
            ->setTo('linkpandemo@gmail.com')
            ->setBody('Description :  '.$request->get('description').' , FROM : '.$request->get('fullname').
                ' , Email: '.$request->get('email').', Phone : '.$request->get('phone').
                ', Company : '.$request->get('company'));
        $mailer = $this->get('mailer');
        $mailer->send($message);
        $spool = $mailer->getTransport()->getSpool();
        $transport = $this->get('swiftmailer.transport.real');
        $spool->flushQueue($transport);

        return $this->render('PublicBundle::contactus.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/about",name="about")
     */
    public function aboutAction()
    {
        return $this->render('PublicBundle::aboutus.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/forget_password",name="forget_password")
     */
    public function forget_passwordAction()
    {
        return $this->render('PublicBundle::forgetpassword.html.twig');
    }

    private function GetPans($category,$length,$filter){
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        if($category == null)
            $categorySearch = 'Agriculture'; else $categorySearch = $category;

        if($length == null)
            $lengthSearch = 15; else $lengthSearch = $length;

        if($filter == null || $filter == 'Default Sorting')
        {
            $query = $em->createQuery(
                '
                SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE p.type = :type 
                AND p.category = :category
                ORDER BY p.id DESC
            '
            )->setParameter('type', 'Discover')
                ->setParameter('category', $categorySearch);
        }

        if($filter == 'Highest Price')
        {
            $query = $em->createQuery(
                '
                SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE p.type = :type 
                AND p.category = :category
                ORDER BY p.price DESC
            '
            )->setParameter('type', 'Discover')
                ->setParameter('category', $categorySearch);
        }

        if($filter == 'Lowest Price')
        {
            $query = $em->createQuery(
                '
                SELECT DISTINCT p FROM UserBundle:Pan p
                WHERE p.type = :type 
                AND p.category = :category
                ORDER BY p.price ASC 
            '
            )->setParameter('type', 'Discover')
                ->setParameter('category', $categorySearch);
        }

        $pans = $query->setMaxResults($lengthSearch)->getResult();
        $session->set('pub_discover_pans',$pans);
        $session->set('pub_discover_category',$categorySearch);
        $session->set('pub_discover_length',$length);
    }

    /**
     * @Route("{_locale}/linkpan/discover/pans",name="discoverPans")
     */
    public function discoverdAction(Request $request)
    {
        $category = $request->get('category');
        $length = $request->get('length');
        $filter = $request->get('filter');
        $this->GetPans($category,$length,$filter);
        return $this->render('PublicBundle::discover.html.twig');
    }


    private function GetByName($name)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneBy(
          array('name'=>$name)
        );
        if(!is_null($pan))
            return $pan->getId();
        else
            return 0;
    }

    /**
     * @Route("{_locale}/linkpan/discover/pans/ByName",name="ByName")
     */
    public function getByNameAction(Request $request)
    {
        $id = $this->GetByName($request->get('name'));
        if($id > 0)
            return $this->redirect($this->generateUrl('details',array('pan'=>$id)));
        else
        {
            echo '<script language="javascript">alert("There s no pan to display with this name :'.$request->get('name').', please sign in and display other pans ")</script>';
            return $this->render('PublicBundle::signin.html.twig');
        }
    }

    /**
     * @Route("{_locale}/linkpan/discover/pans/details",name="details")
     */
    public function detailsdAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Pan');
        $pan = $repo->findOneById($request->get('pan'));
        $session = new Session();
        $session->set('details',$pan);
        //get average rating
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
        $session->set('average',$average);
        //get reviews with rating
        $results = array();
        $reviewrepo = $this->getDoctrine()->getRepository('UserBundle:PanReview');
        $reviews = $reviewrepo->findBy(
          array('pan'=>$pan)
        );
        if(!is_null($reviews))
        {
            foreach ($reviews as $review)
            {
                //find review
                $rate = $ratingrepo->findOneBy(
                    array('pan'=>$pan,'user'=>$review->getUser())
                );
                //
                $temp = array(
                  'review'=>$review,
                  'rate'=>$rate
                );
                array_push($results,$temp);
            }
        }
        $session->set('reviews',$results);
        return $this->render('PublicBundle::detail.html.twig');
    }


    /**
     * @Route("{_locale}/linkpan/signin/forgetPassword/CheckEmail",name="forgetPassword_CheckEmail")
     */
    public function CheckEmailAction(Request $request)
    {
        $email = $request->get('Email');
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user  = $repo->findOneBy(
          array('email'=>$email)
        );
        // Get if he has a security question
        if(is_null($user->getSecuityQuestion()))
        {
            // send mail
        }
        else
        {
            // send data
        }
        //
        /*if(!is_null($user))
            return new JsonResponse($user->getId());
        else*/
        return new JsonResponse(0);
    }
}
