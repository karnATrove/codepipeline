<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IncomingType
 *
 * @ORM\Table(name="warehouse_incoming_type")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\IncomingTypeRepository")
 */
class IncomingType
{
	const OCEAN_FREIGHT=1;
	const FORWARD=2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="detail", type="string", length=255)
     */
    private $detail;

	/**
	 * @ORM\OneToMany(targetEntity="WarehouseBundle\Entity\Incoming", mappedBy="incomingType")
	 */
	private $incoming;

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
     * Set code
     *
     * @param string $code
     *
     * @return IncomingType
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set detail
     *
     * @param string $detail
     *
     * @return IncomingType
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Get detail
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

	/**
	 * @return mixed
	 */
	public function getIncoming()
	{
		return $this->incoming;
	}

	/**
	 * @param mixed $incoming
	 */
	public function setIncoming($incoming)
	{
		$this->incoming = $incoming;
	}
}

