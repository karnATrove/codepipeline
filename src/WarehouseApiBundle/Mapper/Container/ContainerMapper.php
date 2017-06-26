<?php

namespace WarehouseApiBundle\Mapper\Container;


use Doctrine\ORM\EntityManagerInterface;
use Rove\CanonicalDto\Container\ContainerDto;
use WarehouseApiBundle\Exception\MapperException;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Manager\IncomingStatusManager;
use WarehouseBundle\Manager\IncomingTypeManager;

class ContainerMapper
{
	/**
	 * @param ContainerDto           $containerDto
	 * @param EntityManagerInterface $entityManager
	 *
	 * @return Incoming
	 * @throws MapperException
	 */
	public static function mapDtoToEntity(ContainerDto $containerDto, EntityManagerInterface $entityManager)
	{
		$incoming = new Incoming();
		$incoming->setName($containerDto->getName());
		$incomingTypeManager = new IncomingTypeManager($entityManager);
		$typeCode = $containerDto->getTypeCode();
		$incomingType = $incomingTypeManager->findOneBy(['code' => $typeCode]);
		if (!$incomingType) {
			throw new MapperException("Failed to container type with code {$typeCode}");
		}
		$incoming->setType($incomingType);
		$incoming->setEta($containerDto->getEta());
		$incoming->setScheduled($containerDto->getScheduledArrivalTime());
		$incoming->setArrived($containerDto->getArrivalTime());
		$statusCode = $containerDto->getStatusCode();
		$incomingStatusManager = new IncomingStatusManager($entityManager);
		$incomingStatus = $incomingStatusManager->findOneBy(['code' => $statusCode]);
		if (!$incomingStatus) {
			throw new MapperException("Failed to container status with code {$statusCode}");
		}
		$incoming->setStatus($incomingStatus);
		return $incoming;
	}
}