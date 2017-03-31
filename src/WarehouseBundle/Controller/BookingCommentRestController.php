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

use WarehouseBundle\Form\BookingCommentRestType;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingComment;

/**
 * Rest controller for booking comment
 *
 * @package WarehouseBundle\Controller
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */
class BookingCommentRestController extends FOSRestController
{
    /**
     * Return the overall booking comments by booking id.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "BookingComment",
     *   description = "Return the overall booking comments by booking id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the booking is not found"
     *   }
     * )
     * 
     * @param integer $id booking id
     *
     * @return View
     */
    public function getBookingCommentsAction(Booking $booking)
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
     *   resource = "BookingComment",
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
    public function getBookingCommentAction(Booking $booking)
    {
        if (false === $booking) {
            throw $this->createNotFoundException("Booking does not exist.");
        }
        return $booking;
    }

    /**
     * Create a Booking Comment from the submitted form data.<br/>
     *
     * @ApiDoc(
     *   resource = "BookingComment",
     *   description = "Creates a new booking comment from the submitted form data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   input="WarehouseBundle\Form\BookingCommentRestType"
     * )
     *
     * @param Request $request Request
     * @param integer $id booking id
     *
     * @return View
     */
    public function postBookingCommentFormAction(Request $request, Booking $booking)
    {
        $bookingManager = $this->get('BookingManager');
        $booking = ($bookingManager->createBooking())->setCreated(new \DateTime('now'));
        //$form = $this->container->get('fos_comment.form_factory.thread')->createForm();
        $form = $this->createForm(new BookingCommentRestType(),$booking,array('method'=>'POST'));
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
     * Create a Booking Comment from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = "BookingComment",
     *   description = "Creates a new booking comment from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     * @param string $order_reference booking id
     *
     * @RequestParam(name="comment", nullable=false, strict=true, description="Order number from client.")
     *
     * @return View
     */
    public function postBookingCommentAction(ParamFetcher $paramFetcher, Booking $booking)
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