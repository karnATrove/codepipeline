<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WarehouseBundle\Manager\BookingManager;
use WarehouseBundle\Workflow\DashboardWorkflow;

class DashboardController extends Controller
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
        $bookingWorkflow = $this->get(DashboardWorkflow::class);
        $dashboardValues = [];
        $dashboardValues['countBookingsCreatedToday'] = $bookingWorkflow->countBookingsCreatedToday();
        $dashboardValues['countBookingsShippedToday'] = $bookingWorkflow->countBookingsShippedToday();
        $dashboardValues['countStockedProducts'] = $bookingWorkflow->countStockedProducts();
        $dashboardValues['countPickedBookingProductsToday'] = $bookingWorkflow->countPickedBookingProductsToday();
        $dashboardValues['countBookingsCreatedDaily'] = $bookingWorkflow->countBookingsCreatedDaily();
        return $this->render('WarehouseBundle:Dashboard:dashboard.html.twig', $dashboardValues);
    }

}
