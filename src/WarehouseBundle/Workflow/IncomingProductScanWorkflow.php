<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-08
 * Time: 1:21 PM
 */

namespace WarehouseBundle\Workflow;


use Rove\CanonicalDto\Container\ContainerUpdateDto;
use RoveSiteRestApiBundle\Exception\RoveSiteApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\DTO\AjaxResponse\AjaxCommandDTO;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Entity\IncomingStatus;
use WarehouseBundle\Entity\IncomingType;
use WarehouseBundle\Exception\Manager\ManagerException;
use WarehouseBundle\Exception\WorkflowException\WorkflowAPIException;
use WarehouseBundle\Exception\WorkflowException\WorkflowException;
use WarehouseBundle\Manager\IncomingManager;
use WarehouseBundle\Manager\IncomingProductManager;
use WarehouseBundle\Manager\IncomingStatusManager;
use WarehouseBundle\Manager\LocationManager;

class IncomingProductScanWorkflow extends BaseWorkflow
{
	private $incomingProductScanManager;
	private $incomingProductManager;
	private $productManager;
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
		$this->incomingProductScanManager = $container->get('warehouse.manager.incoming_product_scan_manager');
		$this->incomingProductManager = $container->get('warehouse.manager.incoming_product_manager');
		$this->productManager = $container->get('warehouse.manager.product_manager');
		$this->templating = $container->get('templating');
		$this->locationManager = $container->get('warehouse.manager.location_manager');
		$this->user = $this->container->get('security.token_storage')->getToken()->getUser();
	}

	/**
	 * @param IncomingProductScan $incomingProductScan
	 * @param                     $qty
	 * @param                     $locationId
	 *
	 * @return array
	 * @throws WorkflowException
	 */
	public function edit(IncomingProductScan $incomingProductScan, $qty, $locationId)
	{
		if ($qty !== null) {
			$incomingProductScan->setQtyOnScan($qty);
		}
		$locationTitle = $incomingProductScan->getLocation() ?
			$incomingProductScan->getLocation()->printLocation() : "NULL";

		if ($locationId !== null) {
			if (empty($locationId)) {
				$incomingProductScan->setLocation(null);
				$locationTitle = "NULL";
			} else {
				$location = $this->locationManager->findById($locationId);
				if (!$location) {
					throw new WorkflowException("Location no longer exist.");
				}
				$incomingProductScan->setLocation($location);
				$locationTitle = $location->printLocation();
			}
		}
		$incomingProductScan->setModified(new \DateTime());
		$this->incomingProductScanManager->update($incomingProductScan);
		$message = "Model {$incomingProductScan->getProduct()->getModel()} updated with location: " .
			$locationTitle . " Quantity: {$incomingProductScan->getQtyOnScan()}";
		$ajaxCommands[] = new AjaxCommandDTO(null, AjaxCommandDTO::OP_NOTY, $message, ['type' => 'success']);
		return $ajaxCommands;
	}

	/**
	 * @param IncomingProductScan $incomingProductScan
	 *
	 * @return AjaxCommandDTO[]
	 * @throws ManagerException
	 */
	public function delete(IncomingProductScan $incomingProductScan)
	{
		$messages = [];
		$incoming = $incomingProductScan->getIncoming();
		if (!IncomingManager::allowDelete($incoming)) {
			$messages['warning'][] = 'Incoming not allow to be deleted anymore';
		}
		$this->incomingProductScanManager->delete($incomingProductScan);
		$messages['success'][] = "Incoming {$incomingProductScan->getProduct()->getModel()} successfully deleted";
		if (!empty($messages)) {
			$this->container->get('warehouse.utils.message_printer')->printToFlashBag($messages);
		}
		$ajaxCommands[] = new AjaxCommandDTO('.loading', AjaxCommandDTO::OP_HIDE);
		$ajaxCommands[] = new AjaxCommandDTO('.incomingScannedProductsPage',
			AjaxCommandDTO::OP_HTML, $this->getScannedProductTableView($incoming));
		$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
			AjaxCommandDTO::OP_HTML, $this->getMessageBagView());
		return $ajaxCommands;
	}

	/**
	 * @param IncomingProductScan $incomingProductScan
	 * @param Incoming            $incoming
	 *
	 * @return mixed
	 */
	public function getScannedProductTableView(Incoming $incoming)
	{
		$incomingProductScans = $this->incomingProductScanManager
			->orderIncomingProductScanBySkuAndModified($incoming->getIncomingScannedProducts());
		$resp = $this->templating->render("WarehouseBundle::Incoming/products_scanned_rows.html.twig", [
			'scannedProducts' => $incomingProductScans,
			'incoming' => $incoming,
			'isComplete' => IncomingManager::isComplete($incoming),
			'locationDropdownList' => LocationManager::toArray($this->locationManager->getLocations())
		]);
		return $resp;
	}

	/**
	 * @return mixed
	 */
	public function getMessageBagView()
	{
		return $this->templating->render("WarehouseBundle::Incoming/products_scanned_form_message_bag.html.twig");
	}

	/**
	 * @param string   $sku
	 * @param Incoming $incoming
	 * @param array    $messages
	 *
	 * @return array
	 */
	public function newScan(string $sku, Incoming $incoming)
	{
		if (IncomingManager::isComplete($incoming)) {
			throw new WorkflowException("Incoming is already set to complete");
		}
		$messages = [];
		$incomingProduct = $this->incomingProductManager->getOneByIncomingAndSku($incoming, $sku);
		$incomingProductScan = $this->incomingProductScanManager->getOneByIncomingAndSku($incoming, $sku, false);
		$product = $this->productManager->getOneBySku($sku);
		$numberPerScan = 0;
		if (!$incomingProductScan) {
			if (!$product) {
				$product = $this->productManager->createNewProductWithDefaultInfo($sku, $this->user);
				$this->productManager->updateProduct($product, $this->entityManager);
				$messages['warning'][] = "<strong> {$sku} </strong> was created as a new product.";
			}
			$incomingProductScan = new IncomingProductScan();
			$incomingProductScan->setIncoming($incoming);
			$incomingProductScan->setIncomingProduct($incomingProduct);
			$incomingProductScan->setQtyOnScan($numberPerScan);
			$incomingProductScan->setProduct($product);
			$incomingProductScan->setCreated(new \DateTime());
			$incomingProductScan->setModified(new \DateTime());
			if (!$incomingProduct) {
				$messages['success'][] = "<strong> {$sku} </strong> was not identified in the Incoming container " .
					"however it was added to this list.";

			} else {
				$messages['success'][] = "Successfully added <strong> {$sku} </strong>.";
			}
		} else {
			# Update the scan item
			$incomingProductScan->setModified(new \DateTime('now'));
			$incomingProductScan->setQtyOnScan($incomingProductScan->getQtyOnScan() + $numberPerScan);
			$messages['success'][] = "Increased unassigned quantity to <strong> {$sku} </strong>.";
		}
		$incomingProductScan->setUser($this->user);
		$this->incomingProductScanManager->update($incomingProductScan, $this->entityManager);
		$this->entityManager->flush();
		if (!empty($messages)) {
			$this->container->get('warehouse.utils.message_printer')->printToFlashBag($messages);
		}
		$ajaxCommands[] = new AjaxCommandDTO('.incomingScannedProductsPage',
			AjaxCommandDTO::OP_HTML, $this->getScannedProductTableView($incoming));
		$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
			AjaxCommandDTO::OP_HTML, $this->getMessageBagView());
		return $ajaxCommands;
	}

	/**
	 * @param Incoming $incoming
	 *
	 * @return array
	 */
	public function loadScannedProductsFromPackingList(Incoming $incoming)
	{
		if (IncomingManager::isComplete($incoming)) {
			throw new WorkflowException("Incoming is already set to complete");
		}
		$incomingProducts = $incoming->getIncomingProducts();
		$incomeScannedProducts = $incoming->getIncomingScannedProducts();
		$productIds = IncomingProductManager::getIncomingProductIdsByIncomingProductScan($incomeScannedProducts);
		if (!$this->user) {
			throw new WorkflowException("Failed! Can not identify user. Please refresh page and try again.");
		}
		foreach ($incomingProducts as $incomingProduct) {
			if (!in_array($incomingProduct->getProduct()->getId(), $productIds)) {
				$incomingProductScan = new IncomingProductScan();
				$incomingProductScan->setIncoming($incoming);
				$incomingProductScan->setIncomingProduct($incomingProduct);
				$incomingProductScan->setQtyOnScan(0);
				$incomingProductScan->setProduct($incomingProduct->getProduct());
				$incomingProductScan->setCreated(new \DateTime());
				$incomingProductScan->setModified(new \DateTime());
				$incomingProductScan->setUser($this->user);
				$this->incomingProductScanManager->update($incomingProductScan, $this->entityManager);
			}
		}
		$messages['success'][] = "Packing list pre-loaded";
		if (!empty($messages)) {
			$this->container->get('warehouse.utils.message_printer')->printToFlashBag($messages);
		}
		$this->entityManager->flush();
		$this->entityManager->refresh($incoming);
		$ajaxCommands[] = new AjaxCommandDTO('.incomingScannedProductsPage',
			AjaxCommandDTO::OP_HTML, $this->getScannedProductTableView($incoming));
		$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
			AjaxCommandDTO::OP_HTML, $this->getMessageBagView());
		return $ajaxCommands;
	}

	/**
	 * @param Incoming $incoming
	 * @param          $form
	 *
	 * @return array
	 * @throws WorkflowException
	 */
	public function completeProductScan(Incoming $incoming, $form, $updateRove = true)
	{
		if (IncomingManager::isComplete($incoming)) {
			throw new WorkflowException("Incoming is already set to complete");
		}
		foreach ($form as $incomingProductScanId => $detail) {
			$detail = (array)$detail;
			$incomingProductScan = $this->incomingProductScanManager->find($incomingProductScanId);
			if (!$incomingProductScan) {
				throw new WorkflowException("Incoming product not found, please refresh and try again");
			}
			if (!isset($detail['location']) || !isset($detail['quantity'])) {
				throw new WorkflowException("Invalid! Location or quantity not set.");
			}
			$incomingProductScan->setUser($this->user);
			$incomingProductScan->setModified(new \DateTime());
			$incomingProductScan->setLocationId($detail['location']);
			$incomingProductScan->setQtyOnScan($detail['quantity']);
			$this->incomingProductScanManager->update($incomingProductScan, $this->entityManager);
		}

		$incomingWorkflow = new IncomingWorkflow($this->container);
		$incomingWorkflow->setIncomingComplete($incoming, $this->entityManager);

		if ($updateRove && $incoming->getType()->getCode() == IncomingType::OCEAN_FREIGHT_CODE) {
			//update container in rove site first, it throw RoveSiteApiException.
			$containerUpdateDto = new ContainerUpdateDto();
			$containerUpdateDto->setStatusCode($incoming->getStatus()->getCode());
			$containerUpdateDto->setName($incoming->getName());
			try {
				$this->container->get('rove_site_rest_api.manager.container_manager')
					->update($containerUpdateDto, $incoming->getName());

			} catch (RoveSiteApiException $roveSiteApiException) {
				throw new WorkflowAPIException("Error from roveconcepts.com:<br>"
					. $roveSiteApiException->getMessage());
			}
		}

		//save to db
		$this->entityManager->flush();

		$messages['success'][] = "Incoming container scanned list was saved and Incoming container is now complete.";
		$this->container->get('warehouse.utils.message_printer')->printToFlashBag($messages);
		$ajaxCommands[] = new AjaxCommandDTO('.incomingScannedProductsPage',
			AjaxCommandDTO::OP_HTML, $this->getScannedProductTableView($incoming));
		$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
			AjaxCommandDTO::OP_HTML, $this->getMessageBagView());
		return $ajaxCommands;
	}

	/**
	 * @param IncomingProductScan $incomingProductScan
	 *
	 * @return array
	 * @throws WorkflowException
	 */
	public function splitProductScan(IncomingProductScan $incomingProductScan)
	{
		$incoming = $incomingProductScan->getIncoming();
		if (!IncomingStatusManager::haveStatus($incoming, [IncomingStatus::INBOUND, IncomingStatus::ARRIVED])) {
			throw new WorkflowException('Incoming container is no longer in active/arrived status.');
		}
		if (!$this->user) {
			throw new WorkflowException("Failed! Can not identify user. Please refresh page and try again.");
		}
		$newIncomingProductScan = (new IncomingProductScan())
			->setIncoming($incoming)
			->setIncomingProduct($incomingProductScan->getIncomingProduct())
			->setUser($this->user)
			->setCreated(new \DateTime())
			->setModified(new \DateTime())
			->setQtyOnScan(0)
			->setProduct($incomingProductScan->getProduct());
		$this->incomingProductScanManager->update($newIncomingProductScan);
		$messages['success'][] = "Incoming scanned product {$incomingProductScan->getProduct()->getModel()} split";
		$this->container->get('warehouse.utils.message_printer')->printToFlashBag($messages);
		$ajaxCommands[] = new AjaxCommandDTO('.incomingScannedProductsPage',
			AjaxCommandDTO::OP_HTML, $this->getScannedProductTableView($incoming));
		$ajaxCommands[] = new AjaxCommandDTO('#products_scanned_form_message_bag',
			AjaxCommandDTO::OP_HTML, $this->getMessageBagView());
		return $ajaxCommands;
	}
}