<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-07
 * Time: 9:28 AM
 */

namespace WarehouseBundle\Workflow;


use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation\FormType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use WarehouseBundle\Entity\User;

class UserWorkflow
{
	private $container;

	/**
	 * UserWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @param UserInterface $user
	 *
	 * @return \Symfony\Component\Form\FormInterface
	 */
	public function makeUserCreateEditForm(UserInterface $user)
	{
		$formBuilder = $this->container->get('form.factory')->createBuilder(FormType::class, $user);
		$formBuilder->add('username', TextType::class)
			->add('email', TextType::class)
			->add('firstName', TextType::class);
		$formBuilder->add('lastName', TextType::class)
			->add('company', TextType::class, ['required' => false])
			->add('plainPassword', PasswordType::class, ['required' => false]);
		return $formBuilder->getForm();
	}
}