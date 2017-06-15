<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 4:30 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\LocationProduct;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\User;

class LocationProductManager extends BaseManager
{
	private $locationProductRepository;

	/**
	 * LocationProductManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->locationProductRepository = $entityManager->getRepository('WarehouseBundle:LocationProduct');
	}

	/**
	 * @param Product  $product
	 * @param Location $location
	 *
	 * @return LocationProduct
	 */
	public function findOneByProductAndLocation(Product $product, Location $location)
	{
		return $this->locationProductRepository->findOneByProductAndLocation($product, $location);
	}

	/**
	 * @param IncomingProductScan         $incomingProductScan
	 * @param null|EntityManagerInterface $entityManager
	 * @param null|User                   $user
	 */
	public function createLocationProductByIncomingProductScan(IncomingProductScan $incomingProductScan, $entityManager = null, $user = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$locationProduct = new LocationProduct();
		$locationProduct->setProduct($incomingProductScan->getProduct());
		$locationProduct->setLocation($incomingProductScan->getLocation());
		$locationProduct->setOnHand($incomingProductScan->getQtyOnScan());
		$locationProduct->setStaged(0);
		$locationProduct->setCreated(new \DateTime('now'));
		$locationProduct->setModified(new \DateTime());
		if ($user) {
			$locationProduct->setUser($user);
		}
		$entityManager->persist($locationProduct);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * Create a LocationProduct
	 * @param  Location $location      [description]
	 * @param  Product  $product       [description]
	 * @param  integer  $onHand        [description]
	 * @param  integer  $staged        [description]
	 * @param  [type]   $entityManager [description]
	 * @param  [type]   $user          [description]
	 * @return [type]                  [description]
	 */
	public function create(Location $location, Product $product, $onHand = 0, $staged = 0, $entityManager = null, $user = null) {
		$flush = $entityManager ? false : true;
		$locationProduct = (new LocationProduct())
			->setLocation($location)
			->setProduct($product)
			->setOnHand($onHand)
			->setStaged($staged)
			->setCreated(new \DateTime());
		if ($user) {
			$locationProduct->setUser($user);
		}
		$entityManager->persist($locationProduct);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * @param LocationProduct             $locationProduct
	 * @param null|EntityManagerInterface $entityManager
	 * @param null|User                   $user
	 */
	public function updateLocationProduct(LocationProduct $locationProduct, $entityManager = null, $user = null)
	{
		$flush = $entityManager ? false : true;
		$entityManager = $entityManager ? $entityManager : $this->entityManager;
		$locationProduct->setModified(new \DateTime());
		if ($user) {
			$locationProduct->setUser($user);
		}
		$entityManager->persist($locationProduct);
		if ($flush) {
			$entityManager->flush();
		}
	}
}