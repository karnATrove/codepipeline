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
		$defaultCommunication = self::getCommunication($bookingContact->getDefaultCom(), $bookingContact->getCommunications());
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
	 * @param $bookingContactCommunicationList
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
}