<?php

namespace ReportBundle\Controller;

use ReportBundle\Utils\BuildDirectReport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BuildDirectReportController extends Controller
{
	/**
	 *
	 * @Route("/bdReport", name="report_bd_report")
	 */
	public function indexAction()
	{
		return $this->render('ReportBundle:BuildDirectReport:build_direct_report.html.twig', [
		]);
	}

	/**
	 *
	 * @Route("/ajaxReportContent", name="report_ajax_bd_report_content")
	 */
	public function ajaxReportContent(Request $request)
	{
		$start = $request->get('start');
		$end = $request->get('end');
		$end = date('Y-m-d', strtotime("{$end} +1 days"));
		$OFCount = $this->get('report.build_direct_report')->getOceanFrightContainerCount($start, $end);
		$orderShippedCount = $this->get('report.build_direct_report')->getOrdersChangedToShip($start, $end);
		$pickupOrderShippedCount = $this->get('report.build_direct_report')->getPickUpOrdersChangedToShip($start, $end);
		$cartonsOrderChangedToShip = $this->get('report.build_direct_report')->getCartonsOrdersChangedToShip($start, $end);

		$response['ajaxCommand'][] = array(
			'selector' => '#report_content',
			'op' => 'html',
			'value' => $this->renderView('ReportBundle:BuildDirectReport:build_direct_report_content.html.twig', [
				'ofCount' => $OFCount,
				'orderShippedCount' => $orderShippedCount,
				'pickupOrderShippedCount' => $pickupOrderShippedCount,
				'cartonsOrderChangedToShip' => $cartonsOrderChangedToShip,
			]),
		);
		return new JsonResponse($response, 200);
	}
}
