<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="frontpage", methods={"GET"})
     */
    public function indexAction()
    {
        return $this->render('default/frontpage.html.twig');
    }
}
