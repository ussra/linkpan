<?php

namespace UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Groupe;
use Symfony\Component\HttpFoundation\Session\Session;
use UserBundle\Entity\GroupJoin;
use UserBundle\Entity\GroupPost;
use UserBundle\Entity\GroupPostImage;
use UserBundle\Entity\GroupRequest;

class GroupController extends Controller
{
    function groupsManage($currentUser)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $groups = $repo->findBy(
          array('user'=>$currentUser)
        );
        return $groups;
    }

    function pendingInvites($currentUser)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            '
                SELECT gr FROM UserBundle:GroupRequest gr 
                WHERE IDENTITY(gr.group) IN (
                  SELECT g.id FROM UserBundle:Groupe g WHERE 
                  IDENTITY(g.user) = :currentUser 
                )
            '
        )->setParameter('currentUser', $currentUser->getId());
        return $query->getResult();
    }

    function yourGroups($currentUser)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:GroupJoin');
        return $repo->findBy(
          array('user'=>$currentUser)
        );
    }
    /**
     * @Route("{_locale}/linkpan/groups",name="groups")
     */
    public function groupsAction()
    {
        $session = new Session();
        $currentUser = $this->getUser();
        //Get groups that user manage
        $groupsManage = $this->groupsManage($currentUser);
        $session->set('GroupsManage',$groupsManage);
        //Get pending invites
        $session->set('pendingInvites',$this->pendingInvites($currentUser));
        //Group Join
        $session->set('yourgroups',$this->yourGroups($currentUser));

        return $this->render('UserBundle::groups.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/new_group",name="new_group")
     */
    public function newgroupAction(Request $request)
    {
        $currentUser = $this->getUser();
        if(!is_null($request->files->get('groupimage')))
        {
           $group = new Groupe();
           $group->setUser($currentUser);
            // upload  image
            $attdir = __DIR__ . "/../../../web/images/group";
            if (!file_exists($attdir))
                mkdir($attdir, 0777, true);
            $image = $request->files->get('groupimage');
            $files = scandir($attdir);
            $count = count($files)-2;
            $name = $count.'_'.$image->getClientOriginalName();
            $image->move($attdir,$name );
            //
            $group->setImage($name);
            $group->setName($request->get('groupname'));
            $group->setDescription($request->get('groupdescription'));
            $group->setPrivacy($request->get('acc'));
            $date = date("m/d/Y h:i:s ", time());
            $group->setCreationDate($date);
            $em =$this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();
            echo '<script language="javascript">alert("Group is created")</script>';
        }
        else
            echo '<script language="javascript">alert("failed operation , please try again")</script>';

        return $this->forward('UserBundle:Group:groups');
    }

    /**
     * @Route("{_locale}/linkpan/groups/delete_group",name="delete_group")
     */
    public function deletegroupAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $group = $repo->findOneById($request->get('group'));
        if(!is_null($group))
        {
            $message = $group->getName().' is deleted';
            $em = $this->getDoctrine()->getManager();
            $em->remove($group);
            $em->flush();
            echo '<script language="javascript">alert("'.$message.'")</script>';
        }
        else
            echo '<script language="javascript">alert("You cannot delete this this group now , please retry again !")</script>';
        return $this->forward('UserBundle:Group:groups');
    }

    /**
     * @Route("{_locale}/linkpan/groups/filter_groups",name="filter_groups")
     */
    public function filtergroupsAction(Request $request)
    {
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            '
                SELECT DISTINCT g FROM UserBundle:Groupe g WHERE g.name LIKE :filter
                AND IDENTITY(g.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                )
                AND IDENTITY(g.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
            '
        )->setParameter('user', $currentUser)
            ->setParameter('filter', '%'.$request->get('filtergroup').'%');
        $result = $query->getResult();
        // result
        $groups = array();
        $repo = $this->getDoctrine()->getRepository('UserBundle:GroupJoin');
        $reqrepo = $this->getDoctrine()->getRepository('UserBundle:GroupRequest');
        foreach ($result as $group )
        {
            if($group->getUser() != $currentUser )
            {
                $gj = $repo->findOneBy(
                  array('user'=>$currentUser,'group'=>$group)
                );
                if(!is_null($gj))
                    $joinType = 'join';
                else
                {
                    if($group->getPrivacy() == 'private')
                    {
                        $grequest = $reqrepo->findOneBy(
                          array('user'=>$currentUser,'group'=>$group)
                        );

                        if(!is_null($grequest))
                            $joinType = 'Requested';
                        else
                            $joinType = 'Request';
                    }

                    if($group->getPrivacy() == 'public')
                        $joinType = 'Request';
                }
            }
            else
                $joinType = 'owner';


            $temp = array(
                'group'=>$group,
                'joinType'=>$joinType
            );
            array_push($groups,$temp);
        }
        //

        $session = new Session();
        $session->set('filter_text',$request->get('filtergroup'));
        $session->set('groups_filter',array_reverse($groups));
        return $this->forward('UserBundle:Group:groups');
    }

    /**
     * @Route("{_locale}/linkpan/groups/join_group",name="join_group")
     */
    public function joingroupAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $group = $repo->findOneById($request->get('group'));
        if(!is_null($group))
        {
            $em = $this->getDoctrine()->getManager();
            $currentUser = $this->getUser();
            if($group->getPrivacy() == 'private')
            {
                //check if exist
                $repo = $this->getDoctrine()->getRepository('UserBundle:GroupRequest');
                $check_gr = $repo->findOneBy(
                    array('user'=>$currentUser,'group'=>$group)
                );
                if(is_null($check_gr))
                {
                    //
                    $gr = new GroupRequest();
                    $gr->setGroup($group);
                    $gr->setUser($currentUser);
                    $em->persist($gr);
                    $em->flush();
                }

            }
            if($group->getPrivacy() == 'public')
            {
                // check if exist
                $repo = $this->getDoctrine()->getRepository('UserBundle:GroupJoin');
                $check_gj = $repo->findOneBy(
                    array('user'=>$currentUser,'group'=>$group)
                );
                if(is_null($check_gj))
                {
                    $gj = new GroupJoin();
                    $gj->setUser($currentUser);
                    $gj->setGroup($group);
                    $em->persist($gj);
                    $em->flush();
                }

            }

        }
        $session = new Session();
        $filter = $session->get('filter_text');
        return $this->forward('UserBundle:Group:filtergroups',array('filtergroup'=>$filter));
    }

    /**
     * @Route("{_locale}/linkpan/groups/exit_group",name="exit_group")
     */
    public function exitgroupAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $group = $repo->findOneByid($request->get('group'));

        if(!is_null($group))
        {
            $repo = $this->getDoctrine()->getRepository('UserBundle:GroupJoin');
            $gj = $repo->findOneBy(
              array('user'=>$this->getUser(),'group'=>$group)
            );

            if(!is_null($gj))
            {
                $em = $this->getDoctrine()->getManager();
                $em->remove($gj);
                $em->flush();
            }
        }
        $session = new Session();
        $filter = $session->get('filter_text');
        return $this->forward('UserBundle:Group:filtergroups',array('filtergroup'=>$filter));
    }


    /**
     * @Route("{_locale}/linkpan/groups/cancel_request_group",name="cancel_request_group")
     */
    public function cancelrequestgroupAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $group = $repo->findOneByid($request->get('group'));
        if(!is_null($group))
        {
            $repo = $this->getDoctrine()->getRepository('UserBundle:GroupRequest');
            $gr  = $repo->findOneBy(
              array('user'=>$this->getUser(),'group'=>$group)
            );
            if(!is_null($gr))
            {
                $em = $this->getDoctrine()->getManager();
                $em->remove($gr);
                $em->flush();
            }
        }

        $session = new Session();
        $filter = $session->get('filter_text');
        return $this->forward('UserBundle:Group:filtergroups',array('filtergroup'=>$filter));
    }



    private function getPostsData($result)
    {
        $posts = array();
        $imagesrepo = $this->getDoctrine()->getRepository('UserBundle:GroupPostImage');
        $userrepo = $this->getDoctrine()->getRepository('AppBundle:User');
        foreach ($result as $post)
        {
            // find images
            $result = $imagesrepo->findBy(
                array('groupPost'=>$post)
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
              'images'=>$images ,
              'owner'=>$post->getUser(),
              'post'=>$post,

            );
            array_push($posts,$temp);
        }
        return $posts;
    }
    /**
     * @Route("{_locale}/linkpan/groups/view_group",name="view_group")
     */
    public function viewgroupAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $group = $repo->findOneById($request->get('group'));
        if(!is_null($group))
        {
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            // GET  MEMBERS
            $query = $em->createQuery(
                '
                  SELECT gj FROM UserBundle:GroupJoin gj where gj.group = :groupe
                '
            )->setParameter('groupe', $group);
            $memebers = $query->getResult();
            //GET POSTS
            $query = $em->createQuery(
                'SELECT DISTINCT p FROM UserBundle:GroupPost p
                 WHERE p.group = :groupe
                  AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b.userToBlock) FROM UserBundle:Block b WHERE b.user = :user
                  )
                AND IDENTITY(p.user) NOT IN (
                  SELECT IDENTITY(b2.user) FROM UserBundle:Block b2 WHERE b2.userToBlock = :user
                ) 
                ORDER BY p.id DESC
                '
            )->setParameter('groupe', $group)
                ->setParameter('user', $currentUser);
            $result =  $query->setMaxResults(5)->getResult();
            $posts = $this->getPostsData($result);
            //
            $temp = array(
              'group'=>$group,
              'members'=>array_reverse($memebers),
              'posts'=>$posts
            );

            $session = new Session();
            $session->set('view_group',$temp);
            return $this->render('UserBundle::group.html.twig');
        }
        else
        {
            echo '<script language="javascript">alert("Sorry , you cannot access this group now , please retry again !")</script>';
            return $this->forward('UserBundle:Group:groups');
        }

    }


    /**
     * @Route("{_locale}/linkpan/groups/view_group/save_post",name="save_post")
     */
    public function savepostAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
        $group = $repo->findOneById($request->get('group'));

        if(!is_null($group))
        {
            $groupPost = new GroupPost();
            $groupPost->setContent($request->get('content'));
            $date = date("m/d/Y h:i:s ", time());
            $groupPost->setCreationDate($date);
            $groupPost->setUser($this->getUser());
            if (!is_null($request->files->get('post_video')))
            {
                $viddir = __DIR__ . "/../../../web/videos";
                $video = $request->files->get('post_video');
                $files = scandir($viddir);
                $count = count($files)-2;
                $name = $count.'_'.$video->getClientOriginalName();
                $video->move($viddir,$name );
                $groupPost->setVideo($name);
            }
            $groupPost->setGroup($group);
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupPost);
            $em->flush();
            $images = $request->files->get('uploadimage');
            if(!is_null($images[0]))
            {
                $imgdir = __DIR__ . "/../../../web/images/post";
                foreach ($images as $img)
                {
                    $gpi = new GroupPostImage();
                    $gpi->setGroupPost($groupPost);
                    $files = scandir($imgdir);
                    $count = count($files)-2;
                    $name = $count.'_'.$img->getClientOriginalName();
                    $img->move($imgdir,$name );
                    $gpi->setImage($name);
                    $em->persist($gpi);
                    $em->flush();
                }
            }
        }
        else
            echo '<script language="javascript">alert("Sorry , you cannot post in this group now , can you try again")</script>';


        return $this->forward('UserBundle:Group:viewgroup',array('group'=>$request->get('group')));
    }


    /**
     * @Route("{_locale}/linkpan/groups/view_group/remove_member",name="remove_member")
     */
    public function remove_memberAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $repo->findOneById($request->get('user'));
        if(!is_null($user))
        {
            $repo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
            $group = $repo->findOneById($request->get('group'));
            if(!is_null($group))
            {
                $repo = $this->getDoctrine()->getRepository('UserBundle:GroupJoin');
                $gj = $repo->findOneBy(
                  array('user'=>$user,'group'=>$group)
                );
                $em = $this->getDoctrine()->getManager();
                $em->remove($gj);
                $em->flush();
            }
        }

        return new JsonResponse('Done');
    }


    /**
     * @Route("{_locale}/linkpan/groups/view_group/delete_post",name="delete_post")
     */
    public function delete_postAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:GroupPost');
        $groupPost = $repo->findOneById($request->get('post'));
        if(!is_null($groupPost))
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($groupPost);
            $em->flush();
        }
        return $this->forward('UserBundle:Group:viewgroup',array('group'=>$request->get('group')));
    }

    /**
     * @Route("{_locale}/linkpan/groups/clear",name="clear")
     */
    public function clearAction(Request $request)
    {
        $session = new Session();
        $session->set('filter_text',null);
        $session->set('groups_filter',null);
        return $this->forward('UserBundle:Group:groups');
    }

    /**
     * @Route("{_locale}/linkpan/groups/invitations/accept",name="accept_invitation")
     */
    public function acceptInvitationAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:GroupRequest');
        $request = $repo->findOneById($request->get('invitation'));
        if(!is_null($request))
        {
            $userrepo = $this->getDoctrine()->getRepository('AppBundle:User');
            $user = $userrepo->findOneById($request->getUser());
            $grprepo = $this->getDoctrine()->getRepository('UserBundle:Groupe');
            $group = $grprepo->findOneById($request->getGroup());
            if(!is_null($user) && !is_null($grprepo)){
                $em = $this->getDoctrine()->getManager();
                $gj = new GroupJoin();
                $gj->setUser($user);
                $gj->setGroup($group);
                $em->persist($gj);
                $em->remove($request);
                $em->flush();
            }
            else
                echo '<script language="javascript">alert("You cannot Accept this request now!")</script>';
        }
        else
            echo '<script language="javascript">alert("You cannot Accept this request now!")</script>';

        return $this->forward('UserBundle:Group:groups');
    }


    /**
     * @Route("{_locale}/linkpan/groups/invitations/decline",name="decline_invitation")
     */
    public function declineInvitationAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:GroupRequest');
        $request = $repo->findOneById($request->get('invitation'));
        if(!is_null($request))
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($request);
            $em->flush();
        }
        else
            echo '<script language="javascript">alert("You cannot Decline this request now!")</script>';

        return $this->forward('UserBundle:Group:groups');
    }
}
