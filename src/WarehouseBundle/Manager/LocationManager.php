<?php

namespace WarehouseBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Location;

class LocationManager extends BaseManager
{

	/**
	 * LocationManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager, Location::class);
	}

	/**
	 * @param Location[] $locations
	 *
	 * @return array
	 */
	public static function toArray($locations)
	{
		$resp = [];
		foreach ($locations as $location) {
			$resp[] = ['id' => $location->getId(), 'value' => $location->printLocation()];
		}
		return $resp;
	}

	/**
	 * @return Location[]
	 */
	public function getLocations()
	{
		return $this->entityRepository->findAll();
	}

	/**
	 * @param $id
	 *
	 * @return null|object|Location
	 */
	public function findById($id)
	{
		return $this->entityRepository->find($id);
	}

}