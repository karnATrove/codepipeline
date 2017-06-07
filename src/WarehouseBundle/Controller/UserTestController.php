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
	 * @Route("/", name="admin_user_list")
	 */
	public function indexAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		/** @var User[] $users */
		$users = $em->getRepository('WarehouseBundle:User')->findAll();

		$selection = [];
		foreach ($users as $user) {
			$selection[$user->getEmail()] = $user->getId();
		}

		$form = $this->createFormBuilder()
			->add('search', ChoiceType::class, array(
				'choices' => $selection,
				'placeholder' => 'Search by email',
			))
			->getForm();

		// replace this example code with whatever you need
		return $this->render('user/user_list.html.twig', [
			'users' => $users,
			'form' => $form->createView()
		]);
	}


}
