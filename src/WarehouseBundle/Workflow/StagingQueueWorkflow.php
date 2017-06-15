<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 1:21 PM
 */

namespace WarehouseBundle\Workflow;


use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\DTO\AjaxResponse\AjaxCommandDTO;
use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\LocationProduct;
use WarehouseBundle\Exception\Manager\ManagerException;
use WarehouseBundle\Exception\WorkflowException\WorkflowException;
use WarehouseBundle\Manager\LocationManager;
use WarehouseBundle\Manager\LocationProductManager;

class StagingQueueWorkflow extends BaseWorkflow
{
	private $locationProductManager;
	private $locationManager;

	private $templating;
	private $user;

	/**
	 * IncomingProductScanWorkflow constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->locationProductManager = $container->get('warehouse.manager.location_product_manager');
		$this->locationManager = $container->get('warehouse.manager.location_manager');
		$this->templating = $container->get('templating');
		$this->user = $this->container->get('security.token_storage')->getToken()->getUser();
	}

	/**
	 * Update the quantity staged.
	 * @param  LocationProduct $locationProduct [description]
	 * @param  int             $quantity        [description]
	 * @return [type]                           [description]
	 */
	public function update(LocationProduct $locationProduct, int $quantity) {
		# Load the Staging area Product (if exists)
		$stagingLocationProduct = $this->entityManager->getRepository('WarehouseBundle:LocationProduct')->findOneStagingByProduct($locationProduct->getProduct());

		# Calculate the change
		$difference = $quantity - $locationProduct->getStaged();
		if ($difference == 0) return;

		# Works with both increase or decreate
		$locationProduct->setOnHand($locationProduct->getOnHand() - $difference);
		$locationProduct->setStaged($quantity);
		$this->locationProductManager->updateLocationProduct($locationProduct,$this->entityManager,$this->user);
		
		# Increase/Decrease the staging area location
		if ($stagingLocationProduct) {
			# Staging area for product exists
			$stagingLocationProduct->setOnHand($stagingLocationProduct->getOnHand() + $difference);
			$this->locationProductManager->updateLocationProduct($stagingLocationProduct,$this->entityManager,$this->user);
		} else {
			# Find staging area then create LocationProduct
			if ($stagingLocation = $this->entityManager->getRepository('WarehouseBundle:Location')->findOneStaging()) {
				if ($difference < 0)
					throw new \Exception('Staging location should have already existed for this product. Can not continue.');
				# Create new
				$this->locationProductManager->create($stagingLocation,$locationProduct->getProduct(),$difference,0,$this->entityManager,$this->user);
			} else {
				# No staging location exists in the system.
				throw new \Exception('The WMS requires a staging Location to be assigned. Refer to Location.staging.');
				return;
			}
		}

		# Save all of our changes
		$this->entityManager->flush();
	}
}