<?php
namespace WarehouseBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\Model\BookingInterface;
use WarehouseBundle\Model\BookingManager as BaseBookingManager;

use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Entity\BookingComment;
use WarehouseBundle\Entity\BookingFile;
use WarehouseBundle\Entity\LocationProduct;

class BookingManager extends BaseBookingManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param ObjectManager            $om
     */
    public function __construct(ObjectManager $om, ContainerInterface $container)
    {
        parent::__construct();

        $this->objectManager = $om;
        $this->container = $container;
        $this->repository = $om->getRepository('WarehouseBundle\Entity\Booking');

        $metadata = $om->getClassMetadata('WarehouseBundle\Entity\Booking');
    }

    /**
     * {@inheritdoc}
     */
    public function logEntry($note) {
        return TRUE;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBooking(BookingInterface $booking)
    {
        $this->objectManager->remove($booking);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBookingProduct(BookingProduct $bookingProduct)
    {
        $this->objectManager->remove($bookingProduct);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findBookingBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findBookings()
    {
        return $this->repository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function reloadBooking(BookingInterface $booking)
    {
        $this->objectManager->refresh($booking);
    }

    /**
     * {@inheritdoc}
     */
    public function updateBooking(BookingInterface $booking, $andFlush = true)
    {
        $this->objectManager->persist($booking);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateBookingProduct(BookingProduct $bookingProduct, $andFlush = true)
    {
        $this->objectManager->persist($bookingProduct);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateFile(BookingFile $bookingFile, $andFlush = true)
    {
        $this->objectManager->persist($bookingFile);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateComment(BookingComment $bookingComment, $andFlush = true)
    {
        $this->objectManager->persist($bookingComment);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * Creates a pick, reduces location quantity.
     *
     * @param      \WarehouseBundle\Entity\BookingProduct   $bookingProduct   The booking product
     * @param      \WarehouseBundle\Entity\LocationProduct  $locationProduct  The location product
     * @param      integer                                  $qty_picked       The qty picked
     *
     * @return     <type>                                   ( description_of_the_return_value )
     */
    public function createPick(BookingProduct $bookingProduct, LocationProduct $locationProduct, $qty_picked=0) {
        $bookingProductLocation = (new \WarehouseBundle\Entity\BookingProductLocation())
            ->setLocation($locationProduct->getLocation())
            ->setBookingProduct($bookingProduct)
            ->setQty($qty_picked)
            ->setUser($this->container->get('security.token_storage')->getToken()->getUser());
        $this->objectManager->persist($bookingProductLocation);
        $this->objectManager->flush();

        # Reduce the location quantity
        $em = $this->container->get('doctrine.orm.entity_manager');
        if ($locationProduct->getOnHand() - $qty_picked <= 0) {
            # Remove empty location product
            $em->remove($locationProduct);
        } else {
            # Update location product
            $locationProduct->setOnHand($locationProduct->getOnHand() - $qty_picked);
            $locationProduct->setStaged($locationProduct->getStaged());
            $em->persist($locationProduct);
        }
        $em->flush();
        
        return $bookingProductLocation;
    }

    /**
     * Create a booking file from the manager.
     * @param  Booking|null $booking [description]
     * @return [type]                [description]
     */
    public function createFile(Booking $booking = null) {
        $bookingFile = new \WarehouseBundle\Entity\BookingFile();
        $bookingFile->setBooking($booking);

        return $bookingFile;
    }

    /**
     * Create a booking comment from the manager.
     * @param  Booking|null $booking [description]
     * @return [type]                [description]
     */
    public function createComment(Booking $booking = null) {
        $bookingComment = new \WarehouseBundle\Entity\BookingComment();
        $bookingComment->setBooking($booking);

        return $bookingComment;
    }

    /**
     * Create a booking product from the manager.
     * @param  Booking|null $booking [description]
     * @return [type]                [description]
     */
    public function createBookingProduct(Booking $booking = null) {
        $bookingProduct = new \WarehouseBundle\Entity\BookingProduct();
        $bookingProduct->setBooking($booking);

        return $bookingProduct;
    }
}
