<?php
namespace WarehouseBundle\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;

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
}