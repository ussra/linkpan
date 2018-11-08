<?php

namespace UserBundle\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Post;
use UserBundle\Entity\PostImage;

class PostController extends Controller
{
    /**
     * @Route("/linkpan/home/post",name="home_post")
     */
    public function home_postAction(Request $request)
    {
        $viddir = __DIR__ . "/../../../web/videos";
        $currentUser = $this->getUser();
        $post = new Post();
        $post->setContent($request->get('post_description'));
        $date = date("m/d/Y h:i:s a", time());
        $post->setCreationDate($date);
        $post->setUser($currentUser);
        if (!is_null($request->files->get('post_video')))
        {
            $video = $request->files->get('post_video');
            //
            $files = scandir($viddir);
            $count = count($files)-2;
            $name = $count.'_'.$video->getClientOriginalName();
            //
            $video->move($viddir,$name );
            $post->setVideo($name);

        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();

        if (!is_null($request->files->get('uploadimage')))
        {
            $imgdir = __DIR__ . "/../../../web/images/post";
            foreach ($request->files->get('uploadimage') as $img)
            {
                $pi = new PostImage();
                $pi->setPost($post);
                //
                $files = scandir($imgdir);
                $count = count($files)-2;
                $name = $count.'_'.$img->getClientOriginalName();
                //
                $img->move($imgdir, $name);
                $pi->setImage($name);
                $em->persist($pi);
                $em->flush();
            }
        }
        return $this->render('UserBundle::userbase.html.twig');
    }
}
