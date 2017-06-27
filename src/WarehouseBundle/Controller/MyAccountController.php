<?php

namespace WarehouseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use WarehouseBundle\Entity\User;

/**
 * Class MyAccountController
 * @package WarehouseBundle\Controller
 * @Route("/my-account")
 */
class MyAccountController extends Controller
{
	/**
	 * @Route("/edit", name="my_account_edit")
	 */
	public function editMyAccountAction(Request $request)
	{
		$userManager = $this->get('fos_user.user_manager');
		/** @var User $user */
		$user = $this->getUser();
		$oldPassword = $user->getPlainPassword();

		$form = $this->buildUpdateForm($user);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if (!$form->get('plainPassword')){
				$user->setPlainPassword($oldPassword);
			}
			$userManager->updateUser($user);
		}

		return $this->render('WarehouseBundle::MyAccount/my_account_edit.html.twig', [
			'form' => $form->createView(),
		]);
	}

	/**
	 * @param $user
	 *
	 * @return Form
	 */
	private function buildUpdateForm($user){
		$form = $this->createFormBuilder($user)
			->add('username', TextType::class)
			->add('email', TextType::class)
			->add('name', TextType::class)
			->add('plainPassword', PasswordType::class,['required' => false])
			->getForm();
		return $form;
	}
}
