<?php

namespace WarehouseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;
use WarehouseBundle\Entity\User;

/**
 * User controller.
 *
 * @Route("/usertest")
 */
class UserTestController extends Controller
{
	/**
	 * @Route("/", name="user_list")
	 */
	public function indexAction(Request $request)
	{
		$users = $this->container->get('warehouse.manager.user_manager')->getAllUsers();
		return $this->render('user/user_list.html.twig', [
			'users' => $users
		]);
	}

	/**
	 * @Route("/users/view/{id}", name="user_view")
	 */
	public function viewAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('AppBundle:User')->find($id);

		if (!$user) {
			throw new Exception('not found');
		}

		return $this->render('user/user_view.html.twig', [
			'user' => $user,
		]);
	}

	/**
	 * @Route("/createUser", name="user_create")
	 */
	public function createUserAction(Request $request)
	{
		$userManager = $this->get('fos_user.user_manager.default');
		$user = $userManager->createUser();
		$user->setEnabled(true);
		$form = $this->get('warehouse.workflow.user_workflow')->makeUserCreateEditForm($user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$userManager->updateUser($user);
			return $this->redirectToRoute('user_view', ['id' => $user->getId()]);
		}
		return $this->render('user/user_create.html.twig', [
			'form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/{id}/editUser", name="user_edit")
	 */
	public function editUserAction(Request $request, $id)
	{
		$userManager = $this->get('fos_user.user_manager.default');
		$user = $userManager->findUserBy(['id' => $id]);
		$form = $this->get('warehouse.workflow.user_workflow')->makeUserCreateEditForm($user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$userManager->updateUser($user);
		}
		return $this->render('user/user_edit.html.twig', [
			'form' => $form->createView()
		]);
	}
}
