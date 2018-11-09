<?php

namespace UserBundle\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Post;
use UserBundle\Entity\PostComment;
use UserBundle\Entity\PostImage;
use UserBundle\Entity\PostLike;
use UserBundle\Entity\PostShare;

class PostController extends Controller
{
    /**
     * @Route("/linkpan/home/post",name="home_post")
     */
    public function home_postAction(Request $request)
    {
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

    /**
     * @Route("/linkpan/home/post_like",name="post_like")
     */
    public function post_likeAction(Request $request)
    {
        $user = $this->getUser();
        $prepo = $this->getDoctrine()->getRepository('UserBundle:Post');
        $post = $prepo->findOneById($request->get('post'));
        if(sizeof($post) == 1)
        {
            $plrepo = $this->getDoctrine()->getRepository('UserBundle:PostLike');
            $item = $plrepo->findOneBy(
              array('post'=>$post,'user'=>$user)
            );
            if(sizeof($item) == 0)
            {
                $postlike = new PostLike();
                $postlike->setPost($post);
                $postlike->setUser($user);
                $em = $this->getDoctrine()->getManager();
                $em->persist($postlike);
                $em->flush();
                return new JsonResponse('Done');
            }
            else
                return new JsonResponse('ERR');
        }
        else
            return new JsonResponse('ERR');
    }

    /**
     * @Route("/linkpan/home/post_dislike",name="post_dislike")
     */
    public function post_dislikeAction(Request $request)
    {
        $user = $this->getUser();
        $prepo = $this->getDoctrine()->getRepository('UserBundle:Post');
        $post = $prepo->findOneById($request->get('post'));
        if(sizeof($post) == 1)
        {
            $plrepo = $this->getDoctrine()->getRepository('UserBundle:PostLike');
            $item = $plrepo->findOneBy(
                array('post'=>$post,'user'=>$user)
            );
            if(sizeof($item) == 1)
            {
                $em = $this->getDoctrine()->getManager();
                $em->remove($item);
                $em->flush();
                return new JsonResponse('Done');
            }
            else
                return new JsonResponse('ERR');
        }
        else
            return new JsonResponse('ERR');
    }

    /**
     * @Route("/linkpan/home/post_share",name="post_share")
     */
    public function post_shareAction(Request $request)
    {
        $user = $this->getUser();
        $prepo = $this->getDoctrine()->getRepository('UserBundle:Post');
        $post = $prepo->findOneById($request->get('post'));
        if(sizeof($post) == 1)
        {
            $psrepo = $this->getDoctrine()->getRepository('UserBundle:PostShare');
            $item = $psrepo->findOneBy(
                array('post'=>$post,'user'=>$user)
            );
            if(sizeof($item) == 0)
            {
                $postshare = new PostShare();
                $postshare->setUser($user);
                $postshare->setPost($post);
                $em = $this->getDoctrine()->getManager();
                $em->persist($postshare);
                $em->flush();
                return new JsonResponse('Done');
            }
            else
                return new JsonResponse('ERR');
        }
        else
            return new JsonResponse('ERR');
    }

    /**
     * @Route("/linkpan/home/post_comment",name="post_comment")
     */
    public function post_commentAction(Request $request)
    {
        $user = $this->getUser();
        $prepo = $this->getDoctrine()->getRepository('UserBundle:Post');
        $post = $prepo->findOneById($request->get('post'));
        if(sizeof($post) == 1)
        {
            $pc = new PostComment();
            $pc->setComment($request->get('comment'));
            $pc->setPost($post);
            $pc->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($pc);
            $em->flush();
            return new JsonResponse('Done');
        }
        else
            return new JsonResponse('ERR');
    }
}
