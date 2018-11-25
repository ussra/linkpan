<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class fccController extends Controller
{
    /**
     * @Route("{_locale}/linkpan/complaints/add_complaint",name="add_complaint")
     */
    public function add_complaintAction()
    {
        return $this->render('UserBundle::complaintForm.html.twig');
    }
}
