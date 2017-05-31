<?php
namespace WarehouseBundle\Model;

use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;

use WarehouseBundle\Entity\Product;

/**
 * Abstract Booking Manager implementation which can be used as base class for your
 * concrete manager.
 *
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */
abstract class ProductManager implements ProductManagerInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createProduct()
    {
        $product = new \WarehouseBundle\Entity\Product();

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function findProductById($id)
    {
        return $this->findProductBy(array('id' => $id));
    }

    /**
     * {@inheritdoc}
     */
    public function findProductByModel($model)
    {
        return $this->findProductBy(array('model' => $model));
    }

}
