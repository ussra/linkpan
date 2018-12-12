<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class TranslationController extends Controller
{
    /**
     * @Route("/linkpan/home/translation",name="translation")
     */
    public function translationAction(Request $request)
    {
        //$request->set('_local',$request->get('_local'));
        return $this->forward('UserBundle:Home:index');
    }
}
