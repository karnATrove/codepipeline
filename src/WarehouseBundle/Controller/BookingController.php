<?php

namespace WarehouseBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use BG\BarcodeBundle\Util\Base1DBarcode as barCode;
use BG\BarcodeBundle\Util\Base2DBarcode as matrixCode;

use Symfony\Component\Validator\Constraints\DateTime;
use Timestampable\Fixture\Document\Book;
use WarehouseBundle\Doctrine\BookingManager;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingLog;
use WarehouseBundle\Entity\Shipment;
use WarehouseBundle\Utils\StringHelper;

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
		/** @var QueryBuilder $queryBuilder */
		$queryBuilder = $em->getRepository('WarehouseBundle:Booking')->createQueryBuilder('e');

		// Remove deleted
		if (empty($request->get('status')) && !(is_numeric($request->get('status')) && intval($request->get('status')) == 0)) {
			$queryBuilder->andWhere('e.status <> :bstatus');
			$queryBuilder->setParameter('bstatus', 0);
		}

		list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
		list($bookings, $pagerHtml) = $this->paginator($queryBuilder, $request);

		return $this->render('booking/index.html.twig', array(
			'bookings' => $bookings,
			'pagerHtml' => $pagerHtml,
			'filterForm' => $filterForm->createView(),

		));
	}

	/**
	 * Create filter form and process filter request.
	 *
	 */
	protected function filter(QueryBuilder $queryBuilder, Request $request)
	{
		$session = $request->getSession();
		$filterForm = $this->createForm('WarehouseBundle\Form\BookingFilterType');

		# Default sort
		if (empty($request->request->set('pcg_sort_col', ''))) {
			$request->request->set('pcg_sort_col', 'status');
			$request->request->set('pcg_sort_order', 'asc');
		}

		// Reset filter
		if ($request->get('filter_action') == 'reset') {
			$session->remove('BookingControllerFilter');
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
				$session->set('BookingControllerFilter', $filterData);
			}
		} else {
			// Get filter from session
			if ($session->has('BookingControllerFilter')) {
				$filterData = $session->get('BookingControllerFilter');

				foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
					if (is_object($filter)) {
						$filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
					}
				}

				$filterForm = $this->createForm('WarehouseBundle\Form\BookingFilterType', $filterData);
				$this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
			}
		}
		return array($filterForm, $queryBuilder);
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
		$pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
			'proximity' => 3,
			'prev_message' => 'previous',
			'next_message' => 'next',
		));

		return array($entities, $pagerHtml);
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

			$editLink = $this->generateUrl('booking_edit', array('id' => $booking->getId()));
			$this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New booking was created successfully.</a>");

			$nextAction = $request->get('submit') == 'save' ? 'booking' : 'booking_new';
			return $this->redirectToRoute($nextAction);
		}
		return $this->render('booking/new.html.twig', array(
			'booking' => $booking,
			'form' => $form->createView(),
		));
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
			$em->persist($booking);

			/**
			 * find booking changes
			 * @var UnitOfWork $uow */
			$uow = $em->getUnitOfWork();
			$uow->computeChangeSets();
			$changes = $uow->getEntityChangeSet($booking);

			$note = null;
			foreach ($changes as $name => $change) {
				switch ($name) {
					case "modified":
						continue;
						break;
					case "futureship":
						$changedFrom = $change[0] ? $change[0]->format('M d. Y') : "NULL";
						$changedTo = $change[1] ? $change[1]->format('M d. Y') : "NULL";
						$note .= "Future Ship Date changed from {$changedFrom} to {$changedTo}. ";
						continue;
						break;
					case "status":
						$changedFrom = \WarehouseBundle\Utils\Booking::bookingStatusName($change[0]);
						$changedTo = \WarehouseBundle\Utils\Booking::bookingStatusName($change[1]);
						$note .= "Booking status changed from {$changedFrom} to {$changedTo}. ";
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
						$changedFrom = \WarehouseBundle\Utils\Booking::bookingCarrierName($change[0]);
						$changedTo = \WarehouseBundle\Utils\Booking::bookingCarrierName($change[1]);
						$note .= "Booking Carrier changed from {$changedFrom} to {$changedTo}. ";
						continue;
						break;
					case "orderType":
						$changedFrom = \WarehouseBundle\Utils\Booking::bookingOrderTypeName($change[0]);
						$changedTo = \WarehouseBundle\Utils\Booking::bookingOrderTypeName($change[1]);
						$note .= "Booking Order Type changed from {$changedFrom} to {$changedTo}. ";
						continue;
						break;
					default:
						$field = StringHelper::printCamel($name);
						$note .= "{$field} changed from {$change[0]} to {$change[1]}. ";
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
		return $this->render('booking/edit.html.twig', array(
			'booking' => $booking,
			'bookingLogs' => $bookingLogs,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
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
			->setAction($this->generateUrl('booking_delete', array('id' => $booking->getId())))
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
			$bookingManager = $this->get('BookingManager');
			$bookingManager->deleteBooking($booking);
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
		$ids = $request->get("ids", array());
		$action = $request->get("bulk_action", "delete");

		/** @var BookingManager $bookingManager */
		$bookingManager = $this->get('BookingManager');

		$cnt_changes = 0;
		if ($action == "delete") {
			try {
				foreach ($ids as $id) {
					$booking = $bookingManager->findBookingById($id);
					$bookingManager->deleteBooking($booking);
					$cnt_changes++;
				}
				$this->get('session')->getFlashBag()->add('success', $cnt_changes . ' bookings were deleted successfully!');
			} catch (\Exception $ex) {
				$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the bookings ');
			}
		}

		if ($action == "pickingon" || $action == "pickingoff") {
			try {
				foreach ($ids as $id) {
					$booking = $bookingManager->findBookingById($id);
					$booking->setPickingFlag($action == 'pickingon' ? 1 : 0);
					$bookingManager->updateBooking($booking, true); # persist, flush
					$cnt_changes++;
				}

				$this->get('session')->getFlashBag()->add('success', $cnt_changes . ' bookings were successfully updated!');

			} catch (\Exception $ex) {
				$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the bookings ');
			}
		}

		if ($action == "print" || $action == "print_w_documents" || $action == "print_wo_documents") {
			try {
				foreach ($ids as $id) {
					$booking = $bookingManager->findBookingById($id);
					// TODO
				}

				$this->get('session')->getFlashBag()->add('success', $cnt_changes . ' bookings were deleted successfully!');

			} catch (\Exception $ex) {
				$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the bookings ');
			}
		}

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
					'No booking found for id ' . $id
				);
			}

			//$my2dBarcode = new matrixCode();
			$barCode = new barCode();
			$barCode->savePath = $this->getBarcodeCachePath(false) . '/';
			$orderBarCode = $barCode->getBarcodePNGPath(str_pad($booking->getOrderNumber(), 12, 0, STR_PAD_LEFT), 'C128', 1.75, 45);
			$html = $this->renderView('booking/pdf/booking.html.twig', array(
				'booking' => $booking,
				'barcodePathAndFile' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $orderBarCode),
				'orderBarCode' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $orderBarCode),
				//'productBarCodes' => $product_barcodes,
			));
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
		$product_barcodes = array();
		foreach ($booking->getProducts() as $product) {
			$product_barcodes[$product->getId()] = str_replace($this->get('kernel')->getRootDir() . '/../web', '', $barCode->getBarcodePNGPath($product->getProduct()->getModel(), 'C128', 1.5, 35));
		}

		$html = $this->renderView('booking/pdf/booking.html.twig', array(
			'booking' => $booking,
			'orderBarCodeSku' => 'BO' . str_pad($booking->getOrderNumber(), 10, 0, STR_PAD_LEFT),
			'orderBarCode' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $orderBarCode),
			'productBarCodes' => $product_barcodes,
		));

		return new Response($html, 200);
	}

	private function updateBookingAndSaveShipment()
	{

	}
}
