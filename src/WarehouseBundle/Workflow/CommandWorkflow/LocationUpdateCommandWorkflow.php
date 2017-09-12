<?php

namespace WarehouseBundle\Workflow\CommandWorkflow;

use Symfony\Component\Console\Output\OutputInterface;
use WarehouseBundle\Entity\Location;
use WarehouseBundle\Manager\LocationManager;

class LocationUpdateCommandWorkflow extends BaseCommandWorkflow
{

	public function updateLocations(OutputInterface $output = null)
	{
		$this->output = $output;
		for ($i = 1; $i <= 74; $i++) {
			for ($j = 1; $j <= 4; $j++) {
				$existing = $this->container->get(LocationManager::class)->findOneBy(['aisle' => 'A', 'row' => $i, 'level' => $j]);
				if ($existing) {
					continue;
				} else {
					$location = new Location();
					$location->setAisle('A')->setRow($i)->setLevel($j)->setStaging(0)->setCreated(new \DateTime());
					$this->container->get(LocationManager::class)->update($location);
					$this->logToConsole("A-{$i}-{$j} created");
				}
			}
		}
	}
}