<?php

namespace WarehouseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WarehouseBundle\DTO\AjaxResponse\AjaxCommandDTO;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Exception\WorkflowException\WorkflowAPIException;
use WarehouseBundle\Manager\IncomingProductScanManager;
use WarehouseBundle\Manager\LocationManager;
use WarehouseBundle\Utils\AjaxCommandParser;
use WarehouseBundle\Utils\MessagePrinter;
use WarehouseBundle\Workflow\IncomingProductScanWorkflow;

/**
 * Booking controller.
 *
 * @Route("/incoming_product_scan")
 */
class IncomingProductScanController extends Controller
{
	/**
	 * Lists all Incoming entity products.
	 *
	 * @Route("/{id}/products_scanned", name="incoming_products_scanned")
	 * @Method("GET")
	 */
	public function incomingProductsScannedAction(Incoming $incoming)
	{
		$scannedProducts = $this->get(IncomingProductScanManager::class)
			->getByIncoming($incoming);
		$locations = $this->get(LocationManager::class)->getLocations();
		$locationDropdownList = LocationManager::toArray($locations);
		return $this->render('WarehouseBundle::Incoming/products_scanned.html.twig', [
			'incoming' => $incoming,
			'scannedProducts' => $scannedProducts,
			'locationDropdownList' => $locationDropdownList,
		]);
	}

	/**
	 * Lists all Incoming entity products.
	 *
	 * @Route("/{id}/ajax/edit", name="incoming_product_scan_edit_ajax")
	 */
	public function incomingProductScanEditAjaxAction(Request $request, IncomingProductScan $incomingProductScan)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX.");
			return $this->redirect($this->generateUrl('incoming_products_scanned',
				['id' => $incomingProductScan->getIncoming()]));
		}

		try {
			$qty = $request->get('quantity');
			$locationId = $request->get('location');
			$ajaxCommands = $this->container->get(IncomingProductScanWorkflow::class)
				->edit($incomingProductScan, $qty, $locationId);
		} catch (\Exception $exception) {
			$messages['error'][] = "Update failed. Please refresh page and try again. 
			Error detail: {$exception->getMessage()}";
			$this->get(MessagePrinter::class)->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get(IncomingProductScanWorkflow::class)
					->getMessageBagView());
		}

		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}

	/**
	 * Ajax deletion of an IncomingProductScan item.
	 *
	 * @Route("/{id}/ajax/delete", name="incoming_products_scanned_delete_ajax")
	 */
	public function incomingProductsScannedDeleteAjaxAction(IncomingProductScan $incomingProductScan)
	{
		$ajaxCommands = $this->get(IncomingProductScanWorkflow::class)->delete($incomingProductScan);
		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}


	/**
	 * @Route("/{id}/render-product-scanned-row", name="incoming_products_scanned_render_row")
	 */
	public function renderProductScannedRowsAction(Incoming $incoming)
	{
		return new Response($this->get(IncomingProductScanWorkflow::class)
			->getScannedProductTableView($incoming));
	}

	/**
	 * Lists all Incoming entity products.
	 *
	 * @Route("/{id}/ajax/new-scan", name="incoming_products_scanned_new_ajax")
	 */
	public function incomingProductsScannedNewAjaxAction(Request $request, Incoming $incoming)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX.");
			return $this->redirect($this->generateUrl('incoming_products_scanned',
				['id' => $incoming->getId()]));
		}
		try {
			$sku = $request->get('sku');
			$ajaxCommands = $this->get(IncomingProductScanWorkflow::class)->newScan($sku, $incoming);
		} catch (\Exception $exception) {
			$messages['error'][] = "Error: {$exception->getMessage()}";
			$this->get(MessagePrinter::class)->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get(IncomingProductScanWorkflow::class)
					->getMessageBagView());
		}
		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}

	/**
	 * @Route("/{id}/ajax/load", name="incoming_products_scan_load")
	 */
	public function loadIncomingProductScanFromPackingListAction(Request $request, Incoming $incoming)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX.");
			return $this->redirect($this->generateUrl('incoming_products_scanned',
				['id' => $incoming->getId()]));
		}
		try {
			$ajaxCommands = $this->get(IncomingProductScanWorkflow::class)
				->loadScannedProductsFromPackingList($incoming);
		} catch (\Exception $exception) {
			$messages['error'][] = "Error: {$exception->getMessage()}";
			$this->get(MessagePrinter::class)->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get(IncomingProductScanWorkflow::class)
					->getMessageBagView());
		}
		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}

	/**
	 * Submits incoming products scanned form (ajax).
	 *
	 * @Route("/{id}/ajax/complete-scan", name="incoming_products_scan_complete")
	 */
	public function incomingProductsScannedCompleteAjaxAction(Request $request, Incoming $incoming)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error',
				"Form should have been submitted via AJAX.");
			return $this->redirect($this->generateUrl('incoming_products_scanned',
				['id' => $incoming->getId()]));
		}
		try {
			$form = $request->get('scannedProduct');

			//if receive force submit, we don't want update roveconcepts
			$updateRove = $request->get('forceSubmit') ? false : true;

			$ajaxCommands = $this->get(IncomingProductScanWorkflow::class)
				->completeProductScan($incoming, $form, $updateRove);
		} catch (WorkflowAPIException $workflowAPIException) {
			$messages['error'][] = "Complete Container Scan Failed! You can try to complete scan without notify" .
				" Roveconcepts by using [FORCE COMPLETE BUTTON]." .
				" Please notify them that this container is complete if you do this.<br>" .
				$workflowAPIException->getMessage();
			$this->get(MessagePrinter::class)->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get(IncomingProductScanWorkflow::class)
					->getMessageBagView());
			$ajaxCommands[] = new AjaxCommandDTO('#product_scan_form_force_submit',
				AjaxCommandDTO::OP_SHOW);
		} catch (\Exception $exception) {
			$messages['error'][] = "Complete Container Scan Failed! Please contact Rove dev team with detailed error message.<br>"
				. $exception->getMessage();
			$this->get(MessagePrinter::class)->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get(IncomingProductScanWorkflow::class)
					->getMessageBagView());
		}
		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}

	/**
	 * Ajax split of an existing IncomingProductScan item.
	 *
	 * @Route("/incoming-scan-split/ajax/{id}", name="incoming_products_scan_split_ajax")
	 */
	public function incomingProductsScannedSplitAjaxAction(Request $request, IncomingProductScan $incomingProductScan)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error',
				"Form should have been submitted via AJAX.");
			return $this->redirect($this->generateUrl('incoming_products_scanned',
				['id' => $incomingProductScan->getIncoming()->getId()]));
		}
		try {
			$ajaxCommands = $this->get(IncomingProductScanWorkflow::class)
				->splitProductScan($incomingProductScan);
		} catch (\Exception $exception) {
			$messages['error'][] = "Error: {$exception->getMessage()}";
			$this->get(MessagePrinter::class)->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get(IncomingProductScanWorkflow::class)
					->getMessageBagView());
		}
		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}
}