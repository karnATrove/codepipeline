<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WarehouseBundle\Manager\BookingManager;

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
        $bookingWorkflow = $this->get('warehouse.workflow.default_workflow');
        $dashboardValues = [];
        $dashboardValues['countBookingsCreatedToday'] = $bookingWorkflow->countBookingsCreatedToday();
        $dashboardValues['countBookingsShippedToday'] = $bookingWorkflow->countBookingsShippedToday();
        $dashboardValues['countStockedProducts'] = $bookingWorkflow->countStockedProducts();
        $dashboardValues['countPickedProductsToday'] = $bookingWorkflow->countPickedProductsToday();
        return $this->render('WarehouseBundle:Default:dashboard.html.twig', $dashboardValues);
    }

}
