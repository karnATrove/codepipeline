<?php

namespace WarehouseApiBundle\Workflow;


use FOS\RestBundle\View\View;
use Rove\CanonicalDto\Response\ResponseDto;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WarehouseApiBundle\Mapper\Booking\BookingMapper;

class BookingWorkflow extends BaseWorkflow
{
	private $bookingManager;

	/**
	 * BookingWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->bookingManager = $this->container->get('warehouse.manager.booking_manager');
	}

	/**
	 * @param Request $request
	 *
	 * @return View
	 */
	public function getBookingsView(Request $request)
	{
		$criteria = $this->buildBookingSearchCriteria($request);
		$orderBy = $this->buildBookingSearchOrder($request);
		$limit = empty($request->get('limit')) ? 50 : $request->get('limit');;
		$page = empty($request->get('page')) ? 1 : $request->get('page');

		$total = $this->bookingManager->count($criteria);
		$offset = $limit * (($page - 1) > 0 ? ($page - 1) : 0);
		$bookings = $this->bookingManager->findBy($criteria, $orderBy, $limit, $offset);
		$bookingDtos = BookingMapper::mapToDtoList($bookings);
		$view = View::create();
		$view->setData($bookingDtos)->setStatusCode(Response::HTTP_OK)->setHeader('TOTAL_COUNT', $total);
		return $view;
	}

	private function buildBookingSearchCriteria(Request $request)
	{
		$criteria = [];
		return $criteria;
	}

	private function buildBookingSearchOrder(Request $request)
	{
		$order = [];
		return $order;
	}
}