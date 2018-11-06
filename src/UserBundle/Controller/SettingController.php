<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Billing;
use Symfony\Component\HttpFoundation\Session\Session;

class SettingController extends Controller
{

    private function getBilling($user)
    {
        $billinglist = array();
        $repository = $this->getDoctrine()->getRepository(Billing::class);
        $list = $repository->findBy(
            array('user' => $user->getId())
        );
        if(sizeof($list)>0)
        {
            foreach ($list as $value)
            {
                $temp = array(
                    'id'=>$value->getId(),
                    'number'=>$value->getCardNumber(),
                    'brand'=>$value->getBrand()
                );
                array_push($billinglist,$temp);
            }
        }
        $session = new Session();
        $session->set('billing',$billinglist);

    }

    /**
     * @Route("/linkpan/setting_access",name="setting_access")
     */
    public function setting_accAction(Request $request)
    {
        $user = $this->getUser();
        $encoderService = $this->container->get('security.password_encoder');
        $match = $encoderService->isPasswordValid($user, $request->get('password'));
        if($match)
        {
            $this->getBilling($user);
            return new JsonResponse($this->generateUrl('setting'));
        }
        else
            return new JsonResponse('ERR');
    }

    /**
     * @Route("/linkpan/setting/contact",name="contact")
     */
    public function contactAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $dir = __DIR__."/../../../web/images/profile";
        $user->setFirstname($request->get('firstname'));
        $user->setLastname($request->get('lastname'));
        $user->setEmail($request->get('email'));
        $user->setAlternativeEmail($request->get('alteremail'));
        $user->setFax($request->get('fax'));
        $user->setTel($request->get('tel'));
        $user->setAdress($request->get('adress'));
        if(! is_null($request->files->get("image")))
        {
            $img = $request->files->get("image");
            //generate name
            $name = ($request->get('email')).'_'.$img->getClientOriginalName();
            $img->move($dir, $name);
            $user->setImage($name);
        }
        $em->persist($user);
        $em->flush();
        //Get Billing cards
        $this->getBilling($user);
        //
        return $this->render('UserBundle::setting.html.twig');
    }

    /**
     * @Route("/linkpan/setting/company_informations",name="company")
     */
    public function companyAction(Request $request)
    {
        $dir = __DIR__."/../../../web/images/logo";
        $user = $this->getUser();
        $user->setCompanyName($request->get('name'));
        if(! is_null($request->files->get("uploadimage")))
        {
            $img = $request->files->get("uploadimage");
            $name = ($user->getEmail()).'_Logo_'.$img->getClientOriginalName();
            $img->move($dir, $name);
            $user->setLogo($name);
        }
        $user->setYearEstablished($request->get('year'));
        $user->setWebsite($request->get('website'));
        $user->setBusinessType($request->get('business'));
        $user->setNumberEmp($request->get('number'));
        $user->setRegAdress($request->get('regadress'));
        $user->setOptAdress($request->get('optadress'));
        $user->setAboutUs($request->get('aboutus'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        //Get Billing cards
        $this->getBilling($user);
        //
        return $this->render('UserBundle::setting.html.twig');
    }

    /**
     * @Route("/linkpan/setting/update_password",name="updatepassword")
     */
    public function upPasswordAction(Request $request)
    {
        $user = $this->getUser();
        $encoderService = $this->container->get('security.password_encoder');
        $match = $encoderService->isPasswordValid($user, $request->get('oldpass'));
        if($match)
        {
            $encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $request->get('newpass'));
            $user->setPassword($password);
            echo '<script language="javascript">alert("Password Updated !")</script>';
            //Get Billing cards
            $this->getBilling($user);
            //
            return $this->render('UserBundle::setting.html.twig');
        }
        else
        {
            echo '<script language="javascript">alert("Incorrect password !")</script>';
            //Get Billing cards
            $this->getBilling($user);
            //
            return $this->render('UserBundle::setting.html.twig');
        }
    }

    /**
     * @Route("/linkpan/setting/security_question",name="securityquestion")
     */
    public function securityquestionAction(Request $request)
    {
        $user = $this->getUser();
        $user->setSecuityQuestion($request->get('securityQuestion'));
        $user->setResponse($request->get('response'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        echo '<script language="javascript">alert("Your security question is updated ")</script>';
        //Get Billing cards
        $this->getBilling($user);
        //
        return $this->render('UserBundle::setting.html.twig');
    }

    /**
     * @Route("/linkpan/setting/billing",name="billing")
     */
    public function billingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $stripeClient = $this->get('flosch.stripe.client');
        $customer = null;
        if(is_null($user->getStripeId()))
        {
            $customer = $stripeClient->createCustomer(null, $user->getEmail());
            $user->setStripeId($customer->id);
            $em->persist($user);
            $em->flush();
        }
        else
            // Retrieve the customer
            $customer = $stripeClient->retrieveCustomer($user->getStripeId());

        if(!is_null($customer))
        {
            $token = $request->get('token');
            $repo = $this->getDoctrine()->getRepository('UserBundle:Billing');
            $existBilling = $repo->findOneBy(
              array('cardNumber'=>$token['card']['last4'],'user'=>$user)
            );

            if(sizeof($existBilling) == 1)
                return new JsonResponse('exist');
            else
            {
                $customer->sources->create(array("source" => $token['id']));
                // save card data
                $billing = new Billing();
                $billing->setCardNumber($token['card']['last4']);
                $billing->setBrand($token['card']['brand']);
                $billing->setCardId($token['card']['id']);
                $billing->setUser($user);
                $em->persist($billing);
                $em->flush();
                //Get Billing cards
                $this->getBilling($user);
                return new JsonResponse($this->generateUrl('setting'));
            }
        }
        else
            return new JsonResponse('ERR');
    }

    /**
     * @Route("/linkpan/setting/remove_card",name="remove_card")
     */
    public function removecardAction(Request $request)
    {
        $user = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Billing');
        $userBillings  = $repo->findBy(
            array('user'=>$user)
        );
        if(sizeof($userBillings)<= 1)
        {
            return new JsonResponse('ERR');
        }
        else
        {
            $stripeClient = $this->get('flosch.stripe.client');
            $customer = $stripeClient->retrieveCustomer($user->getStripeId());
            $repo = $this->getDoctrine()->getRepository('UserBundle:Billing');
            $billing = $repo->findOneById($request->get('billingid'));
            if(!is_null($billing))
            {
                $em = $this->getDoctrine()->getManager();
                $customer->sources->retrieve($billing->getCardId())->delete();
                $em->remove($billing);
                $em->flush();
                $this->getBilling($user);
                return new JsonResponse($this->generateUrl('setting'));
            }
            else
                return new JsonResponse('ERR');
        }
    }

    /**
     * @Route("/linkpan/setting/links",name="links")
     */
    public function linksAction(Request $request)
    {
        $user = $this->getUser();
        $user->setFacebook($request->get('facebook'));
        $user->setTwitter($request->get('twitter'));
        $user->setLinkedin($request->get('linkedin'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        echo '<script language="javascript">alert("networking links updated !")</script>';
        return $this->render('UserBundle::setting.html.twig');
    }

}
