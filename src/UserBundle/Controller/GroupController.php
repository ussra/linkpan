<?php

namespace UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Groupe;
use Symfony\Component\HttpFoundation\Session\Session;
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
        //
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
}
