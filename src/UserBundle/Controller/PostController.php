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
        /**var_dump($request->files->get('uploadimage'));
        var_dump($request->files->get('post_video'));
        var_dump($request->get('post_description'));**/

        $post = new Post();
        $post->setContent($request->get('post_description'));
        $date = date("m/d/Y h:i:s ", time());
        $post->setCreationDate($date);
        $post->setUser($this->getUser());
        // set video
        if (!is_null($request->files->get('post_video')))
        {
            $viddir = __DIR__ . "/../../../web/videos";
            $video = $request->files->get('post_video');
            $files = scandir($viddir);
            $count = count($files)-2;
            $name = $count.'_'.$video->getClientOriginalName();
            $video->move($viddir,$name );
            $post->setVideo($name);
        }
        //
        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();
        // save images
        $images = $request->files->get('uploadimage');
        if(!is_null($images[0]))
        {
            $imgdir = __DIR__ . "/../../../web/images/post";
            foreach ($images as $img)
            {
                $pi = new PostImage();
                $pi->setPost($post);
                $files = scandir($imgdir);
                $count = count($files)-2;
                $name = $count.'_'.$img->getClientOriginalName();
                $img->move($imgdir,$name );
                $pi->setImage($name);
                $em->persist($pi);
                $em->flush();
            }
        }

        return $this->forward('UserBundle:Home:index');
    }
}
