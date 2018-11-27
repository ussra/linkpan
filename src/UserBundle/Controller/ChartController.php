<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ChartController extends Controller
{
    /**
     * @Route("/linkpan/charts",name="charts")
     */
    public function followAction(Request $request)
    {
        return $this->render('UserBundle::charts.html.twig');
    }
}
