<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use Timestampable\Fixture\Document\Book;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Utils\Booking as BookingUtility;
use WarehouseBundle\Entity\BookingFile;
use WarehouseBundle\Entity\BookingLog;
use WarehouseBundle\Form\BookingFileType;

#http://codemonkeys.be/2013/01/ajaxify-your-symfony2-forms-with-jquery/

/**
 * Booking controller.
 *
 * @Route("/file")
 */
class BookingFileController extends Controller
{
	/**
	 * Lists all Booking entities.
	 *
	 * @Route("/new/{id}", name="file_new")
	 * @param Request $request
	 * @param Booking $booking
	 * @return JsonResponse|string
	 */
	public function createAction(Request $request, Booking $booking)
	{
		//This is optional. Do not do this check if you want to call the same action using a regular request.
		$bookingManager = $this->get('BookingManager');
		$bookingFile = $bookingManager->createFile($booking);

		$form = $this->createForm('WarehouseBundle\Form\BookingFileType', $bookingFile,
			array('action' => $this->generateUrl('file_new', array('id' => $booking->getId())), 'method' => 'POST'));
		$form->handleRequest($request);

		$defaultBol = BookingUtility::getDefaultBookingBol($booking);
		$defaultLabel = BookingUtility::getDefaultBookingLabel($booking);
		if (!$request->isXmlHttpRequest()) {
			// If not ajax request, return the actual form on the booking edit page
			return $this->render('WarehouseBundle:BookingFile:form_panel.html.twig', array(
				'booking' => $booking,
				'file_form' => $form->createView(),
				'bol' => $defaultBol,
				'label' => $defaultLabel
			));
		}

		if ($form->isSubmitted() && $form->isValid()) {
			// $file stores the uploaded PDF file
			/** @var UploadedFile $file */
			$file = $bookingFile->getFilepath();

			$fileName = $this->get('app.document_uploader')->upload($file);

			// Update the 'document' property to store the PDF file name
			// instead of its contents
			$bookingFile->setFilepath($fileName);
			$bookingFile->setCreated(new \DateTime("now"));

			# We want to save the modification date
			$booking->setModified(new \DateTime('now'));

			# Save the file and persist,flush
			$bookingManager->updateBooking($booking, true);
			$bookingManager->updateFile($bookingFile, true);

			//add booking log
			$em = $this->getDoctrine()->getManager();
			$bookingLog = new BookingLog();
			$bookingLog->setBooking($booking)
				->setUser($this->getUser())
				->setCreated(new \DateTime('now'))
				->setNote('Booking file uploaded.');
			$em->persist($bookingLog);

			$em->flush();

			//$data = $form->getData();
			$response['success'] = true;
			$response['message'] = 'Success!';
			$response['ajaxCommand'][] = array(
				'selector' => '#date-modified .value',
				'op' => 'html',
				'value' => $booking->getModified()->format('Y-m-d h:i A'),
			);
			$response['ajaxCommand'][] = array(
				'selector' => '.project_files',
				'op' => 'append',
				'value' => '<li>' . $this->renderView('WarehouseBundle:BookingFile:file.html.twig', array('file' => $bookingFile)) . '</li>',
			);
			$response['ajaxCommand'][] = array(
				'selector' => '.project_files .remove',
				'op' => 'remove',
				'value' => '',
			);
			$response['ajaxCommand'][] = array(
				'selector' => '.log_booking tbody',
				'op' => 'prepend',
				'value' => $this->renderView('WarehouseBundle:BookingLog:booking_log_row.html.twig',
					array('log' => $bookingLog)),
			);
			return new JsonResponse($response, 200);
		}

		return new JsonResponse(
			array(
				'success' => FALSE,
				'message' => 'Error',
				'errors' => $form->getErrors(),
				'form' => $this->renderView('WarehouseBundle:BookingFile:ajax_file_form.html.twig',
					array(
						'booking' => $booking,
						'entity' => $bookingFile,
						'form' => $form->createView(),
					)
				),
			), 400);
	}


}