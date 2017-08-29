<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\LocationProduct;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\User;
use WarehouseBundle\Repository\LocationProductRepository;

class LocationProductManager extends BaseManager
{

	/**
	 * LocationProductManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, LocationProduct::class);
	}

	/**
	 * @param Product  $product
	 * @param Location $location
	 *
	 * @return LocationProduct
	 */
	public function findOneByProductAndLocation(Product $product, Location $location)
	{
		/** @var LocationProductRepository $repo */
		$repo = $this->entityRepository;
		return $repo->findOneByProductAndLocation($product, $location);
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
	 * @param Location                    $location
	 * @param Product                     $product
	 * @param int                         $onHand
	 * @param int                         $staged
	 * @param EntityManagerInterface|null $entityManager
	 * @param null                        $user
	 */
	public function create(Location $location, Product $product, $onHand = 0, $staged = 0, EntityManagerInterface $entityManager = null, $user = null)
	{
		$flush = $entityManager ? false : true;
		$locationProduct = new LocationProduct();
		$locationProduct->setLocation($location)
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