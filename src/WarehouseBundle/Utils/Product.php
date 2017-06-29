<?php
namespace WarehouseBundle\Utils;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use WarehouseBundle\Entity\Product as ProductEntity;
use WarehouseBundle\Model\ProductInterface;
use WarehouseBundle\Model\ProductManagerInterface;

class Product
{
	/**
	 * Container aware.
	 */
	private $container;

    /**
     * Product manager.
     *
     * @var ProductManagerInterface
     */
    private $productManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

	/**
	 * Make this utility container-aware (adding availaiblity of doctrine for example)
	 *
	 * @param      <type>  $container  The container
	 */
	public function __construct(Container $container,ProductManagerInterface $productManager, RequestStack $requestStack) {
		$this->container = $container;
        $this->productManager = $productManager;
        $this->requestStack = $requestStack;
	}

    /**
     * Creates a product and returns it.
     *
     * @param string $model
     * @param string $description
     * @param int $qtyPerCarton
     * @param float $length
     * @param float $width
     * @param float $height
     * @param string $dimUnits
     * @param float $weight
     * @param string $weightUnits
     * @param int $status
     *
     * @return ProductInterface
     */
    public function create($model, $description, $qtyPerCarton, $length, $width, $height, $dimUnits, $weight, $weightUnits, $status)
    {
        #$manipulator = $this->getContainer()->get('app.booking');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (get_class($user) !== 'WarehouseBundle\Entity\User') {
            $user = NULL;
        }

        $product = $this->productManager->createProduct();
        $product->setModel($model);
        $product->setDescription($description);
        $product->setQtyPerCarton($qtyPerCarton?$qtyPerCarton:1);
        $product->setLength($length);
        $product->setWidth($width);
        $product->setHeight($height);
        $product->setDimUnits($dimUnits?$dimUnits:'in');
        $product->setWeight($weight);
        $product->setWeightUnits($weightUnits?$weightUnits:'lbs');
        $product->setStatus($status?$status:ProductEntity::PRODUCT_STATUS_ACTIVE);
        $product->setUser($user);

        # Validate that the product does not already exist
        if ($this->productManager->validateExists($product)) {
            if (FALSE) {
                # We can optionally update the product...
                $original = $this->productManager->findByModel($product->getModel());
                $product->setId($original->getId()); # Swap the id
            } else {
                return $this->productManager->findByModel($product->getModel());
            }
        }

        # Create product if it does not exist.
        $this->productManager->updateProduct($product);

        # Use below if we ever use EventDispatching
        #$event = new ProductEvent($product, $this->getRequest());
        #$this->dispatcher->dispatch(WarehouseProductEvents::PRODUCT_CREATED, $event);

        return $product;
    }

    /**
     * Get on hand from Locations
     *
     * @param  \WarehouseBundle\Entity\Product $product [description]
     * @return     integer  On hand quantity.
     */
    public function getOnHand(\WarehouseBundle\Entity\Product $product) {
        $onHand = 0;
        foreach($product->getLocations() as $location) {
            $onHand += $location;
        }
        return $onHand;
    }

	/**
	 * @param ProductEntity $product
	 *
	 * @return mixed
	 */
    public function getOnHold(\WarehouseBundle\Entity\Product $product){
    	return $this->container->get("doctrine")->getRepository('WarehouseBundle:BookingProduct')->getOnHoldQuantityByProduct($product);
    }

    /**
     * Get allocated quantity
     *
     * @param  \WarehouseBundle\Entity\Product $product [description]
     * @return     integer  Allocated quantity.
     */
    public function getAllocated(\WarehouseBundle\Entity\Product $product) {
        return $this->container->get("doctrine")->getRepository('WarehouseBundle:BookingProduct')->getAllocatedQuantityByProduct($product);
    }

    /**
     * Get picked quantity by product
     *
     * @param  \WarehouseBundle\Entity\Product $product [description]
     * @param      boolean  $active_only Only include active orders (non shipped).
     * @return     integer  Allocated quantity.
     */
    public function getPicked(\WarehouseBundle\Entity\Product $product, $active_only = TRUE) {
        return $this->container->get("doctrine")->getRepository('WarehouseBundle:Product')->getPickedQtyByProduct($product,$active_only);
    }

    /**
     * Get available quantity
     *
     * @param  \WarehouseBundle\Entity\Product $product [description]
     * @return     integer  Quantity available.
     */
    public function getAvailable(\WarehouseBundle\Entity\Product $product) {
    	return $this->getAvailableByProduct($product);
//        return $this->container->get("doctrine")->getRepository('WarehouseBundle:Product')->getAvailableByProduct($product);
//
//        $onHand = 0;
//        foreach($product->getLocations() as $location) {
//            $onHand += $location->getOnHand();
//        }
//        return $onHand;
    }

    /**
	 * Get the available quantity.
	 * Operation: (on hand) - (on hold) - (allocated)
	 * @param  \WarehouseBundle\Entity\Product $product [description]
	 * @return [type]                                   [description]
	 */
	public function getAvailableByProduct(\WarehouseBundle\Entity\Product $product) {
		return $this->getOnHand($product) - $this->getOnHold($product);
	}

    /**
     * Gets the available internal for a specified product. This will tally onhand - picked (but not shipped).
     *
     * @param      \WarehouseBundle\Entity\Product  $product  The product
     *
     * @return     <type>                           The available internal.
     */
    public function getAvailableInternal(\WarehouseBundle\Entity\Product $product) {
        return $this->getOnHand($product) - $this->getPicked($product);
    }

    /**
     * Gets the location map.
     *
     * @param  \WarehouseBundle\Entity\Product $product [description]
     * @return     array  The location map.
     */
    public function getLocationMap(\WarehouseBundle\Entity\Product $product) {
        $map = array();
        foreach($product->getLocations() as $location) {
            $map[] = $location->getOnHand().' @ '.$location->getLocation()->getAisle().'-'.$location->getLocation()->getRow().'-'.$location->getLocation()->getLevel();
        }
        return $map;
    }

    /**
     * Listing of available product statuses.
     *
     * @return     array  Available product statuses.
     */
    public function productStatusList() {
        return array(
            \WarehouseBundle\Entity\Product::PRODUCT_STATUS_DELETED => 'Deleted',
	        \WarehouseBundle\Entity\Product::PRODUCT_STATUS_ACTIVE => 'Active',
	        \WarehouseBundle\Entity\Product::PRODUCT_STATUS_INACTIVE => 'Inactive',
        );
    }

    /**
     * Return a human readable format of the product status.
     *
     * @param      integer  $status  The product status identifier
     *
     * @return     string  Human readable text.
     */
    public function productStatusName($status) {
        return isset($this->productStatusList()[$status]) ? $this->productStatusList()[$status] : 'Unknown';
    }

    /**
     * Listing of available product dimension units.
     *
     * @return     array  Available product dimension options.
     */
    public function productDimensionUnitList() {
        return array(
            'in' => 'Inches',
            'cm' => 'Centimeters',
            'mm' => 'Millimeters',
        );
    }

    /**
     * Return a human readable format of the product dimension units.
     *
     * @param      string  $dimUnit  The product dimension unit identifier
     *
     * @return     string  Human readable text.
     */
    public function productDimensionUnitName($dimUnit) {
        return isset($this->productDimensionUnitList()[$dimUnit]) ? $this->productDimensionUnitList()[$dimUnit] : 'Unknown';
    }

    /**
     * Listing of available product dimension units.
     *
     * @return     array  Available product dimension options.
     */
    public function productWeightUnitList() {
        return array(
            'lbs' => 'Pounds',
            'kgs' => 'Kilograms',
        );
    }

    /**
     * Return a human readable format of the product weight units.
     *
     * @param      string  $weightUnit  The product weight unit identifier
     *
     * @return     string  Human readable text.
     */
    public function productWeightUnitName($weightUnit) {
        return isset($this->productWeightUnitList()[$weightUnit]) ? $this->productWeightUnitList()[$weightUnit] : 'Unknown';
    }

    /**
     * Get BookingProduct's based on Product entity.
     *
     * @param  \WarehouseBundle\Entity\Product $product [description]
     * @return     integer  Allocated quantity.
     */
    public function getAllocatedProducts(\WarehouseBundle\Entity\Product $product) {
        return $this->container->get("doctrine")->getRepository('WarehouseBundle:BookingProduct')->getAllocatedByProduct($product);
    }

    /**
     * Get picked BookingProduct's based on Product entity.
     *
     * @param  \WarehouseBundle\Entity\Product $product [description]
     * @param   integer Number of results to return
     *
     * @return     integer  Allocated quantity.
     */
    public function getRecentPickedProducts(\WarehouseBundle\Entity\Product $product, $limit=10) {
        return $this->container->get("doctrine")->getRepository('WarehouseBundle:BookingProduct')->getPickedRecentByProduct($product,$limit);
    }

    public function getRecentShippedProducts(\WarehouseBundle\Entity\Product $product, $limit=10) {
        return $this->container->get("doctrine")->getRepository('WarehouseBundle:BookingProduct')->getShippedRecentByProduct($product,$limit);
    }
}