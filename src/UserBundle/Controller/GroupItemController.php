<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\GroupPost;
use UserBundle\Entity\GroupPostImage;

class GroupItemController extends Controller
{
    /**
     * @Route("/linkpan/groups/group_post",name="group_post")
     */
    public function group_postAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $group = $repo->findOneById($request->get('group'));
        if(!is_null($group))
        {
            $gPost = new GroupPost();
            $gPost->setUser($this->getUser());
            $gPost->setGroup($group);
            $gPost->setContent($request->get('post_description'));
            $date = date("m/d/Y h:i:s ", time());
            $gPost->setCreationDate($date);
            //set video
            if (!is_null($request->files->get('post_video')))
            {
                $viddir = __DIR__ . "/../../../web/videos";
                $video = $request->files->get('post_video');
                $files = scandir($viddir);
                $count = count($files)-2;
                $name = $count.'_'.$video->getClientOriginalName();
                $video->move($viddir,$name );
                $gPost->setVideo($name);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($gPost);
            $em->flush();

            // set images
            $images = $request->files->get('uploadimage');
            if(!is_null($images[0]))
            {
                $imgdir = __DIR__ . "/../../../web/images/postgroup";
                foreach ($images as $img)
                {
                    $pgImage = new GroupPostImage();
                    $pgImage->setGrouppost($gPost);
                    $files = scandir($imgdir);
                    $count = count($files)-2;
                    $name = $count.'_'.$img->getClientOriginalName();
                    $img->move($imgdir,$name );
                    $pgImage->setImage($name);
                    $em->persist($pgImage);
                    $em->flush();
                }
            }
            //
        }
        return $this->render('UserBundle::viewgroup.html.twig');
    }


    /**
     * @Route("/linkpan/groups/remove_member",name="remove_member")
     */
    public function remove_memberAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $member = $repo->findOneById($request->get('member'));
        if(!is_null($member))
        {
            $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
            $grp = $repo->findOneById($request->get('group'));
            if(!is_null($grp))
            {
                $repo = $this->getDoctrine()->getRepository('UserBundle:GroupJoin');
                $grpj = $repo->findOneBy(
                    array('user'=>$member,'group'=>$grp)
                );
                if(!is_null($grpj))
                {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($grpj);
                    $em->flush();
                    return new JsonResponse('Done');
                }
            }
            else
                return new JsonResponse('ERR');
        }
        else
            return new JsonResponse('ERR');


    }
}
