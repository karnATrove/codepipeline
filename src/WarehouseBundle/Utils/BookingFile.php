<?php

namespace WarehouseBundle\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use WarehouseBundle\Entity\Booking as BookingEntity;
use WarehouseBundle\Entity\Carrier;

class BookingFile
{
	private $targetDir;

	public function __construct($targetDir)
	{
		$this->targetDir = $targetDir;
	}

	public function upload(UploadedFile $file)
	{
		$fileName = md5(uniqid()) . '-' . $file->getClientOriginalName();

		$file->move($this->targetDir, $fileName);

		return $fileName;
	}

	public function getDefaultBolUrl(BookingEntity $booking)
	{
		switch ($booking->getCarrierId()) {
			case Carrier::CARRIER_FEDEX:
				break;
			default:
				break;
		}
	}

	public function getDefaultLabelUrl(BookingEntity $booking)
	{

	}
}