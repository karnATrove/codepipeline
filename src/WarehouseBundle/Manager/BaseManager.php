<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 1:43 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\IEntity;

class BaseManager
{
	protected $entityManager;

	protected $entityRepository;

	/**
	 * BaseEntityManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 * @param                        $entityClassName
	 *
	 */
	public function __construct(EntityManagerInterface $entityManager, string $entityClassName)
	{
		$this->entityManager = $entityManager;
		$this->entityRepository = $entityManager->getRepository($entityClassName);
	}

	/**
	 * @param IEntity                $entity
	 * @param EntityManagerInterface $entityManager
	 */
	public function update(IEntity $entity, EntityManagerInterface $entityManager = null)
	{
		$flush = $entityManager == null;
		$entityManager = $entityManager ?? $this->entityManager;
		$entityManager->persist($entity);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * @param IEntity                $entity
	 * @param EntityManagerInterface $entityManager
	 */
	public function remove(IEntity $entity, EntityManagerInterface $entityManager = null)
	{
		$flush = $entityManager == null;
		$entityManager = $entityManager ?? $this->entityManager;
		$entityManager->remove($entity);
		if ($flush) {
			$entityManager->flush();
		}
	}

	/**
	 * @return array
	 */
	public function findAll()
	{
		return $this->entityRepository->findAll();
	}

	/**
	 * @param $id
	 *
	 * @return null|object
	 */
	public function find($id)
	{
		return $this->entityRepository->find($id);
	}

	/**
	 * @param $criteria
	 * @param $orderBy
	 * @param $limit
	 * @param $offset
	 *
	 * @return array
	 */
	public function findBy($criteria, $orderBy = null, $limit = null, $offset = null)
	{
		return $this->entityRepository->findBy($criteria, $orderBy, $limit, $offset);
	}

	/**
	 * @param $criteria
	 *
	 * @return null|object
	 */
	public function findOneBy($criteria)
	{
		return $this->entityRepository->findOneBy($criteria);
	}

	/**
	 * @param IEntity $entity
	 *
	 * @return IEntity
	 */
	public function refresh(IEntity $entity)
	{
		$this->entityManager->refresh($entity);
		return $entity;
	}
}