<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-07
 * Time: 8:45 AM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\User;

class UserManager
{
	private $entityManager;
	private $userRepository;

	/**
	 * UserManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->userRepository = $entityManager->getRepository('WarehouseBundle:User');
	}

	/**
	 * @return array|User[]
	 */
	public function getAllUsers(){
		return $this->userRepository->findAll();
	}
}