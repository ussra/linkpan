<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request,AuthenticationUtils $authenticationUtils)
    {
        $email = $request->get('email');
        $entityManager = $this->getDoctrine()->getManager();
        //
        $query = $entityManager->createQuery(
            'SELECT u.id FROM AppBundle:User u WHERE u.email = :email'
        )
        ->setParameter('email',$email);
        if(sizeof($query->getResult())>0)
            return $this->render('::base.html.twig',array('message'=>$email.' has already an account on linkpan !'));
        else
        {
            // Create a new blank user and process the form
            $user = new User();
            // Encode the new users password
            $encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $request->get('confirmpassword'));
            $user->setPassword($password);
            $user->setEmail($email);
            // Set their role
            $user->setRole('ROLE_USER');
            $user->setFirstname($request->get('firstname'));
            $user->setLastname($request->get('lastname'));

            //active
            $user->setIsActive(false);
            $alpha = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $token = $email.substr(str_shuffle($alpha),0,8);
            $user->setToken($token);
            $user->setStripeId('');
            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();


            //send email
            $message = \Swift_Message::newInstance()
                ->setSubject('Linkpan Account')
                ->setFrom('omarerrabaany@gmail.com')
                ->setTo($email)
                ->setBody('link to activate your email : '.$this->generateUrl('active_account', array('token' => $token,'email'=>$email), UrlGeneratorInterface::ABSOLUTE_URL));
            $mailer = $this->get('mailer');
            $mailer->send($message);
            $spool = $mailer->getTransport()->getSpool();
            $transport = $this->get('swiftmailer.transport.real');
            $spool->flushQueue($transport);

            return $this->render('PublicBundle::signin.html.twig',
                array(
                    'activemessage'=>'we send you an email to activate your profile !',
                    'email'=>$email,
                    'last_username' => $authenticationUtils->getLastUsername(),
                    'error'         => $authenticationUtils->getLastAuthenticationError(),
                    )
            );
        }

    }


    /**
     * @Route("{_locale}/active_account", name="active_account")
     */
    public function activeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy(
            array('token' => $request->query->get('token'), 'email' => $request->query->get('email'))
        );
        if(!is_null($user))
        {
            if(!$user->getisActive())
            {
                $user->setIsActive(true);
                $em->persist($user);
                $em->flush();
                echo '<script language="javascript">alert("account successfully active , sign in !")</script>';
                return $this->render('PublicBundle::signin.html.twig');;
            }
            else
            {
                echo '<script language="javascript">alert("your account is already activated, you can connect")</script>';
                return $this->render('PublicBundle::signin.html.twig');;
            }
        }
        else
        {
            echo '<script language="javascript">alert("No account with these email , please sign up to have an account")</script>';
            return $this->render('PublicBundle::signup.html.twig');
        }
    }
}