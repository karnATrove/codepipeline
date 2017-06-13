<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 11:58 AM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Location;

class LocationManager
{
	private $entityManager;
	private $locationRepository;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->locationRepository = $entityManager->getRepository('WarehouseBundle:Location');
	}

	/**
	 * @param Location[] $locations
	 */
	public static function toArray($locations)
	{
		$resp = [];
		foreach ($locations as $location) {
			$resp[$location->getId()] = $location->printLocation();
		}
		return $resp;
	}

	/**
	 * @return array|\WarehouseBundle\Entity\Location[]
	 */
	public function getLocations()
	{
		return $this->locationRepository->findAll();
	}

	/**
	 * @param $id
	 *
	 * @return null|object|Location
	 */
	public function findById($id){
		return $this->locationRepository->find($id);
	}

}