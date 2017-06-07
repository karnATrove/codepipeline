<?php

namespace WarehouseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use WarehouseBundle\Entity\UserGroup;

/**
 * Class UserGroupController
 * @package WarehouseBundle\Controller
 * @Route("/user-group")
 */
class UserGroupController extends Controller
{
	/**
	 * @Route("/", name="user_group_list")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$userGroups = $em->getRepository('WarehouseBundle:UserGroup')->findAll();
		return $this->render('user/user_group_list.html.twig', [
			'userGroups' => $userGroups
		]);
	}

	/**
	 * @Route("/view/{id}", name="user_group_view")
	 */
	public function viewAction(UserGroup $userGroup)
	{
		if (!$userGroup) {
			throw new \Exception('not found');
		}
		return $this->render('user/user_group_view.html.twig', [
			'userGroup' => $userGroup
		]);
	}

	/**
	 * @Route("/create", name="user_group_create")
	 */
	public function createUserGroupAction(Request $request)
	{
		$userGroup = new UserGroup(null);
		$form = $this->createFormBuilder($userGroup)->add('name', TextType::Class)->getForm();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($userGroup);
			$em->flush();
			return $this->redirectToRoute('user_group_list');
		}

		return $this->render('user/user_group_create.html.twig', [
			'form' => $form->createView(),
		]);
	}


}
