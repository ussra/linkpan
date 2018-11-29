<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use UserBundle\Entity\Complaint;
use UserBundle\Entity\ComplaintReview;

class fccController extends Controller
{

    /**
     * @Route("{_locale}/linkpan/complaints",name="complaints")
     */
    public function complaintsAction()
    {
        // GET ALL COMPLAINTS
        $repo = $this->getDoctrine()->getRepository('UserBundle:Complaint');
        $complaints = $repo->findAll();
        $session = new Session();
        $session->set('complaints',$complaints);
        //
        return $this->render('UserBundle::complaints.html.twig');
    }


    /**
     * @Route("{_locale}/linkpan/complaints/add_complaint",name="add_complaint")
     */
    public function add_complaintAction()
    {
        $data = array();
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT u
            FROM AppBundle:User u
            WHERE u.id != :user
            ORDER BY u.lastname'
        )->setParameter('user', $currentUser->getId());
        if(!is_null($query))
        {
            $users = $query->getResult();
            foreach ($users as $item)
            {
                $temp = array(
                    'id'=>$item->getId(),
                    'firstname'=>$item->getFirstname(),
                    'lastname'=>$item->getLastname()
                );
                array_push($data,$temp);
            }
        }
        $session = new Session();
        $session->set('all_users',$data);
        return $this->render('UserBundle::complaintForm.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/complaints/search",name="search_complaints")
     */
    public function searchcomplaintsAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Complaint');
        $result = $repo->createQueryBuilder('cmp')
            ->where("
                IDENTITY(cmp.userToComplaint) IN 
                    (SELECT u.id FROM AppBundle:User u WHERE 
                    CONCAT(u.firstname ,' ', u.lastname) LIKE :filter)
            ")

            ->setParameter('filter', '%' . $request->get('filtercomplaint') . '%')
            ->getQuery()
            ->getResult();
        $session = new Session();
        $session->set('complaint_filter_text',$request->get('filtercomplaint'));
        $session->set('complaints',$result);
        return $this->render('UserBundle::complaints.html.twig');
    }

    /**
     * @Route("{_locale}/linkpan/complaints/save_complaint",name="save_complaint")
     */
    public function savecomplaintAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $userToComplaint = $repo->findOneById($request->get('userlist'));
        $frepo = $this->getDoctrine()->getRepository('UserBundle:Complaint');
        $fcc = $frepo->findOneBy(
          array('user'=>$this->getUser(),'userToComplaint'=>$userToComplaint)
        );
        if(!is_null($fcc))
            echo '<script language="javascript">alert("You cannot complaint same user twice , if there s any addition can u please update the last complaint you publish about this user , Thanks")</script>';
        else
        {
            $complaint = new Complaint();
            if(!is_null($userToComplaint))
                $complaint->setUserToComplaint($userToComplaint);

            $complaint->setTitle($request->get('title'));
            $complaint->setCause($request->get('cause'));
            $complaint->setTransaction($request->get('amount'));
            if (!is_null($request->files->get('file')))
            {
                $dir = __DIR__ . "/../../../web/attachements/fcc";
                if (!file_exists($dir))
                    mkdir($dir, 0777, true);

                $file = $request->files->get('file');
                $files = scandir($dir);
                $count = count($files)-2;
                $name = $count.'_'.$file->getClientOriginalName();
                $file->move($dir,$name );
                $complaint->setFile($name);
            }
            $complaint->setDescription($request->get('description'));
            $complaint->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($complaint);
            $em->flush();

            echo '<script language="javascript">alert("Complaint Saved ! Thank you")</script>';
        }

        return $this->forward('UserBundle:fcc:complaints');
    }

    /**
     * @Route("{_locale}/linkpan/complaints/complaint_detail",name="complaint_detail")
     */
    public function complaintdetailAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Complaint');
        $complaint = $repo->findOneById($request->get('complaint'));
        if(!is_null($complaint))
        {
            $session = new Session();
            $session->set('complaint_detail',$complaint);
            // get complaint reviews
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
              'SELECT rv FROM UserBundle:ComplaintReview rv WHERE 
                IDENTITY(rv.complaint) = :complaint '
            )->setParameter('complaint', $complaint->getId());
            $session->set('complaint_reviews',$query->getResult());
            //
            return $this->render('UserBundle::complaintDetail.html.twig');
        }
        else
        {
            echo '<script language="javascript">alert("You cannot access this complaint, please retry again ")</script>';
            return $this->forward('UserBundle:fcc:complaints');
        }
    }

    /**
     * @Route("{_locale}/linkpan/complaints/complaint_detail/complaint_comment",name="complaint_comment")
     */
    public function complaintcommentAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Complaint');
        $complaint =  $repo->findOneById($request->get('complaint'));
        if(is_null($complaint))
            return new JsonResponse('ERR');
        else
        {
            $complaintReview = new ComplaintReview();
            $complaintReview->setUser($this->getUser());
            $complaintReview->setComplaint($complaint);
            $complaintReview->setReview($request->get('comment'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($complaintReview);
            $em->flush();
            return new JsonResponse('Done');
        }
    }

    /**
     * @Route("{_locale}/linkpan/complaints/complaint_detail/delete_complaint",name="delete_complaint")
     */
    public function deletecomplaintAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('UserBundle:Complaint');
        $complaint =  $repo->findOneById($request->get('complaint'));
        if(!is_null($complaint))
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($complaint);
            $em->flush();
            return $this->forward('UserBundle:fcc:complaints');
        }
        else
        {
            echo '<script language="javascript">alert("You cannot delete this complaint now , please try again");this.reload();</script>';
        }
    }
}
