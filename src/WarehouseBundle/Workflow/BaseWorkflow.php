<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 1:35 PM
 */

namespace WarehouseBundle\Workflow;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseWorkflow
{
	protected $container;
	/** @var EntityManagerInterface $entityManager  */
	protected $entityManager;

	/**
	 * BaseWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->entityManager = $container->get('doctrine.orm.entity_manager');
	}
}