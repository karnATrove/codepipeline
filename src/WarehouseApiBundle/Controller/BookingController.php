<?php

namespace WarehouseApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Rove\CanonicalDto\Response\ResponseErrorDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WarehouseApiBundle\Exception\ApiException;
use WarehouseApiBundle\Mapper\Booking\BookingMapper;
use WarehouseBundle\Manager\BookingManager;

class BookingController extends FOSRestController
{

	/**
	 * Return the all Bookings. You can put limit and page parameters to select your record. Default return 50 record
	 * for page 1.
	 *
	 * @Secure(roles="ROLE_API")
	 * @ApiDoc(
	 *   resource = "Booking",
	 *   description = "Get Bookings",
	 *     statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the booking is not found",
	 *     500 = "Error"
	 *   }
	 * )
	 *
	 * @param Request $request
	 *
	 * @return \FOS\RestBundle\View\View
	 */
	public function getBookingsAction(Request $request)
	{
		try {
			$view = $this->get('warehouse_api.workflow.booking_workflow')->getBookingsView($request);
			return $view;
		} catch (\Exception $exception) {
			$errorDto = new ResponseErrorDto(Response::HTTP_INTERNAL_SERVER_ERROR, "Error", $exception->getMessage());
			$view = View::create();
			$view->setData($errorDto)->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			return $view;
		}
	}

	/**
	 * Return an booking identified by booking order reference.
	 *
	 * @Secure(roles="ROLE_API")
	 * @ApiDoc(
	 *   resource = "Booking",
	 *   description = "Return an booking identified by unique id",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the booking is not found",
	 *     500 = "Error"
	 *   }
	 * )
	 *
	 * @param string $orderReference order reference passed from 3rd-party
	 *
	 * @return View
	 */
	public function getBookingAction(string $orderReference)
	{
		try {
			$bookingManager = $this->get(BookingManager::class);
			$booking = $bookingManager->findBy(['orderReference' => $orderReference], null, 1);
			if (empty($booking)) {
				throw new ApiException("Booking with order reference {$orderReference} not found", Response::HTTP_NOT_FOUND);
			}
			$booking = $booking[0];
			$bookingDto = BookingMapper::mapToDto($booking);
			$view = View::create();
			$view->setData($bookingDto)->setStatusCode(Response::HTTP_OK);
			return $view;
		} catch (ApiException $apiException) {
			$errorDto = new ResponseErrorDto($apiException->getHttpCode(), "Error", $apiException->getMessage());
			$view = View::create();
			$view->setData($errorDto)->setStatusCode($apiException->getHttpCode());
			return $view;
		} catch (\Exception $exception) {
			$errorDto = new ResponseErrorDto(Response::HTTP_INTERNAL_SERVER_ERROR, "Error", $exception->getMessage());
			$view = View::create();
			$view->setData($errorDto)->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			return $view;
		}
	}

	/**
	 *
	 * Create new Booking
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @Secure(roles="ROLE_API")
	 * @ApiDoc(
	 *   resource = "Booking",
	 *   description = "Create new booking",
	 *   statusCodes = {
	 *     201 = "Created",
	 *     400 = "Failed",
	 *     500 = "Error"
	 *   }
	 * )
	 *
	 * @return View
	 *
	 */
	public function postBookingAction(Request $request)
	{
		try {

			$this->get('warehouse_api.workflow.booking_workflow')->createBooking($request);
			$view = View::create();
			$view->setData('{}')->setStatusCode(Response::HTTP_CREATED);
			return $view;
		} catch (ApiException $apiException) {
			$errorDto = new ResponseErrorDto($apiException->getHttpCode(), 'Error', $apiException->getMessage());
			$view = View::create();
			$view->setData($errorDto)->setStatusCode($apiException->getHttpCode());
			return $view;
		} catch (\Exception $exception) {
			$errorDto = new ResponseErrorDto(Response::HTTP_INTERNAL_SERVER_ERROR, "Error", $exception->getMessage());
			$view = View::create();
			$view->setData($errorDto)->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			return $view;
		}
	}
}
