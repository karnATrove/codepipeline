<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingComment;
use WarehouseBundle\Form\BookingCommentType;

#http://codemonkeys.be/2013/01/ajaxify-your-symfony2-forms-with-jquery/

/**
 * Booking controller.
 *
 * @Route("/comment")
 */
class BookingCommentController extends Controller
{


	/**
     * Lists all Booking entities.
     *
     * @Route("/new/{id}", name="comment_new")
     * @Template()
     */
    public function createAction(Request $request, Booking $booking)
    {
    	$bookingManager = $this->get('BookingManager');
    	$bookingComment = $bookingManager->createComment($booking);
    	$bookingComment->setUser($this->getUser());

        $commentForm = $this->createForm('WarehouseBundle\Form\BookingCommentType',$bookingComment,
        	array('action' => $this->generateUrl('comment_new',array('id'=>$booking->getId())),'method' => 'POST'));
        $commentForm->handleRequest($request);
        
        //if ( $request->isMethod( 'POST' ) ) {
		if (!$request->isXmlHttpRequest()) {
			# If not ajax request, return html form for booking edit page
	        return $this->render('WarehouseBundle:BookingComment:form_panel.html.twig', array(
	            'booking' => $booking,
	            'comment_form' => $commentForm->createView(),
	        ));
	    } elseif ($commentForm->isSubmitted() && $commentForm->isValid()) {
			$bookingComment->setCreated(new \DateTime("now"));

			# We want to save the modification date
			$booking->setModified(new \DateTime('now'));

			# Update booking and comment, persist and flush
			$bookingManager->updateBooking($booking,true);
			$bookingManager->updateComment($bookingComment,true);

			//$data = $commentForm->getData();
			$response['success'] = true;
			$response['message'] = 'Success!';
			$response['ajaxCommand'][] = array(
				'selector' => '#date-modified .value',
				'op' => 'html',
				'value' => $booking->getModified()->format('Y-m-d h:i A'),
			);
			$response['ajaxCommand'][] = array(
				'selector' => '.booking_comments',
				'op' => 'append',
				'value' => '<li>'.$this->renderView('WarehouseBundle:BookingComment:comment.html.twig',array('comment' => $bookingComment)).'</li>',
			);
			$response['ajaxCommand'][] = array(
				'selector' => '.booking_comments .remove',
				'op' => 'remove',
				'value' => '',
			);
			return new JsonResponse($response, 200);
		}
		//}
        return new JsonResponse(
            array(
            	'success' => FALSE,
		        'message' => 'Error',
		        'form' => $this->renderView('AcmeDemoBundle:Demo:form.html.twig',
                	array(
			            'entity' => $entity,
			            'form' => $form->createView(),
        			)
        	)
		), 400);
    }


}