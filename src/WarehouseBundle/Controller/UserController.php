<?php

namespace WarehouseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;
use WarehouseBundle\Entity\User;
use WarehouseBundle\Manager\UserManager;
use WarehouseBundle\Workflow\UserWorkflow;

/**
 * User controller.
 *
 * @Route("/user")
 * @Security("has_role('ROLE_ADMIN')")
 */
class UserController extends Controller
{
	/**
	 * @Route("/", name="user_list")
	 */
	public function indexAction(Request $request)
	{
		$users = $this->container->get(UserManager::class)->getAllUsers();
		return $this->render('WarehouseBundle::User/user_list.html.twig', [
			'users' => $users
		]);
	}

	/**
	 * @Route("/users/view/{id}", name="user_view")
	 */
	public function viewAction(User $user)
	{
		if (!$user) {
			throw new Exception('not found');
		}
		return $this->render('WarehouseBundle::User/user_view.html.twig', [
			'user' => $user,
		]);
	}

	/**
	 * @Route("/createUser", name="user_create")
	 */
	public function createUserAction(Request $request)
	{
		$userManager = $this->get('fos_user.user_manager');
		$user = $userManager->createUser();
		$user->setEnabled(true);
		$form = $this->get(UserWorkflow::class)->makeUserCreateEditForm($user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$userGroupIds = $form->get('userGroup')->getData();
			$this->get(UserWorkflow::class)->updateUser($user, $userGroupIds);
			return $this->redirectToRoute('user_view', ['id' => $user->getId()]);
		}
		return $this->render('WarehouseBundle::User/user_create.html.twig', [
			'form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/{id}/editUser", name="user_edit")
	 */
	public function editUserAction(Request $request, $id)
	{
		$userManager = $this->get('fos_user.user_manager');
		$user = $userManager->findUserBy(['id' => $id]);
		$form = $this->get(UserWorkflow::class)->makeUserCreateEditForm($user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$userGroupIds = $form->get('userGroup')->getData();
			$this->get(UserWorkflow::class)->updateUser($user, $userGroupIds);
		}
		return $this->render('WarehouseBundle::User/user_edit.html.twig', [
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/{id}/disableUser", name="user_disable")
	 */
	public function disableUserAction($id)
	{
		$userManager = $this->get('fos_user.user_manager');
		$user = $userManager->findUserBy(['id' => $id]);
		$user->setEnabled(false);
		$userManager->updateUser($user);
		return $this->redirectToRoute('user_list');
	}

	/**
	 * @Route("/{id}/enableUser", name="user_enable")
	 */
	public function enableUserAction($id)
	{
		$userManager = $this->get('fos_user.user_manager');
		$user = $userManager->findUserBy(['id' => $id]);
		$user->setEnabled(true);
		$userManager->updateUser($user);
		return $this->redirectToRoute('user_list');
	}
}
