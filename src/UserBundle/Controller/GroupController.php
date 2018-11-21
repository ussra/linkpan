<?php

namespace UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Groupe;

class GroupController extends Controller
{
    /**
     * @Route("{_locale}/linkpan/groups",name="groups")
     */
    public function groupsAction()
    {
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
           /** $group = new Groupe();
            $group->setUser($currentUser);
            // upload  image
            $attdir = __DIR__ . "/../../../web/images/group";
            $image = $request->files->get('groupimage');
            $files = scandir($attdir);
            $count = count($files)-2;
            $name = $count.'_'.$image->getClientOriginalName();
            $image->move($attdir,$name );
            //
            $group->setImage($name);**/
        }
        return new JsonResponse('');
    }
}
