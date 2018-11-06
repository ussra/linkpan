<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

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
}