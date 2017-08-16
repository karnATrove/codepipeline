<?php

namespace WarehouseApiBundle\Workflow;


use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerBuilder;
use Rove\CanonicalDto\Booking\BookingContactDto;
use Rove\CanonicalDto\Booking\BookingDto;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WarehouseApiBundle\Exception\ApiException;
use WarehouseApiBundle\Mapper\Booking\BookingCommentMapper;
use WarehouseApiBundle\Mapper\Booking\BookingContactMapper;
use WarehouseApiBundle\Mapper\Booking\BookingItemMapper;
use WarehouseApiBundle\Mapper\Booking\BookingMapper;
use WarehouseBundle\Entity\BookingContact;
use WarehouseBundle\Manager\BookingContactManager;
use WarehouseBundle\Model\Booking\BookingSearchModel;
use WarehouseBundle\Manager\ProductManager;
use WarehouseBundle\Manager\BookingManager;

class BookingWorkflow extends BaseWorkflow
{
	private $bookingManager;
	private $productManager;
	private $bookingContactManger;

	/**
	 * BookingWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->bookingManager = $container->get(BookingManager::class);
		$this->productManager = $container->get(ProductManager::class);
		$this->bookingContactManger = $container->get(BookingContactManager::class);

	}

	/**
	 * @param Request $request
	 *
	 * @return View
	 */
	public function getBookingsView(Request $request)
	{
		$criteria = $this->buildBookingSearchCriteria($request);
        $bookingSearchModel = new BookingSearchModel();
        $bookingSearchModel->setCriteria($criteria);
		$orderBy = $this->buildBookingSearchOrder($request);
		$limit = empty($request->get('limit')) ? 50 : $request->get('limit');;
		$page = empty($request->get('page')) ? 1 : $request->get('page');

		$total = $this->bookingManager->count($bookingSearchModel);
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

	/**
	 * Create booking
	 *
	 * @param Request $request
	 *
	 * @throws \WarehouseApiBundle\Exception\ApiException
	 */
	public function createBooking(Request $request)
	{

		$data = $request->getContent();
		/** @var BookingDto $bookingDto */
		$bookingDto = SerializerBuilder::create()->build()->deserialize($data, BookingDto::class, 'json');
		$activeBookingStatuses = array_keys($this->container->get('app.booking')->bookingStatusList(TRUE, TRUE));

		## check if booking exists
		$booking = $this->bookingManager->findBy(['orderReference' => $bookingDto->getOrderReference(), 'status' => $activeBookingStatuses], null, 1);
		if (!empty($booking)) {
			throw new ApiException("Booking {$bookingDto->getOrderReference()} already exists.", Response::HTTP_BAD_REQUEST);
		}

		## check if missing any booking product or product out of stock.
		/** @var \Rove\CanonicalDto\Booking\BookingItemDto $item */
		foreach ($bookingDto->getBookingItems() as $item) {
			//check product existence, create if not exist
			$product = $this->productManager->getOneBySku($item->getSku());
			if (!$product) {
				// if not exists, try to create missing product, but still throw exception next, as missing product stock must be empty.
				$product = $this->createMissingProduct($item->getSku());
				$this->productManager->updateProduct($product, null);// we want to save this no matter what
				throw new ApiException("Booking {$bookingDto->getOrderReference()} product {$item->getSku()} is not available.", Response::HTTP_BAD_REQUEST);
			} else {
				$available = $this->productManager->getProductAvailableQuantity($item->getSku());
				if ($available < $item->getQuantity()) {
					throw new ApiException("Booking {$bookingDto->getOrderReference()} product {$item->getSku()} is not available.", Response::HTTP_BAD_REQUEST);
				}
			}
		}


		// create booking
		$booking = $this->createBookingFromDto($bookingDto);

		// create booking product
		if (!empty($bookingDto->getBookingItems())) {
			foreach ($bookingDto->getBookingItems() as $item) {
				$bookingProduct = BookingItemMapper::mapDtoToEntity($item, $this->productManager);
				$bookingProduct->setBooking($booking);
				$booking->addProduct($bookingProduct);
			}
		}
		// create booking comment
		if (!empty($bookingDto->getBookingComments())) {
			foreach ($bookingDto->getBookingComments() as $bookingCommentDto) {
				$bookingComment = BookingCommentMapper::mapDtoToEntity($bookingCommentDto);
				$bookingComment->setBooking($booking);
				$booking->addComment($bookingComment);
			}
		}

		// create booking contacts but wont set the default
		if (!empty($bookingDto->getBookingContacts())) {
			// only grab the first one
			$bookingContactDto = $bookingDto->getBookingContacts()[0];
			$bookingContact = $this->createBookingContactFromDto($bookingContactDto);
			$bookingContact->setBooking($booking);
			$booking->setContact($bookingContact);
		}

		$this->bookingManager->updateBooking($booking, $this->entityManager, FALSE); // wont flush
		$this->entityManager->flush();
	}

	/**
	 * Create booking from bookingDto
	 *
	 * @param \Rove\CanonicalDto\Booking\BookingDto $bookingDto
	 *
	 * @return \WarehouseBundle\Entity\Booking
	 */
	public function createBookingFromDto(BookingDto $bookingDto)
	{
		$bookingEntity = BookingMapper::mapDtoToEntity($bookingDto, $this->entityManager);
		$bookingEntity->setCreated(new \DateTime('now'));
		$bookingEntity->setModified(new \DateTime('now'));
		return $bookingEntity;
	}

	/**
	 * @param BookingContactDto $bookingContactDto
	 *
	 * @return BookingContact
	 */
	public function createBookingContactFromDto(BookingContactDto $bookingContactDto)
	{
		$bookingContactEntity = BookingContactMapper::mapDtoToEntity($bookingContactDto);
		return $bookingContactEntity;
	}

}