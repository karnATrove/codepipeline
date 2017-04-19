<?php

namespace ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BuildDirectReportController extends Controller
{
	/**
	 *
	 * @Route("/bdReport", name="report_bd_report")
	 */
	public function indexAction()
	{
		return $this->render('ReportBundle:BuildDirectReport:build_direct_report.html.twig');
	}
}
