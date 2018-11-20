<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Pan;
use DateTime;
class PanController extends Controller
{
    /**
     * @Route("/linkpan/save_pan",name="save_pan")
     */
    public function save_panAction(Request $request)
    {
        if(!is_null($request->files->get('uploadimage')))
        {
            $pan = new Pan();
            //v1
            $pan->setName($request->get('productname'));
            $pan->setOthername($request->get('othername'));
            $pan->setOrigin($request->get('origin'));
            if(!is_null($request->get('pantype')))
                $pan->setType($request->get('pantype'));
            $pan->setAvailability($request->get('av'));
            $pan->setAccess($request->get('acc'));
            $pan->setCategory($request->get('category'));
            $pan->setQuentity($request->get('quantity'));
            $pan->setPrice($request->get('price'));
            $pan->setQuantityType($request->get('qtType'));
            // v2
            $pan->setDescription($request->get('description'));
            if(!is_null($request->files->get('uploadattachement')))
            {
                $attdir = __DIR__ . "/../../../web/attachements/pans";
                $attachement = $request->files->get('uploadattachement');
                $files = scandir($attdir);
                $count = count($files)-2;
                $name = $count.'_'.$attachement->getClientOriginalName();
                $attachement->move($attdir,$name );
                $pan->setAttachement($name);
            }

            $imagdir = __DIR__ . "/../../../web/images/pans";
            $image = $request->files->get('uploadimage');
            $files = scandir($imagdir);
            $count = count($files)-2;
            $name = $count.'_'.$image->getClientOriginalName();
            $image->move($imagdir,$name );
            $pan->setImage($name);
            if(is_null($request->get('pantype')))
                $pan->setType('Discover');
            else
                $pan->setType($request->get('pantype'));

            $date = date("m/d/Y h:i:s ", time());
            $pan->setCreationDate($date);
            $pan->setUser($this->getUser());
            //
            $em = $this->getDoctrine()->getManager();
            $em->persist($pan);
            $em->flush();
            echo '<script language="javascript">alert("Pan saved !")</script>';
            return $this->forward('UserBundle:Home:pans');
        }
        else
        {
            echo '<script language="javascript">alert("Please you need to set the image of the pan")</script>';
            return $this->forward('UserBundle:Home:pans');
        }
    }


    /**
     * @Route("/linkpan/remove_pan",name="remove_pan")
     */
    public function remove_panAction(Request $request)
    {
        $selected = $request->get('selected');
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Pan::class);
        foreach ( $selected as $value)
        {
            $pan = $repo->findOneById($value);
            if(!is_null($pan))
            {
                $em->remove($pan);
                $em->flush();
            }
        }
        return new JsonResponse('Done');
    }




}
