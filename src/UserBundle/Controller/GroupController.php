<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Groupe;

class GroupController extends Controller
{
    /**
     * @Route("/linkpan/groups/create_group",name="create_group")
     */
    public function create_groupAction(Request $request)
    {
        if(!is_null($request->get('pv')))
        {
            $group = new Groupe();
            //
            $imagdir = __DIR__ . "/../../../web/images/group";
            $image = $request->files->get('groupimage');
            $files = scandir($imagdir);
            $count = count($files)-2;
            $name = $count.'_'.$image->getClientOriginalName();
            $image->move($imagdir,$name );
            $group->setImage($name);
            //
            $group->setName($request->get('groupname'));
            $group->setDescription($request->get('groupdescription'));
            $group->setPrivacy($request->get('pv'));
            $date = date("m/d/Y h:i:s ", time());
            $group->setCreationDate($date);
            $group->setUser($this->getUser());
            //save the group
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();
            //
            echo '<script language="javascript">alert("Group inserted !")</script>';
        }
        else
            echo '<script language="javascript">alert("informations not completed !")</script>';

        return $this->forward('UserBundle:Home:groups');
    }


    /**
     * @Route("/linkpan/groups/remove_group",name="remove_group")
     */
    public function remove_groupAction(Request $request)
    {
        $currentUser = $this->getUser();
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $grp = $repo->findOneById($request->get('group'));
        if(sizeof($grp) == 1)
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($grp);
            $em->flush();
            echo '<script language="javascript">alert("Group Removed !")</script>';
        }
        else
            echo '<script language="javascript">alert("we cannot remove this group now , please retry again!")</script>';

        return $this->forward('UserBundle:Home:groups');
    }




    /**
     * @Route("/linkpan/groups/view_group",name="view_group")
     */
    public function view_groupAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $grp = $repo->findOneById($request->get('group'));

        if(sizeof($grp) == 1)
        {
            $currentUser = $this->getUser();
            //Get group owner info
            $userrepo = $this->getDoctrine()->getRepository('AppBundle:User');
            $group_owner = $userrepo->findOneById($grp->getUser());
            //Get group memebers
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                'SELECT  IDENTITY(m.user) 
                    FROM UserBundle:GroupJoin m
                    WHERE m.group = :grp
                    AND IDENTITY(m.user) NOT IN (
                      SELECT IDENTITY(b.userToBlock)  FROM UserBundle:Block b 
                      WHERE IDENTITY(b.user) = :currentUser
                    )
                    '
            )->setParameter('grp', $grp->getId())
                ->setParameter('currentUser', $currentUser->getId());
            $result =  $query->getResult();

            $members = array();
            if(!is_null($result))
            {
                foreach ($result as $item)
                {
                    $user = $userrepo->findOneById($item);
                    $member = $userrepo->findOneById($user);
                    $temp = array(
                       'member_id'=>$member->getId(),
                       'member_first_name'=>$member->getFirstname(),
                       'member_last_name'=>$member->getLastname()
                    );
                    array_push($members,$temp);
                }
            }
            //get posts
            $posts = array();
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                'SELECT DISTINCT p
                FROM UserBundle:GroupPost p
                WHERE p.group = :group
            '
            )->setParameter('group', $grp);
            $result =  $query->getResult();
            if(sizeof($result)>0)
            {
                $imagesrepo = $this->getDoctrine()->getRepository('UserBundle:GroupPostImage');
                foreach ($result as $post)
                {
                    $owner = $userrepo->findOneById($post->getUser());
                    //Get images
                    $result = $imagesrepo->findBy(
                        array('grouppost'=>$post)
                    );
                    $images = array();
                    if(!empty($result))
                    {
                        foreach ($result as $img)
                        {
                            array_push($images,$img->getImage());
                        }
                    }

                    $temp = array(
                        'id'=>$post->getId(),
                        'owner_id'=>$owner->getId(),
                        'owner_first_name'=>$owner->getFirstname(),
                        'owner_last_name'=>$owner->getLastname(),
                        'owner_company_name'=>$owner->getCompanyName(),
                        'owner_adress'=>$owner->getAdress(),
                        'owner_image'=>$owner->getImage(),
                        'post_creation_date'=>$post->getCreationDate(),
                        'post_content'=>$post->getContent(),
                        'post_video'=>$post->getVideo(),
                        'post_images'=>$images
                    );
                    array_push($posts,$temp);
                }
            }
            //
            $temp = array(
              'group_id'=>$grp->getId(),
              'group_name'=>$grp->getName(),
              'group_image'=>$grp->getImage(),
              'group_description'=>$grp->getDescription(),
              'group_privacy'=>$grp->isPrivacy(),
              'group_creation_date'=>$grp->getCreationDate(),
              'group_owner_id'=>$group_owner->getId(),
              'group_owner_first_name'=>$group_owner->getFirstname(),
              'group_owner_last_name'=>$group_owner->getLastname(),
              'group_members'=>$members,
              'group_posts'=>$posts
            );
            $session = new Session();
            $session->set('group_view_data',$temp);
            return $this->render('UserBundle::viewgroup.html.twig');
        }
        else
        {
            echo '<script language="javascript">alert("Please try again !")</script>';
            return $this->forward('UserBundle:Home:groups');
        }

    }

}
