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

use WarehouseBundle\Form\BookingProductType;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingProduct;

/**
 * Rest controller for booking
 *
 * @package WarehouseBundle\Controller
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */
class BookingProductRestController extends FOSRestController
{
    /**
     * Return all products for a booking identified by unique booking id.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = true,
     *   description = "Return all products for a booking identified by unique booking id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the booking is not found"
     *   }
     * )
     *
     * @param string $id unique booking id
     *
     * @return View
     */
    public function getProductAction($id)
    {
        $bookingManager = $this->get('BookingManager');
        $booking = $bookingManager->findBookingById($id);

        if (false === $booking) {
            throw $this->createNotFoundException("Booking does not exist.");
        }

        $view = View::create();
        $view->setData($booking->getProducts())->setStatusCode(200);
        return $view;
    }

    /**
     * Create a Product from the submitted data.<br/>
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new product from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="id", nullable=false, strict=true, description="Unique booking id.")
     * @RequestParam(name="product", nullable=false, strict=true, description="Product.")
     *
     * @return View
     */
    public function postProductAction(ParamFetcher $paramFetcher)
    {
        $bookingManager = $this->get('BookingManager');
        $booking = $bookingManager->findBookingById($paramFetcher->get('id'));
        
        if (false === $booking) {
            throw $this->createNotFoundException("Booking does not exist.");
        }

        $booking->addProduct($paramFetcher->get('product'));
        $booking->setModified(new \DateTime('now'));

        # persist and flush
        $bookingManager->updateBooking($booking,true);

        $view = View::create();
        $view->setData($booking->getProducts())->setStatusCode(200);
        return $view;
    }

    /**
     * Delete a Product from the submitted data.<br/>
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = true,
     *   description = "Deletes a product from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="id", nullable=false, strict=true, description="Unique booking id.")
     * @RequestParam(name="product", nullable=false, strict=true, description="Product.")
     *
     * @return View
     */
    public function deleteProductAction(ParamFetcher $paramFetcher)
    {
        $bookingManager = $this->get('BookingManager');
        $booking = $bookingManager->findBookingById($paramFetcher->get('id'));

        if (false === $booking) {
            throw $this->createNotFoundException("Booking does not exist.");
        }

        // We do not check if the Role previously exists.
        $booking->removeProduct($paramFetcher->get('product'));
        
        # persist and flush
        $bookingManager->updateBooking($booking,true);
        
        $view = View::create();
        $view->setData($booking->getProducts())->setStatusCode(200);
        return $view;
    }

}