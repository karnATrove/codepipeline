<?php
namespace WarehouseBundle\Model;
use WarehouseBundle\Entity\Product;

/**
 * Interface to be implemented by product managers. This adds an additional level
 * of abstraction between your application, and the actual repository.
 *
 * All changes to products should happen through this interface.
 *
 * The class also contains ACL annotations which will only work if you have the
 * SecurityExtraBundle installed, otherwise they will simply be ignored.
 *
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */
interface ProductManagerInterface
{
    /**
     * Creates a product log entry.
     *
     * @param string $note
     */
    public function logEntry($note);

    /**
     * Creates an empty product instance.
     *
     * @return ProductInterface
     */
    public function createProduct();

    /**
     * Deletes a product.
     *
     * @param ProductInterface $product
     */
    public function deleteProduct(ProductInterface $product);

    /**
     * Finds one product by the given criteria.
     *
     * @param array $criteria
     *
     * @return ProductInterface
     */
    public function findProductBy(array $criteria);

    /**
     * Find a product by its internal id.
     *
     * @param string $id
     *
     * @return ProductInterface or null if product does not exist
     */
    public function findProductById($id);

    /**
     * Find a product by its model.
     *
     * @param string $orderReference
     *
     * @return ProductInterface or null if product does not exist
     */
    public function findProductByModel($model);

    /**
     * Returns a collection with all product instances.
     *
     * @return \Traversable
     */
    public function findProducts();

    /**
     * Reloads a product.
     *
     * @param ProductInterface $product
     */
    public function reloadProduct(ProductInterface $product);

    /**
     * Updates a product.
     *
     * @param ProductInterface $product
     */
    public function updateProduct(ProductInterface $product);
}
