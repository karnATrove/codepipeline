<?php

namespace WarehouseBundle\Workflow\CommandWorkflow;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseCommandWorkflow
{
	protected $container;
	/** @var EntityManagerInterface $entityManager */
	protected $entityManager;

	/** @var OutputInterface */
	protected $output;

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

	/**
	 * @param $info
	 */
	protected function logToConsole($info)
	{
		if ($this->output) {
			$this->output->writeln($info);
		}
	}
}