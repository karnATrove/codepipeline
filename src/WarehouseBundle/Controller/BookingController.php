<?php

namespace WarehouseBundle\Controller;

use BG\BarcodeBundle\Util\Base1DBarcode as barCode;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap3View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use WarehouseBundle\DTO\Booking\BulkAction;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingLog;
use WarehouseBundle\Entity\BookingStatusLog;
use WarehouseBundle\Entity\Shipment;
use WarehouseBundle\Enum\SessionEnum;
use WarehouseBundle\Exception\Manager\BookingManagerException;
use WarehouseBundle\Form\BookingFilterType;
use WarehouseBundle\Manager\BookingManager;
use WarehouseBundle\Utils\Booking as BookingUtility;
use WarehouseBundle\Utils\StringHelper;
use WarehouseBundle\Workflow\BookingWorkflow;

/**
 * Booking controller.
 *
 * @Route("/booking")
 */
class BookingController extends Controller
{

	/**
	 * Lists all Booking entities.
	 *
	 * @Route("/", name="booking")
	 * @Method("GET")
	 */
	public function indexAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$bookingRepository = $em->getRepository('WarehouseBundle:Booking');
		$queryBuilder = $bookingRepository->createQueryBuilder('e');

		// Remove deleted
		if (empty($request->get('status')) || !(is_numeric($request->get('status'))
				|| intval($request->get('status')) == 0)
		) {
			$queryBuilder->andWhere('e.status <> :bstatus');
			$queryBuilder->setParameter('bstatus', Booking::STATUS_DELETED);
		}
		list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
		list($bookings, $pagerHtml) = $this->paginator($queryBuilder, $request);
		return $this->render('booking/index.html.twig', [
			'bookings' => $bookings,
			'pagerHtml' => $pagerHtml,
			'filterForm' => $filterForm->createView(),
		]);
	}

	/**
	 * Create filter form and process filter request
	 */
	protected function filter(QueryBuilder $queryBuilder, Request $request)
	{
		$session = $request->getSession();
		$filterForm = $this->createForm(BookingFilterType::class);

		# Default sort
		if (empty($request->request->set('pcg_sort_col', ''))) {
			$request->request->set('pcg_sort_col', 'status');
			$request->request->set('pcg_sort_order', 'asc');
		}

		// Reset filter
		if ($request->get('filter_action') == 'reset') {
			$session->remove(SessionEnum::BOOKING_CONTROLLER_FILTER);
		}

		// Filter action
		if ($request->get('filter_action') == 'filter') {
			// Bind values from the request
			$filterForm->handleRequest($request);
			if ($filterForm->isValid()) {
				// Build the query from the given form object
				$this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
				// Save filter to session
				$filterData = $filterForm->getData();
				$session->set(SessionEnum::BOOKING_CONTROLLER_FILTER, $filterData);
			}
		} else {
			// Get filter from session
			if ($session->has(SessionEnum::BOOKING_CONTROLLER_FILTER)) {
				$filterData = $session->get(SessionEnum::BOOKING_CONTROLLER_FILTER);

				foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
					if (is_object($filter)) {
						$filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
					}
				}

				$filterForm = $this->createForm(BookingFilterType::class, $filterData);
				$this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
			}
		}
		return [$filterForm, $queryBuilder];
	}


	/**
	 * Get results from paginator and get paginator view.
	 *
	 */
	protected function paginator(QueryBuilder $queryBuilder, Request $request)
	{
		//sorting
		$sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'id');
		$queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
		// Paginator
		$adapter = new DoctrineORMAdapter($queryBuilder);
		$pagerfanta = new Pagerfanta($adapter);
		$pagerfanta->setMaxPerPage($request->get('pcg_show', 50));

		try {
			$pagerfanta->setCurrentPage($request->get('pcg_page', 1));
		} catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
			$pagerfanta->setCurrentPage(1);
		}

		$entities = $pagerfanta->getCurrentPageResults();

		// Paginator - route generator
		$me = $this;
		$routeGenerator = function ($page) use ($me, $request) {
			$requestParams = $request->query->all();
			$requestParams['pcg_page'] = $page;
			return $me->generateUrl('booking', $requestParams);
		};

		// Paginator - view
		$view = new TwitterBootstrap3View();
		$pagerHtml = $view->render($pagerfanta, $routeGenerator, [
			'proximity' => 3,
			'prev_message' => 'previous',
			'next_message' => 'next',
		]);

		return [$entities, $pagerHtml];
	}


	/**
	 * Displays a form to create a new Booking entity.
	 *
	 * @Route("/new", name="booking_new")
	 * @Method({"GET", "POST"})
	 */
	public function newAction(Request $request)
	{
		$bookingManager = $this->get('BookingManager');
		$booking = $bookingManager->createBooking();

		$form = $this->createForm('WarehouseBundle\Form\BookingType', $booking);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$bookingManager->updateBooking($booking, TRUE); # Persists/flush

			$editLink = $this->generateUrl('booking_edit', ['id' => $booking->getId()]);
			$this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New booking was created successfully.</a>");

			$nextAction = $request->get('submit') == 'save' ? 'booking' : 'booking_new';
			return $this->redirectToRoute($nextAction);
		}
		return $this->render('booking/new.html.twig', [
			'booking' => $booking,
			'form' => $form->createView(),
		]);
	}

	/**
	 * Displays a form to edit an existing Booking entity.
	 *
	 * @Route("/{id}/edit", name="booking_edit")
	 * @Method({"GET", "POST"})
	 */
	public function editAction(Request $request, Booking $booking)
	{
		$bookingManager = $this->get('BookingManager');
		$deleteForm = $this->createDeleteForm($booking);
		$editForm = $this->createForm('WarehouseBundle\Form\BookingType', $booking);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$booking->setModified(new \DateTime('now'));
			$bookingManager->updateBooking($booking, false);//persist not flush
			if ($booking->getStatus() == Booking::STATUS_SHIPPED) {
				$booking->setShipped(new \DateTime('now'));
			}
			$em->persist($booking);

			/**
			 * find booking changes
			 * @var UnitOfWork $uow
			 */
			$uow = $em->getUnitOfWork();
			$uow->computeChangeSets();
			$changes = $uow->getEntityChangeSet($booking);

			$note = null;
			foreach ($changes as $name => $change) {
				switch ($name) {
					case "modified":
						continue;
						break;
					case "shipped":
						continue;
						break;
					case "futureship":
						$changedFrom = $change[0] ? $change[0]->format('M d. Y') : "NULL";
						$changedTo = $change[1] ? $change[1]->format('M d. Y') : "NULL";
						$note .= "Future Ship Date changed from {$changedFrom} to {$changedTo}. ";
						continue;
						break;
					case "status":
						$changedFrom = BookingUtility::bookingStatusName($change[0]);
						$changedTo = BookingUtility::bookingStatusName($change[1]);
						$note .= "Booking status changed from {$changedFrom} to {$changedTo}. ";

						//save status log
						$statusLog = new BookingStatusLog();
						$statusLog->setOldBookingStatus($change[0]);
						$statusLog->setNewBookingStatus($change[1]);
						$statusLog->setBooking($booking);
						$em->persist($statusLog);

						//save shipment
						if ($change[1] == Booking::STATUS_SHIPPED) {
							$shipment = new Shipment();
							$shipment->setUser($this->getUser());
							$shipment->setBooking($booking);
							$shipment->setCreated(new \DateTime('now'));
							$em->persist($shipment);
						}
						continue;
						break;
					case "carrierId":
						$changedFrom = BookingUtility::bookingCarrierName($change[0]);
						$changedTo = BookingUtility::bookingCarrierName($change[1]);
						$note .= "Booking Carrier changed from {$changedFrom} to {$changedTo}. ";
						continue;
						break;
					case "orderType":
						$changedFrom = BookingUtility::bookingOrderTypeName($change[0]);
						$changedTo = BookingUtility::bookingOrderTypeName($change[1]);
						$note .= "Booking Order Type changed from {$changedFrom} to {$changedTo}. ";
						continue;
						break;
					default:
						$field = StringHelper::printCamel($name);
						$note .= "{$field} changed from " . $change[0] . " to " . $change[1] . ". ";
						break;
				}

			}

			if ($note) {
				$bookingLog = new BookingLog();
				$bookingLog->setBooking($booking)
					->setUser($this->getUser())
					->setCreated(new \DateTime('now'))
					->setNote($note);

				$em->persist($bookingLog);
			}
			$em->flush();
			$this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
		}

		$bookingLogs = $this->getDoctrine()
			->getRepository('WarehouseBundle:BookingLog')
			->getLogByBooking($booking, 10);
		return $this->render('booking/edit.html.twig', [
			'booking' => $booking,
			'bookingLogs' => $bookingLogs,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		]);
	}

	/**
	 * Creates a form to delete a Booking entity.
	 *
	 * @param Booking $booking The Booking entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(Booking $booking)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('booking_delete', ['id' => $booking->getId()]))
			->setMethod('DELETE')
			->getForm();
	}

	/**
	 * Deletes a Booking entity.
	 *
	 * @Route("/{id}", name="booking_delete")
	 * @Method("DELETE")
	 */
	public function deleteAction(Request $request, Booking $booking)
	{
		$form = $this->createDeleteForm($booking);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$bookingManager = $this->get('BookingManager');
			$bookingManager->deleteBooking($booking);
			$this->get('session')->getFlashBag()->add('success', 'The Booking was deleted successfully');
		} else {
			$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Booking');
		}
		return $this->redirectToRoute('booking');
	}

	/**
	 * Delete Booking by id
	 *
	 * @Route("/delete/{id}", name="booking_by_id_delete")
	 * @Method("GET")
	 */
	public function deleteByIdAction(Booking $booking)
	{
		try {
			$bookingManager = $this->get('warehouse.manager.booking_manager');
			$bookingManager->deleteBookingById($booking->getId());
			$this->get('session')->getFlashBag()->add('success', 'The Booking was deleted successfully');
		} catch (\Exception $ex) {
			$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Booking');
		}
		return $this->redirect($this->generateUrl('booking'));

	}

	/**
	 * Bulk Action
	 * @Route("/bulk-action/", name="booking_bulk_action")
	 * @Method("POST")
	 */
	public function bulkAction(Request $request)
	{
		$form = $request->get('form');
		if (!$form) {
			$this->get('session')->getFlashBag()->add('error',
				'No form submitted');
			return $this->redirect($this->generateUrl('booking'));
		}
		try {
			$serializer = new Serializer([new ObjectNormalizer()]);
			/** @var BulkAction $bulkAction */
			$bulkAction = $serializer->denormalize($form, BulkAction::class);
			$ids = $bulkAction->getOrderIds();
			if (empty($ids)) {
				$this->get('session')->getFlashBag()->add('error',
					"Error: no booking selected");
				return $this->redirect($this->generateUrl('booking'));
			}
			$responseData = null;
			$response = $this->get('warehouse.work_flow.booking_work_flow')->bulkAction($bulkAction, $responseData);
			switch ($response) {
				case BookingWorkflow::BULK_ACTION_TYPE_RENDER_PDF:
					return new Response($responseData, 200);
					break;
				case BookingWorkflow::BULK_ACTION_TYPE_RETURN:
					return $responseData;
				default:
					break;
			}
			$this->get('session')->getFlashBag()->add('success',
				'Bookings get updated');
		} catch (BookingManagerException $bookingManagerException) {
			$this->get('session')->getFlashBag()->add('error',
				"Error: {$bookingManagerException->getdetail()}. please resolve errors and redo");
		} catch (\Exception $exception) {
			$this->get('session')->getFlashBag()->add('error',
				"Error: {$exception->getMessage()}. please resolve errors and redo");
		}

//		  = new AjaxCommandDTO('booking_modal', AjaxCommandDTO::OP_MODAL, 'show');
//		$response = new ResponseDTO();
//		$response->addAjaxCommand($ajaxCommand);

		return $this->redirect($this->generateUrl('booking'));
	}

	/**
	 * Export Pick List to PDF
	 *
	 * @Route("/{id}/pick_list/pdf", name="pick_list_pdf")
	 */
	public function pickListPDFAction(Request $request, Booking $booking)
	{
		if (FALSE) {
			if (!$booking) {
				throw $this->createNotFoundException(
					'No booking found for id ' . $booking->getId()
				);
			}
			//$my2dBarcode = new matrixCode();
			$barCode = new barCode();
			$barCode->savePath = $this->getBarcodeCachePath(false) . '/';
			$orderBarCode = $barCode->getBarcodePNGPath(str_pad($booking->getOrderNumber(), 12, 0, STR_PAD_LEFT), 'C128', 1.75, 45);
			$html = $this->renderView('booking/pdf/booking.html.twig', [
				'booking' => $booking,
				'barcodePathAndFile' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $orderBarCode),
				'orderBarCode' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $orderBarCode)
			]);
		} else {
			$html = $this->pickListAction($request, $booking->getId());
		}

		$filename = $booking->getOrderNumber() . '.pdf';

		return new Response(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html),
			200,
			[
				'Content-Type' => 'application/pdf',
				'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
			]
		);
	}

	/**
	 * simple cache path returning method (sample cache path: "upload/barcode/cache" )
	 *
	 * @param bool $public
	 *
	 * @return string
	 *
	 */
	protected function getBarcodeCachePath($public = false)
	{

		return (!$public) ? $this->get('kernel')->getRootDir() . '/../web/uploads/barcode/cache' : '/uploads/barcode/cache';
	}

	/**
	 * Export Pick List to PDF
	 *
	 * SKU IDENTIFICATION
	 * BO0000068008 -> Booking 68006 (12 chars)
	 * E1-001 -> Booking Product
	 *
	 * @Route("/{id}/pick_list", name="pick_list")
	 */
	public function pickListAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('WarehouseBundle:Booking');
		$booking = $repository->find($id);

		if (!$booking) {
			throw $this->createNotFoundException(
				'No booking found for id ' . $id
			);
		}

		//$my2dBarcode = new matrixCode();
		$barCode = new barCode();
		$barCode->savePath = $this->getBarcodeCachePath(false) . '/';
		$orderBarCode = $barCode->getBarcodePNGPath('BO' . str_pad($booking->getOrderNumber(), 10, 0, STR_PAD_LEFT), 'C128', 1.75, 45);
		$product_barcodes = [];
		foreach ($booking->getProducts() as $product) {
			$product_barcodes[$product->getId()] = str_replace($this->get('kernel')->getRootDir() . '/../web', '', $barCode->getBarcodePNGPath($product->getProduct()->getModel(), 'C128', 1.5, 35));
		}

		$html = $this->renderView('booking/pdf/booking.html.twig', [
			'booking' => $booking,
			'orderBarCodeSku' => 'BO' . str_pad($booking->getOrderNumber(), 10, 0, STR_PAD_LEFT),
			'orderBarCode' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $orderBarCode),
			'productBarCodes' => $product_barcodes,
		]);

		return new Response($html, 200);
	}

	/**
	 * @param $ids
	 * @Route("/pick-summary", name="booking_pick_summary")
	 */
	public function pickSummaryAction(Request $request)
	{
		$ids = $request->get('ids');
		if (empty($ids)) {
			throw $this->createNotFoundException("booking not found");
		}
		$bookingIdList = explode(',', $ids);
		$pickSummaryModel = $this->get('warehouse.manager.booking_manager')->getPickSummaryModel($bookingIdList);
		$pickSummaryDTO = $this->get('warehouse.manager.booking_manager')->getPickSummaryDTO($pickSummaryModel);
		$bookings = $this->get('warehouse.manager.booking_manager')->getBookingByIdList($bookingIdList);
		$viewData = BookingManager::formatPickSummaryForView($pickSummaryDTO);
		$html = $this->renderView('booking/pdf/pick_summary.html.twig', [
			'bookings' => $bookings,
			'viewData' => $viewData]);
		return new Response($html, 200);
	}

	/**
	 * @param $ids
	 * @Route("/download-documents", name="booking_download_documents")
	 */
	public function downloadDocumentsAction(Request $request)
	{
		$ids = $request->get('ids');
		if (empty($ids)) {
			throw $this->createNotFoundException("booking not found");
		}
		$bookingIdList = explode(',', $ids);
		$response = $this->get('warehouse.work_flow.booking_work_flow')->downloadDocuments($bookingIdList);
		return $response;
	}
}
