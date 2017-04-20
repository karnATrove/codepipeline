<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Form\BookingProductType;

/**
 * Booking controller.
 *
 * @Route("/booking_product")
 */
class BookingProductController extends Controller
{


	/**
     * Displays a form to edit an existing BookingProduct entity.
     *
     * @Route("/{id}/edit", name="booking_product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, BookingProduct $bookingProduct)
    {
        $deleteForm = $this->createDeleteForm($bookingProduct);
        $editForm = $this->createForm('WarehouseBundle\Form\BookingProductType', $bookingProduct);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $bookingManager = $this->get('BookingManager');
            # Persist and flush
            $bookingManager->updateBookingProduct($bookingProduct,true);

            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('location_edit', array('id' => $bookingProduct->getId()));
        }
        return $this->render('bookingProduct/edit.html.twig', array(
            'bookingProduct' => $bookingProduct,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Deletes an BookingProduct entity.
     *
     * @Route("/{id}", name="booking_product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, BookingProduct $bookingProduct)
    {
        $form = $this->createDeleteForm($bookingProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookingManager = $this->get('BookingManager');
            $bookingManager->deleteBookingProduct($bookingProduct);
            $this->get('session')->getFlashBag()->add('success', 'The BookingProduct was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the BookingProduct');
        }

        # See if destination was passed (redirect)
        if ($request->get('destination') && !empty($request->get('destination')))
            return $this->redirect($request->get('destination'));

        # Default redirect
        return $this->redirect($this->generateUrl('bookingProduct'));
    }


    /**
     * Creates a form to delete a BookingProduct entity.
     *
     * @param BookingProduct $bookingProduct The BookingProduct entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(BookingProduct $bookingProduct)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('booking_product_delete', array('id' => $bookingProduct->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


    /**
     * Delete BookingProduct by id
     *
     * @Route("/delete/{id}", name="booking_product_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(BookingProduct $bookingProduct){
        $em = $this->getDoctrine()->getManager();
        
        try {
            $bookingManager = $this->get('BookingManager');
            $bookingManager->deleteBookingProduct($bookingProduct);
            $this->get('session')->getFlashBag()->add('success', 'The BookingProduct was deleted successfully');
        } catch (\Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the BookingProduct');
        }

        # See if destination was passed (redirect)
        $request = new Request();
        if ($request->get('destination') && !empty($request->get('destination')))
            return $this->redirect($request->get('destination'));

        # Default redirect
        return $this->redirect($this->generateUrl('bookingProduct'));

    }
    

    /**
    * Bulk Action
    * @Route("/bulk-action/", name="booking_product_bulk_action")
    * @Method("POST")
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $bookingManager = $this->get('BookingManager');
                $repository = $this->getDoctrine()->getManager()->getRepository('WarehouseBundle:BookingProduct');

                foreach ($ids as $id) {
                    $bookingProduct = $repository->find($id);
                    $bookingManager->deleteBookingProduct($bookingProduct);
                }
                $this->get('session')->getFlashBag()->add('success', 'Booking Products was deleted successfully!');
            } catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Booking Products ');
            }
        }

        # See if destination was passed (redirect)
        if ($request->get('destination') && !empty($request->get('destination')))
            return $this->redirect($request->get('destination'));

        # Default redirect
        return $this->redirect($this->generateUrl('bookingProduct'));
    }








    /**
     * Lists all Booking entities.
     *
     * @Route("/ajax/create/{product_id}", name="booking_product_new_ajax")
     * @Template()
     */
    public function createAjaxAction(Request $request, $product_id)
    {
        //This is optional. Do not do this check if you want to call the same action using a regular request.
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array('message' => 'You can access this only using Ajax!'), 400);
        }

        $product = $this->getDoctrine()->getRepository('WarehouseBundle:Product')->find($product_id);

        $bookingManager = $this->get('BookingManager');
        $bookingProduct = $bookingManager->createBookingProduct();

        $form = $this->createForm('WarehouseBundle\Form\BookingProductType',$bookingProduct);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $bookingProduct->setCreated(new \DateTime("now"));
            $booking = $bookingProduct->getBooking();
            $booking->setModified(new \DateTime('now'));

            # Persist and flush
            $bookingManager->updateBooking($booking,true);
            $bookingManager->updateBookingProduct($bookingProduct,true);

            //$data = $form->getData();
            $response['success'] = true;
            $response['message'] = 'Success!';
            $response['ajaxCommand'][] = array(
                'selector' => '.bulk_action tbody',
                'op' => 'append',
                'value' => $this->renderView('WarehouseBundle:BookingProduct:booking_product_row.html.twig',array('bookingProduct' => $bookingProduct)),
            );
            $response['ajaxCommand'][] = array(
                'selector' => '.bulk_action .remove',
                'op' => 'remove',
                'value' => '',
            );
            return new JsonResponse($response, 200);
        }

        return new JsonResponse(
            array(
                'success' => FALSE,
                'message' => 'Error',
                'errors' => $form->getErrors(),
                'form' => $this->renderView('WarehouseBundle:BookingProduct:ajax_booking_product_form.html.twig',
                    array(
                        'booking' => $product,
                        'entity' => $bookingProduct,
                        'form' => $form->createView(),
                    )
            )
        ), 400);
    }


    /**
     * Submission of Booking Product Picking Entities.
     *
     * @Route("/pick/ajax/{id}", name="booking_product_pick_ajax")
     * @Template()
     */
    public function formAjaxBookingProductPickForm(Request $request, BookingProduct $bookingProduct) {
	    $bookingManager = $this->get('BookingManager');

	    /** @var Form $form */
	    $form = $this->createFormBuilder($bookingProduct)
            ->setAction($this->generateUrl('booking_product_pick_ajax', array('id' => $bookingProduct->getId())))
            ->setMethod('POST')
            ->add('status', ChoiceType::class, array(
                'choices' => array_flip(\WarehouseBundle\Utils\Booking::bookingProductStatusList()),
                'choices_as_values' => true,
            ))
            ->add('location',CollectionType::class, array('mapped' => FALSE))
		    ->getForm();

        // Find locations
        $locations = $bookingProduct->getProduct()->getLocations();
        foreach($locations as $productLocation) {
            $form->get('location')->add($productLocation->getId(), IntegerType::class,[
                'label' => $productLocation->getLocation()->getAisle(). ' - '. $productLocation->getLocation()->getRow(). ' - '. $productLocation->getLocation()->getLevel(),
                'mapped'=>false,
                'data' => 0,
                'attr' => [
                    'min' => 0,
                    'max' => min($bookingProduct->getQty(),$productLocation->getOnHand()),
                    'class' => 'location_pick_qty',
                ],
            ]);
        }

        # Update the booking (products)
        $form->handleRequest($request);

        $response = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $booking = $bookingProduct->getBooking();
            $booking->setModified(new \DateTime('now'));

            # See if any picks were set
            $locations = isset($request->request->get('form')['location']) ? $request->request->get('form')['location'] : array();
            foreach($locations as $location_product_id => $qty_picked) {
                if (intval($qty_picked) > 0) {
                    $locationProduct = $this->getDoctrine()->getRepository('WarehouseBundle:LocationProduct')->find(intval($location_product_id));
                    # Create BookingProductLocation()
                    $bookingManager->createPick($bookingProduct,$locationProduct,$qty_picked);
                }
            }

            # Note Booking status is updated by a hidden field and jQuery
            $bookingManager->updateBookingProduct($bookingProduct,TRUE);
            $bookingManager->updateBooking($booking,TRUE);

            # Ajax response
            $response['success'] = true;
            $response['ajaxCommand'][] = array('selector'=>'[data-id="'.$bookingProduct->getId().'"] .bp-status','op'=>'html','value'=>$this->get('app.booking')->bookingProductStatusName($bookingProduct->getStatus()));
            $response['ajaxCommand'][] = array('selector'=>'[data-id="'.$bookingProduct->getId().'"] .bp-picked','op'=>'html','value'=>$this->get('app.booking')->bookingProductPickedQty($bookingProduct));
            $response['ajaxCommand'][] = array('selector'=>'[data-id="'.$bookingProduct->getId().'"] .bp-available','op'=>'html','value'=>$this->get('app.product')->getAvailableInternal($bookingProduct->getProduct()));
            $response['ajaxCommand'][] = array('selector'=>'#picking-modal','op'=>'modal','value'=>'hide');
            $response['ajaxCommand'][] = array('selector'=>'#date-modified .text-success','op'=>'html','value'=>$booking->getModified()->format('Y-m-d h:i A'));
        } elseif ($form->isSubmitted()) {
            $response['success'] = false;
            $response['ajaxCommand'][] = array(
                'selector' => '#scan-result',
                'op' => 'prepend',
                'value' => $this->renderView('WarehouseBundle::Scan/booking/error.html.twig', array('search' => $bookingProduct->getBooking()->getOrderNumber(),'error' => 'There was an issue trying to submit the form.')),
            );
        } else {
            //return $this->renderView('WarehouseBundle::BookingProduct/picking_form.html.twig', array('bookingProduct' => $bookingProduct,'form'=>$form->createView()));
            # Default form
            $response['success'] = true;
            $response['ajaxCommand'][] = array(
                'selector' => '#picking-result',
                'op' => 'html',
                'value' => $this->renderView('WarehouseBundle::BookingProduct/picking/picking_form.html.twig', array('bookingProduct' => $bookingProduct,'form'=>$form->createView())),
            );
        }
        return new JsonResponse($response, 200);
    }

}