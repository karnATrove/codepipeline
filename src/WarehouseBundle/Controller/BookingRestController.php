<?php
namespace WarehouseBundle\Controller;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use WarehouseBundle\Form\BookingRestType;
use WarehouseBundle\Entity\Booking;

/**
 * Rest controller for booking
 *
 * @package WarehouseBundle\Controller
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */
class BookingRestController extends FOSRestController
{
    /**
     * Return the overall booking list.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Booking",
     *   description = "Return the overall Booking List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the booking is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getBookingsAction()
    {
        $bookingManager = $this->get('BookingManager');
        $bookings = $bookingManager->findBookings();
        if (!$bookings) {
            throw $this->createNotFoundException('Data not found.');
        }
        $view = View::create();
        $view->setData($bookings)->setStatusCode(200);
        return $view;
    }

    /**
     * Return an booking identified by unique id.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Booking",
     *   description = "Return an booking identified by unique id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the booking is not found"
     *   }
     * )
     *
     * @Annotations\View(
     *     template = "WarehouseBundle:Booking:getBooking.html.twig",
     *     templateVar="booking"
     * )
     * 
     * @param integer $id booking id
     *
     * @return View
     */
    public function getBookingAction(Booking $booking)
    {
        if (false === $booking) {
            throw $this->createNotFoundException("Booking does not exist.");
        }
        return $booking;
    }
/**
     * Return an booking identified by order number.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Booking",
     *   description = "Return an booking identified by order number",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the booking is not found"
     *   }
     * )
     *
     * @Annotations\View(
     *     template = "WarehouseBundle:Booking:getBooking.html.twig",
     *     templateVar="booking"
     * )
     * 
     * @param integer $orderNumber order number
     *
     * @return View
     */
    
    public function getBookingOrderNumberAction($orderNumber)
    {
        $booking = $this->get('doctrine')->getEntityManager()->getRepository('WarehouseBundle:Booking')->findOneBy(array('orderNumber'=>$orderNumber));
        if (false === $booking) {
            throw $this->createNotFoundException("Booking does not exist.");
        }
        return $booking;
    }

    /**
     * Create a Booking from the submitted form data.<br/>
     *
     * @ApiDoc(
     *   resource = "Booking",
     *   description = "Creates a new booking from the submitted form data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   input="WarehouseBundle\Form\BookingRestType"
     * )
     *
     * @param Request $request Request
     *
     * @return View
     */
    public function postBookingFormAction(Request $request)
    {
        $bookingManager = $this->get('BookingManager');
        $booking = ($bookingManager->createBooking())->setCreated(new \DateTime('now'));
        //$form = $this->container->get('fos_comment.form_factory.thread')->createForm();
        $form = $this->createForm(new BookingRestType(),$booking,array('method'=>'POST'));
        $form->setData($booking);
        $form->handleRequest($request);

        if (!$form->isValid()) {
          throw new \Symfony\Component\HttpKernel\Exception\HttpException(Response::HTTP_BAD_REQUEST, $form->getErrors(true, false));
        }

        if ($form->isValid()) {
            $bookingManager->updateBooking($booking);
            return View::create($booking, 200);
            return $booking;
        }
        return View::create($form, 400);
        return $form;
        
        $view = View::create();
        $errors = $this->get('validator')->validate($booking);
        if (count($errors) == 0) {
            //$bookingManager->updateBooking($booking,TRUE); #flush
            $view->setData($booking)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Create a Booking from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = "Booking",
     *   description = "Creates a new booking from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="ordernumber", nullable=false, strict=true, description="Order number from client.")
     * @RequestParam(name="orderreference", nullable=false, strict=true, description="Order reference from client.")
     * @RequestParam(name="ordertype", nullable=false, strict=true, description="Order type.")
     * @RequestParam(name="carrierid", nullable=false, strict=true, description="The carrier.")
     * @RequestParam(name="products", nullable=true, strict=true, description="The products.", map=true)
     *
     * @return View
     */
    public function postBookingAction(ParamFetcher $paramFetcher)
    {
       // return $this->createForm(new BookingRestType());
        $manipulator = $this->get('app.booking');
        $booking = $manipulator->create(
            $paramFetcher->get('ordernumber'),
            $paramFetcher->get('orderreference'),
            $paramFetcher->get('ordertype'),
            $paramFetcher->get('carrierid')
        );

        if (is_array($paramFetcher->get('products'))) {
            $this->get('session')->getFlashBag()->add('success',  'array: '. print_r($paramFetcher->get('products'),TRUE));
        } elseif (is_object($paramFetcher->get('products'))) {
            $this->get('session')->getFlashBag()->add('success',  'object: '. print_r($paramFetcher->get('products'),TRUE));
        } else {
            $this->get('session')->getFlashBag()->add('success',  'string: '. $paramFetcher->get('products'));
        }

        $view = View::create();
        $errors = $this->get('validator')->validate($booking);
        if (count($errors) == 0) {
            //$bookingManager->updateBooking($booking,TRUE); #flush
            $view->setData($booking)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Update a Booking from the submitted data by ID.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Booking",
     *   description = "Updates a booking from the submitted data by ID.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="id", nullable=false, strict=true, description="Unique ID.")
     * @RequestParam(name="ordernumber", nullable=true, strict=true, description="Order number from client.")
     * @RequestParam(name="orderreference", nullable=true, strict=true, description="Order reference from client.")
     * @RequestParam(name="ordertype", nullable=true, strict=true, description="Order type.")
     * @RequestParam(name="carrierid", nullable=true, strict=true, description="The carrier.")
     *
     * @return View
     */
    public function putBookingAction(ParamFetcher $paramFetcher)
    {
        $bookingManager = $this->get('BookingManager');
        $booking = $bookingManager->findBookingBy(
            array('id' => $paramFetcher->get('id'))
        );

        if (false === $booking) {
            throw $this->createNotFoundException("Booking does not exist.");
        }

        if($paramFetcher->get('ordernumber')){$booking->setOrderNumber($paramFetcher->get('ordernumber'));}
        if($paramFetcher->get('orderreference')){$booking->setOrderReference($paramFetcher->get('orderreference'));}
        if($paramFetcher->get('ordertype')){$booking->setOrderType($paramFetcher->get('ordertype'));}
        if($paramFetcher->get('carrierid')){
            $carrierManager = $this->get('warehouse.manager.carrier_manager');
            $booking->setCarrier($carrierManager->find($paramFetcher->get('carrierid')));
        }
        $booking->setModified(new \DateTime('now'));

        $view = View::create();
        $errors = $this->get('validator')->validate($booking);
        if (count($errors) == 0) {
            $bookingManager->updateBooking($booking,TRUE); # Flush
            $view->setData($booking)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }


    /**
     * Delete an booking identified by unique id.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Booking",
     *   description = "Delete an booking identified by unique id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the booking is not found"
     *   }
     * )
     *
     * @param string $id unique id
     *
     * @return View
     */
    public function deleteBookingAction($id)
    {
        $bookingManager = $this->get('BookingManager');
        $booking = $bookingManager->findBookingBy(
            array('id' => $id)
        );

        if (!$booking) {
            throw $this->createNotFoundException('Booking not found.');
        }

        $bookingManager->deleteBooking($booking);

        $view = View::create();
        $view->setData("Booking deleted.")->setStatusCode(204);
        return $view;
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);
        return $view;
    }

}