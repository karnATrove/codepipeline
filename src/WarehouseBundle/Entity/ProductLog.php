<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LocationLog
 *
 * @ORM\Table(name="warehouse_product_log")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\ProductLogRepository")
 */
class ProductLog implements IEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * One ProductLog has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many ProductLog have One product.
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $product;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text")
     */
    private $note;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return string
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return ProductLog
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return ProductLog
     */
    public function setUser(\WarehouseBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \WarehouseBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set product
     *
     * @param \WarehouseBundle\Entity\Location $product
     *
     * @return ProductLog
     */
    public function setProduct(\WarehouseBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \WarehouseBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
