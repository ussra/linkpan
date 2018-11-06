<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


class ListController extends Controller
{


    /**
     * @Route("/welcome", name="test")
     */
    public function showAction(Request $request)
    {
        $user = $this->getUser();
        $oldPassword = $request->get('old');
        $encoderService = $this->container->get('security.password_encoder');
        $match = $encoderService->isPasswordValid($user, $oldPassword);

       /** if($match === true) {
            /**$encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $newPassword);
            $user->setPassword($password);

        }

        print("Old password is: ".$oldPassword).'<br>';
        print("New password is: ".$newPassword).'<br>';
        var_dump($match);**/
        var_dump($match);
        return new JsonResponse('');
    }
}