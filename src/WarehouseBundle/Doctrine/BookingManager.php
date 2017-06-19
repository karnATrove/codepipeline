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
        $em = $this->container->get('doctrine.orm.entity_manager');

        $bookingProductLocation = (new \WarehouseBundle\Entity\BookingProductLocation())
            ->setLocation($locationProduct->getLocation())
            ->setBookingProduct($bookingProduct)
            ->setQty($qty_picked)
            ->setUser($this->container->get('security.token_storage')->getToken()->getUser());
        $em->persist($bookingProductLocation);

         # If this is a staging location, we want to deduct 'staged' from other locations
        if ($locationProduct->getLocation()->getStaging()) {
            # Reduce staged from product location
            # TODO: Redo the staging storage with a new table and remove 'staged' field
            // Find a location product of same product with staging > 0
            // This is a hack and should be replaced
            // This will loop through each location product (rack) and deduct as many picked as required
            $qty = $qty_picked;
            $pickLocations = $em->getRepository('WarehouseBundle:LocationProduct')->findByStagedProduct($locationProduct->getProduct());
            foreach($pickLocations as $l) {
                $qty_reduce = min($l->getStaged(),$qty);
                $l->setStaged($l->getStaged() - $qty_reduce);
                $l->setModified(new \DateTime());
                $em->persist($l);
                $qty -= $qty_reduce;
                if ($qty <= 0) break;
            }
        } else {
            $locationProduct->setStaged($locationProduct->getStaged());
        }

        # Reduce the location quantity
        if ($locationProduct->getOnHand() - $qty_picked <= 0 && !$locationProduct->getLocation()->getStaging()) {
            # Remove empty location product
            $em->remove($locationProduct);
        } else {
            # Update location product
            $locationProduct->setOnHand($locationProduct->getOnHand() - $qty_picked);
            
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
