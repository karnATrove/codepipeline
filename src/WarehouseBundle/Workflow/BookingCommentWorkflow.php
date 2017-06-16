<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-13
 * Time: 4:12 PM
 */

namespace WarehouseBundle\Workflow;


use Rove\CanonicalDto\Order\OrderCommentCreateDto;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use WarehouseBundle\DTO\AjaxResponse\AjaxCommandDTO;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingComment;
use WarehouseBundle\Entity\User;
use WarehouseBundle\Exception\WorkflowException\WorkflowException;
use WarehouseBundle\Form\BookingCommentType;

class BookingCommentWorkflow extends BaseWorkflow
{
	private $formBuilder;
	private $router;
	/** @var User $user */
	private $user;
	private $bookingCommentManager;
	private $bookingManager;
	private $templating;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->bookingCommentManager = $this->container->get('warehouse.manager.booking_comment_manager');
		$this->bookingManager = $this->container->get('warehouse.manager.booking_manager');
		$this->formBuilder = $container->get('form.factory');
		$this->router = $container->get('router');
		$this->user = $this->container->get('security.token_storage')->getToken()->getUser();
		$this->templating = $container->get('templating');
	}

	public function create(Request $request, Booking $booking)
	{
		if (!$this->user) {
			throw new WorkflowException("Failed! Can not identify user. Please refresh page and try again.");
		}

		if (!$this->user){
			throw new WorkflowException("Failed! Can not identify user. Please refresh page and try again.");
		}

		$bookingComment = new BookingComment();
		$commentForm = $this->generateCreateForm($booking, $bookingComment);
		$commentForm->handleRequest($request);

		if (!$commentForm->isSubmitted() || !$commentForm->isValid()) {
			throw new WorkflowException("Failed! Comment form not valid.");
		}

		if ($commentForm->get('notifyCustomerCare')->getData()==1){
			$this->sendCommentToRove($bookingComment->getComment(),$booking->getOrderNumber());
		}

		$bookingComment->setCreated(new \DateTime());
		$bookingComment->setBooking($booking);
		$bookingComment->setUser($this->user);
		$this->bookingCommentManager->update($bookingComment, $this->entityManager);

		$booking->setModified(new \DateTime());
		$this->bookingManager->updateBooking($booking, $this->entityManager);
		$this->entityManager->flush();

		$messages['success'][] = "Comment created.";
		$this->container->get('warehouse.utils.message_printer')->printToFlashBag($messages);
		$ajaxCommands[] = new AjaxCommandDTO('.booking-comment-message-bag-container',
			AjaxCommandDTO::OP_HTML, $this->getMessageBagView());

		$ajaxCommands[] = new AjaxCommandDTO('#date-modified .value', AjaxCommandDTO::OP_HTML,
			$booking->getModified()->format('Y-m-d h:i A'));
		$ajaxCommands[] = new AjaxCommandDTO('.booking_comments', AjaxCommandDTO::OP_APPEND,
			'<li>' . $this->templating->render('booking_comment/comment.html.twig',
				['comment' => $bookingComment]) . '</li>');
		$ajaxCommands[] = new AjaxCommandDTO('.booking_comments .remove', AjaxCommandDTO::OP_REMOVE);
		return $ajaxCommands;
	}

	/**
	 * @param $comment
	 * @param $orderNumber
	 */
	private function sendCommentToRove($comment, $orderNumber){
		$orderCommentCreateDto = new OrderCommentCreateDto();
		$orderCommentCreateDto->setOrderNumber($orderNumber);
		$orderCommentCreateDto->setNotifyCustomerCare(true);
		$orderCommentCreateDto->setComment($comment);
		$orderCommentCreateDto->setCreatedBy($this->user->getEmail());
		$this->container->get('rove_site_rest_api.manager.order_comment_manager')->create($orderCommentCreateDto);
	}

	/**
	 * @param Booking             $booking
	 * @param BookingComment|null $bookingComment
	 *
	 * @return \Symfony\Component\Form\FormInterface
	 */
	public function generateCreateForm(Booking $booking, BookingComment $bookingComment = null)
	{
		$bookingComment = $bookingComment ? $bookingComment : new BookingComment();
		return $this->formBuilder->create(BookingCommentType::class, $bookingComment,
			['action' => $this->router->generate('booking_comment_new',
				['id' => $booking->getId()]), 'method' => 'POST']);
	}

	/**
	 * @return mixed
	 */
	public function getMessageBagView()
	{
		return $this->templating->render("booking_comment/_message_bag.html.twig");
	}
}