<?php
/**
 * Created by PhpStorm.
 * User: rovedev
 * Date: 2017-08-02
 * Time: 7:48 AM
 */

namespace WarehouseBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use WarehouseBundle\Entity\BookingContact;

class BookingContactManager extends BaseManager {
    private $bookingContactRepository;

    public function __construct(EntityManagerInterface $entityManager) {
        parent::__construct($entityManager);
        $this->bookingContactRepository = $entityManager->getRepository(BookingContact::class);
    }

    /**
     * @param BookingContact $bookingContact
     * @param null           $entityManager
     */
    public function update(BookingContact $bookingContact, $entityManager = null)
    {
        $flush = $entityManager ? false : true;
        $entityManager = $entityManager ? $entityManager : $this->entityManager;
        $entityManager->persist($bookingContact);
        if ($flush) {
            $entityManager->flush();
        }
    }

    /**
     * handle update action on default Communication manually
     *
     * @param BookingContact $bookingContact
     * @param null|EntityManagerInterface $entityManager
     *
     */
    public function updateDefaultCommunication(BookingContact $bookingContact, $entityManager = null)
    {
        $bookingContactCommunication = $bookingContact->getDefaultCom();

        if (!$bookingContactCommunication) {
            $flush = $entityManager ? false : true;
            $entityManager = $entityManager ? $entityManager : $this->entityManager;
            $entityManager->persist($bookingContactCommunication);
            if ($flush) {
                $entityManager->flush();
            }
        }
    }

    /**
     * Handle unset action on default Communication manually
     * todo: need to test
     * @param \WarehouseBundle\Entity\BookingContact $bookingContact
     * @param null $entityManager
     * @param bool $remove      TRUE to actually remove the Communication not only unset.
     *
     * @internal param null $entityManage
     */
    public function unsetDefaultCommunication(BookingContact $bookingContact, $entityManager = null, $remove = FALSE) {
        $bookingContactCommunication = $bookingContact->getDefaultCom();
        if (!$bookingContactCommunication) {
            $flush = $entityManager ? false : true;
            $entityManager = $entityManager ? $entityManager : $this->entityManager;
            if ($remove) {
                $entityManager->remove($bookingContactCommunication);
            }
            $bookingContact->setDefaultCom(null);
            if ($flush) {
                $entityManager->flush();
            }
        }
    }
}