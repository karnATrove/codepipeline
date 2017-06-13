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
use WarehouseBundle\Manager\LocationManager;
use WarehouseBundle\Utils\AjaxCommandParser;

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
		$scannedProducts = $this->get('warehouse.manager.incoming_product_scan_manager')
			->getByIncoming($incoming);
		$locations = $this->get('warehouse.manager.location_manager')->getLocations();
		$locationDropdownList = LocationManager::toArray($locations);
		return $this->render('incoming/products_scanned.html.twig', [
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
		$qty = $request->get('quantity');
		$locationId = $request->get('location');
		if ($qty !== null) {
			$incomingProductScan->setQtyOnScan($qty);
		}
		$locationTitle = $incomingProductScan->getLocation() ?
			$incomingProductScan->getLocation()->printLocation() : "NULL";

		$em = $this->getDoctrine()->getManager();
		if ($locationId !== null) {
			if (empty($locationId)) {
				$incomingProductScan->setLocation(null);
				$locationTitle = "NULL";
			} else {
				$location = $this->get('warehouse.manager.location_manager')->findById($locationId);
				if (!$location) {
					return new JsonResponse(['status' => 'error', 'error' => "", 'message' => "Location not found"],
						JsonResponse::HTTP_OK);
				}
				$incomingProductScan->setLocation($location);
				$locationTitle = $location->printLocation();
			}
		}
		$incomingProductScan->setModified(new \DateTime());
		//persist data
		$em->persist($incomingProductScan);
		$em->flush();

		$message = "Model {$incomingProductScan->getProduct()->getModel()} updated with location: " .
			$locationTitle . " Quantity: {$incomingProductScan->getQtyOnScan()}";
		$response = ['status' => 'success', 'error' => "", 'message' => $message];
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}

	/**
	 * Ajax deletion of an IncomingProductScan item.
	 *
	 * @Route("/{id}/ajax/delete", name="incoming_products_scanned_delete_ajax")
	 */
	public function incomingProductsScannedDeleteAjaxAction(IncomingProductScan $incomingProductScan)
	{
		$ajaxCommands = $this->get('warehouse.workflow.incoming_product_scan_workflow')->delete($incomingProductScan);
		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}


	/**
	 * @Route("/{id}/render-product-scanned-row", name="incoming_products_scanned_render_row")
	 */
	public function renderProductScannedRowsAction(Incoming $incoming)
	{
		return new Response($this->get('warehouse.workflow.incoming_product_scan_workflow')
			->getScannedProductTableView($incoming));
	}

	/**
	 * @Route("/render-product-scanned-form-message-bag", name="product_scanned_form_message_bag")
	 */
	public function renderProductScannedFormMessageBagAction()
	{
		return new Response($this->get('warehouse.workflow.incoming_product_scan_workflow')->getMessageBagView());
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
			$ajaxCommands = $this->get('warehouse.workflow.incoming_product_scan_workflow')->newScan($sku, $incoming);
		} catch (\Exception $exception) {
			$messages['error'][] = "Error: {$exception->getMessage()}";
			$this->get('warehouse.utils.message_printer')->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get('warehouse.workflow.incoming_product_scan_workflow')
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
			$ajaxCommands = $this->get('warehouse.workflow.incoming_product_scan_workflow')
				->loadScannedProductsFromPackingList($incoming);
		} catch (\Exception $exception) {
			$messages['error'][] = "Error: {$exception->getMessage()}";
			$this->get('warehouse.utils.message_printer')->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get('warehouse.workflow.incoming_product_scan_workflow')
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
			$ajaxCommands = $this->get('warehouse.workflow.incoming_product_scan_workflow')
				->completeProductScan($incoming, $form);
		} catch (\Exception $exception) {
			$messages['error'][] = "Error: {$exception->getMessage()}";
			$this->get('warehouse.utils.message_printer')->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get('warehouse.workflow.incoming_product_scan_workflow')
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
			$ajaxCommands = $this->get('warehouse.workflow.incoming_product_scan_workflow')
				->splitProductScan($incomingProductScan);
		} catch (\Exception $exception) {
			$messages['error'][] = "Error: {$exception->getMessage()}";
			$this->get('warehouse.utils.message_printer')->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
				AjaxCommandDTO::OP_HTML, $this->get('warehouse.workflow.incoming_product_scan_workflow')
					->getMessageBagView());
		}
		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}
}