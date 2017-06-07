<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-07
 * Time: 12:49 PM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use WarehouseBundle\Entity\User;
use WarehouseBundle\Entity\UserGroup;

class UserGroupManager
{
	private $entityManager;
	private $userGroupRepository;

	/**
	 * UserGroupManager constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->userGroupRepository = $entityManager->getRepository('WarehouseBundle:UserGroup');
	}

	/**
	 * @param UserGroup[] $userGroups
	 *
	 * @return array
	 */
	public static function makeUserGroupListForForm($userGroups)
	{
		$selection = [];
		foreach ($userGroups as $userGroup) {
			$selection[$userGroup->getName()] = $userGroup->getId();
		}
		return $selection;
	}


	/**
	 * @param User $user
	 *
	 * @return array
	 */
	public static function makeSelectedUserGroupListForForm($user)
	{
		$selected = [];
		foreach ($user->getGroups() as $group) {
			$selected[] = $group->getId();
		}
		return $selected;
	}

	/**
	 * @return array|UserGroup[]
	 */
	public function getAllUserGroups()
	{
		return $this->userGroupRepository->findAll();
	}

	/**
	 * @param $id
	 *
	 * @return null|object|UserGroup
	 */
	public function getUserGroupById($id){
		return $this->userGroupRepository->find($id);
	}

}