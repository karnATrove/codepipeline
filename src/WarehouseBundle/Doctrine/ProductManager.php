<?php
namespace WarehouseBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WarehouseBundle\Model\ProductInterface;
use WarehouseBundle\Model\ProductManager as BaseProductManager;

use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Entity\LocationProduct;

class ProductManager extends BaseProductManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param ObjectManager            $om
     */
    public function __construct(ObjectManager $om, ContainerInterface $container)
    {
        parent::__construct();

        $this->objectManager = $om;
        $this->container = $container;
        $this->repository = $om->getRepository('WarehouseBundle\Entity\Product');

        $metadata = $om->getClassMetadata('WarehouseBundle\Entity\Product');
    }

    /**
     * {@inheritdoc}
     */
    public function logEntry($note) {
        return TRUE;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteProduct(ProductInterface $product)
    {
        $this->objectManager->remove($product);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findProductBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findByModel(string $model) {
        return $this->findProductBy(array('model'=>trim($model)));
    }

    /**
     * {@inheritdoc}
     */
    public function findProducts()
    {
        return $this->repository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function reloadProduct(ProductInterface $product)
    {
        $this->objectManager->refresh($product);
    }

    /**
     * {@inheritdoc}
     */
    public function updateProduct(ProductInterface $product, $andFlush = true)
    {
        if ($product->getCreated() == NULL)
            $product->setCreated(new \DateTime('now'));
        $this->objectManager->persist($product);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * Validates if a product exists or not.
     * @param  ProductInterface $product [description]
     * @return boolean                    [description]
     */
    public function validateExists(ProductInterface $product) {
        $lookup = $this->findProductBy(array('model'=>$product->getModel()));
        return !is_null($lookup) && $lookup->getModel() == $product->getModel();
    }

}
