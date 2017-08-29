<?php

namespace WarehouseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IncomingFile
 *
 * @ORM\Table(name="warehouse_incoming_file")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\IncomingFileRepository")
 */
class IncomingFile implements IEntity
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
     * One IncomingFile has One User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Many IncomingFile have One Incoming
     * @ORM\ManyToOne(targetEntity="Incoming", inversedBy="files")
     * @ORM\JoinColumn(name="incoming_id", referencedColumnName="id")
     */
    private $incoming;

    /**
     * @var string
     *
     * @ORM\Column(name="filepath", type="string", length=75)
     */
    private $filepath;

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
     * Set filepath
     *
     * @param string $filepath
     *
     * @return IncomingFile
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get filepath
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return IncomingFile
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
     * Set incoming
     *
     * @param \WarehouseBundle\Entity\Incoming $incoming
     *
     * @return IncomingFile
     */
    public function setIncoming(\WarehouseBundle\Entity\Incoming $incoming = null)
    {
        $this->incoming = $incoming;

        return $this;
    }

    /**
     * Get incoming
     *
     * @return \WarehouseBundle\Entity\Incoming
     */
    public function getIncoming()
    {
        return $this->incoming;
    }

    /**
     * Set user
     *
     * @param \WarehouseBundle\Entity\User $user
     *
     * @return IncomingFile
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
}
