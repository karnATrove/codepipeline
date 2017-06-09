<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-07
 * Time: 3:22 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingStatus;
use WarehouseBundle\Entity\IncomingType;

class IncomingTypeManager extends BaseManager
{

	private $incomingTypeRepository;

	/**
	 * IncomingProductScanManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		parent::__construct($entityManager);
		$this->incomingTypeRepository = $entityManager->getRepository('WarehouseBundle:IncomingType');
	}

	/**
	 * can change to read from db
	 *
	 * @return array
	 */
	public static function incomingTypeList()
	{
		return [
			IncomingType::OCEAN_FREIGHT => 'Ocean Freight',
			IncomingType::FORWARD => 'Forward',
		];
	}

}