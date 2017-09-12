<?php

namespace WarehouseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WarehouseBundle\Workflow\CommandWorkflow\LocationUpdateCommandWorkflow;

class LocationUpdateCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName("warehouse:location:update")
			->setDescription('Updating locations...')
			->setHelp('This command update locations')
			->addArgument('orderNumber', InputArgument::OPTIONAL, 'Order number');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln([
			'Starting...'
		]);
			$this->getContainer()->get(LocationUpdateCommandWorkflow::class)
				->updateLocations($output);


	}
}