<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
	/**
     * @Route("/", name="dashboard")
     *
     * @param Request $request Request
     *
     * @return Response
     */
    public function dashboardAction()
    {
        return $this->render('WarehouseBundle:Default:dashboard.html.twig');
    }

}
