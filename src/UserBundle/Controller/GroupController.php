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
}
