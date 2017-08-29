<?php

namespace WarehouseBundle\Workflow\ControllerWorkflow;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\Entity\User;

class BaseControllerWorkflow
{
	protected $container;
	/** @var EntityManagerInterface $entityManager */
	protected $entityManager;
	/** @var User */
	protected $currentUser;
	protected $securityChecker;

	protected $templating;
	protected $formBuilder;
	protected $router;

	/**
	 * BaseWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->entityManager = $container->get('doctrine.orm.entity_manager');
		$this->currentUser = $container->get('security.token_storage')->getToken()->getUser();
		$this->templating = $container->get('templating');
		$this->formBuilder = $container->get('form.factory');
		$this->router = $container->get('router');
		$this->securityChecker = $container->get('security.authorization_checker');
	}

	/**
	 * return if user is logged in
	 *
	 * @return bool
	 */
	public function isUserLoggedIn()
	{
		if ($this->securityChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
			return true;
		}
		return false;
	}
}