<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-07
 * Time: 9:28 AM
 */

namespace WarehouseBundle\Workflow;


use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use WarehouseBundle\Entity\User;
use WarehouseBundle\Manager\UserGroupManager;

class UserWorkflow
{
	private $container;
	/** @var UserManager $fosUserManager */
	private $fosUserManager;
	private $userManager;
	private $userGroupManager;
	private $entityManager;

	/**
	 * UserWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->fosUserManager = $container->get('fos_user.user_manager');
		$this->userManager = $container->get('warehouse.manager.user_manager');
		$this->userGroupManager = $container->get('warehouse.manager.user_group_manager');
		$this->entityManager = $container->get('doctrine.orm.entity_manager');
	}

	/**
	 * @param User $user
	 *
	 * @return \Symfony\Component\Form\FormInterface
	 */
	public function makeUserCreateEditForm(User $user)
	{
		$userGroups = $this->container->get('warehouse.manager.user_group_manager')->getAllUserGroups();
		$selection = UserGroupManager::makeUserGroupListForForm($userGroups);
		$selected = UserGroupManager::makeSelectedUserGroupListForForm($user);
		$formBuilder = $this->container->get('form.factory')->createBuilder(FormType::class, $user);
		$formBuilder->add('username', TextType::class)
			->add('email', TextType::class)
			->add('name', TextType::class)
			->add('plainPassword', PasswordType::class, ['required' => false])
			->add('userGroup', ChoiceType::class, [
				'choices' => $selection,
				'placeholder' => 'Please Select',
				'mapped' => false,
				'multiple' => true,
				'data' => $selected,
				'required' => false
			]);
		return $formBuilder->getForm();
	}

	/**
	 * @param $user
	 * @param $userGroupIds
	 */
	public function updateUser($user, $userGroupIds)
	{
		$this->fosUserManager->updateUser($user);
		$this->updateUserGroupForUser($user, $userGroupIds);
	}

	/**
	 * @param User $user
	 * @param      $userGroupIds
	 */
	private function updateUserGroupForUser($user, $userGroupIds)
	{
		$existingUserGroups = $user->getGroups();
		foreach ($existingUserGroups as $existingUserGroup) {
			if (!in_array($existingUserGroup->getId(), $userGroupIds)) {
				$user->removeGroup($existingUserGroup);
				$this->userManager->updateUser($user, $this->entityManager);
			} else {
				if (($key = array_search($existingUserGroup->getId(), $userGroupIds)) !== false) {
					unset($userGroupIds[$key]);
				}
			}
		}
		foreach ($userGroupIds as $userGroupId) {
			$userGroup = $this->userGroupManager->getUserGroupById($userGroupId);
			$user->addGroup($userGroup);
			$this->userManager->updateUser($user, $this->entityManager);
		}
		$this->entityManager->flush();
	}
}