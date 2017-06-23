<?php

namespace WarehouseBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\BookingContactCommunication;

/**
 * BookingContact
 *
 * @ORM\Table(name="warehouse_booking_contact")
 * @ORM\Entity(repositoryClass="WarehouseBundle\Repository\BookingContactRepository")
 * @Gedmo\Loggable
 */
class BookingContact
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
     * One BookingContact has One Booking
     * @ORM\OneToOne(targetEntity="Booking", inversedBy="contact")
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id")
     */
    private $booking;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ORM\Column(name="is_residential", type="smallint")
     */
    private $isResidential = 1;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="company", type="string", length=75, nullable=true)
     */
    private $company;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ORM\Column(name="default_com", type="integer", nullable=true)
     */
    private $defaultCom;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="street", type="string", length=50)
     */
    private $street;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="street2", type="string", length=50, nullable=true)
     */
    private $street2;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="city", type="string", length=50)
     */
    private $city;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="state", type="string", length=50)
     */
    private $state;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="zip", type="string", length=20)
     */
    private $zip;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ORM\Column(name="country", type="string", length=50)
     */
    private $country;

    /**
     * One BookingContact has Many BookingContactCommunication.
     * @ORM\OneToMany(targetEntity="BookingContactCommunication", mappedBy="contact", cascade={"persist","remove"})
     */
    private $communications;

    /**
     * Constructor to initiate defaults.
     */
    public function __construct() {
        $this->communications = new ArrayCollection();
    }

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
     * Set isResidential
     *
     * @param integer $isResidential
     *
     * @return BookingContact
     */
    public function setIsResidential($isResidential)
    {
        $this->isResidential = $isResidential;

        return $this;
    }

    /**
     * Get isResidential
     *
     * @return int
     */
    public function getIsResidential()
    {
        return $this->isResidential;
    }

    /**
     * Set company
     *
     * @param string $company
     *
     * @return BookingContact
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return BookingContact
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set defaultCom
     *
     * @param integer $defaultCom
     *
     * @return BookingContact
     */
    public function setDefaultCom($defaultCom)
    {
        $this->defaultCom = $defaultCom;

        return $this;
    }

    /**
     * Get defaultCom
     *
     * @return int
     */
    public function getDefaultCom()
    {
        return $this->defaultCom;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return BookingContact
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street2
     *
     * @param string $street2
     *
     * @return BookingContact
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * Get street2
     *
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return BookingContact
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return BookingContact
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return BookingContact
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return BookingContact
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set booking
     *
     * @param Booking $booking
     *
     * @return BookingContact
     */
    public function setBooking(Booking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * Add communication
     *
     * @param BookingContactCommunication $communication
     *
     * @return BookingContact
     */
    public function addCommunication(BookingContactCommunication $communication)
    {
        $communication->setContact($this);
        $this->communications[] = $communication;

        return $this;
    }

    /**
     * Remove communication
     *
     * @param BookingContactCommunication $communication
     */
    public function removeCommunication(BookingContactCommunication $communication)
    {
        $this->communications->removeElement($communication);
    }

    /**
     * Get communications
     *
     * @return Collection|BookingContactCommunication[]
     */
    public function getCommunications()
    {
        return $this->communications;
    }
}
