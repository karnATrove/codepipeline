<?php

namespace WarehouseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WarehouseBundle\DTO\AjaxResponse\AjaxCommandDTO;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Utils\AjaxCommandParser;

/**
 * Booking controller.
 *
 * @Route("/booking-comment")
 */
class BookingCommentController extends Controller
{


	/**
	 * Lists all Booking entities.
	 *
	 * @Route("/new/{id}", name="booking_comment_new")
	 */
	public function createAction(Request $request, Booking $booking)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error',
				"Create comment only allow via AJAX.");
			return $this->redirect($this->generateUrl('booking_edit',
				['id' => $booking->getId()]));
		}

		try {
			$ajaxCommands = $this->container->get('warehouse.workflow.booking_comment_workflow')
				->create($request, $booking);
		} catch (\Exception $exception) {
			$messages['error'][] = "Error: {$exception->getMessage()}";
			$this->get('warehouse.utils.message_printer')->printToFlashBag($messages);
			$ajaxCommands[] = new AjaxCommandDTO('.booking-comment-message-bag-container',
				AjaxCommandDTO::OP_HTML, $this->get('warehouse.workflow.booking_comment_workflow')
					->getMessageBagView());
		}
		$response = AjaxCommandParser::parseAjaxCommands($ajaxCommands);
		return new JsonResponse($response, JsonResponse::HTTP_OK);
	}

	/**
	 * Lists all Booking entities.
	 *
	 * @Route("/view/{id}", name="booking_comment_view")
	 */
	public function viewAction(Booking $booking)
	{
		$commentForm = $this->get('warehouse.workflow.booking_comment_workflow')->generateCreateForm($booking);
		return $this->render('booking_comment/form_panel.html.twig', [
			'booking' => $booking,
			'comment_form' => $commentForm->createView(),
		]);
	}
}