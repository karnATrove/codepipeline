<?php

namespace WarehouseApiBundle\Mapper\Booking;


use JMS\Serializer\SerializerBuilder;
use Rove\CanonicalDto\Booking\BookingCommunicationDto;
use Rove\CanonicalDto\Booking\BookingContactDto;
use WarehouseApiBundle\Exception\MapperException;
use WarehouseBundle\Entity\BookingContact;
use WarehouseBundle\Entity\BookingContactCommunication;

class BookingContactMapper
{
	public static function mapToDtoList($list)
	{
		$dtoList = [];
		foreach ($list as $item) {
			$dtoList[] = self::mapToDto($item);
		}
		return $dtoList;
	}

	/**
	 * @param BookingContact $bookingContact
	 *
	 * @return BookingContactDto
	 */
	public static function mapToDto(BookingContact $bookingContact)
	{
		$serializer = SerializerBuilder::create()->build();
		$json = $serializer->serialize($bookingContact, 'json');
		/** @var BookingContactDto $bookingContactDto */
		$bookingContactDto = $serializer->deserialize($json, BookingContactDto::class, 'json');
		$bookingContactDto->setAddress1($bookingContact->getStreet());
		$bookingContactDto->setAddress2($bookingContact->getStreet2());
		$defaultCommunication = $bookingContact->getDefaultCom();
		$bookingContactDto->setDefaultCommunication(self::mapBookingCommunication($defaultCommunication));
		$bookingContactDto->setCommunications(self::mapBookingCommunicationList($bookingContact->getCommunications()));
		return $bookingContactDto;
	}

	/**
	 * @param                               $id
	 * @param BookingContactCommunication[] $communicationList
	 *
	 * @return BookingContactCommunication
	 * @throws MapperException
	 */
	private static function getCommunication($id, $communicationList)
	{
		foreach ($communicationList as $communication) {
			if ($communication->getId() == $id) {
				return $communication;
			}
		}
		throw new MapperException("Failed to map default booking communication");
	}

	/**
	 * @param BookingContactCommunication $bookingContactCommunication
	 *
	 * @return BookingCommunicationDto
	 */
	public static function mapBookingCommunication(BookingContactCommunication $bookingContactCommunication)
	{
		$bookingCommunicationDto = new BookingCommunicationDto();
		$bookingCommunicationDto->setType($bookingContactCommunication->getType());
		$bookingCommunicationDto->setValue($bookingContactCommunication->getValue());
		$bookingCommunicationDto->setCreatedAt($bookingContactCommunication->getCreated());
		$bookingCommunicationDto->setUpdatedAt($bookingContactCommunication->getModified());
		return $bookingCommunicationDto;
	}

	/**
	 * @param BookingContactCommunication[] $bookingContactCommunicationList
	 *
	 * @return BookingCommunicationDto[]
	 */
	public static function mapBookingCommunicationList($bookingContactCommunicationList)
	{
		$resp = [];
		foreach ($bookingContactCommunicationList as $bookingContactCommunication) {
			$resp[] = self::mapBookingCommunication($bookingContactCommunication);
		}
		return $resp;
	}

    /**
     * @param  BookingContactDto $bookingContactDto
     * @return BookingContact
     * @throws MapperException
     */
    public static function mapDtoToEntity(BookingContactDto $bookingContactDto)
    {
	    $bookingContact = new BookingContact();
        $bookingContact->setIsResidential($bookingContact->getIsResidential());
        $bookingContact->setCompany($bookingContactDto->getCompany());
        if (!$bookingContactDto->getName()) {
            throw new MapperException("Booking contact name is empty");
        }
        $bookingContact->setName($bookingContactDto->getName());
        if (!$bookingContactDto->getAddress1()) {
            throw new MapperException("Booking address is empty");
        }
        $bookingContact->setStreet($bookingContactDto->getAddress1());
        $bookingContact->setStreet2($bookingContactDto->getAddress2());
        if (!$bookingContactDto->getCity()) {
            throw new MapperException("Booking address city is empty");
        }
        $bookingContact->setCity($bookingContactDto->getCity());
        if (!$bookingContactDto->getState()) {
            throw new MapperException("Booking address state/province is empty");
        }
        $bookingContact->setState($bookingContactDto->getState());
        if (!$bookingContactDto->getZip()) {
            throw new MapperException("Booking address postal code is empty");
        }
        $bookingContact->setZip($bookingContactDto->getZip());
        if (!$bookingContactDto->getCountry()) {
            throw new MapperException("Booking address country is empty");
        }
        $bookingContact->setCountry($bookingContactDto->getCountry());

        // handle booking contact communications
        $bookingCommunications = self::mapCommunicationDtoListToEntityList($bookingContactDto->getCommunications());
        // build relations
        foreach ($bookingCommunications as $bookingCommunication) {
            $bookingCommunication->setContact($bookingContact);
            $bookingContact->addCommunication($bookingCommunication);
        }
        // set default communication
        self::mapDefaultCommunicationDtoToEntity($bookingContact, $bookingContactDto);

        return $bookingContact;
    }

    /**
     * Map Dto to entity: BookingContactCommunication
     * @param BookingCommunicationDto $bookingCommunicationDto
     * @return BookingContactCommunication
     */
    public static function mapCommunicationDtoToEntity(BookingCommunicationDto $bookingCommunicationDto)
    {
        $bookingCommunication = new BookingContactCommunication();
        $bookingCommunication->setType($bookingCommunicationDto->getType());
        $bookingCommunication->setValue($bookingCommunicationDto->getValue());
        $bookingCommunication->setCreated($bookingCommunicationDto->getCreatedAt());
        $bookingCommunication->setModified($bookingCommunicationDto->getUpdatedAt());
        return $bookingCommunication;
    }

    /**
     * Map list of dto to entity: BookingContactCommunication
     * @param BookingCommunicationDto[] $list
     * @return BookingContactCommunication[]
     */
    public static function mapCommunicationDtoListToEntityList($list)
    {
        $bookingCommunications = array();
        /** @var BookingCommunicationDto $item */
        foreach ($list as $item) {
            $bookingCommunications[] = self::mapCommunicationDtoToEntity($item);
        }

        return $bookingCommunications;
    }

    /**
     * Try to map the default communication Dto to {BookingContact}.defaultCom
     * @param \WarehouseBundle\Entity\BookingContact $bookingContact
     * @param \Rove\CanonicalDto\Booking\BookingContactDto $bookingContactDto
     */
    public static function mapDefaultCommunicationDtoToEntity(BookingContact $bookingContact, BookingContactDto $bookingContactDto)
    {
        $bookingCommunicationDto = $bookingContactDto->getDefaultCommunication();
        if (!empty($bookingCommunicationDto)) {
            foreach ($bookingContact->getCommunications() as $communication) {
                if ($communication->getValue() == $bookingCommunicationDto->getValue() && $communication->getType() == $bookingCommunicationDto->getType()) {
                    $bookingContact->setDefaultCom($communication);
                    return;
                }
            }

            // jump here which means the default communication is not in the communication list, add it to the list
            $defaultCom = self::mapCommunicationDtoToEntity($bookingContactDto->getDefaultCommunication());
            $defaultCom->setContact($bookingContact);
            $bookingContact->addCommunication($defaultCom);
            $bookingContact->setDefaultCom($defaultCom);
        }
    }
}