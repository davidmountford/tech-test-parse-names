<?php

namespace App\Services;

use App\Services\Uploads\HomeownerUploads;
use App\Services\Uploads\UploadsInterface;

class UploadService {
    public const homeowner = "homeowner";

    public const types = [
        self::homeowner => HomeownerUploads::class
    ];

    /**
     * Main function for uploading a CSV file.
     */
    public function getUploader(string $type): UploadsInterface
    {
        // Fetch the Classname of the specific uploader class for the $type name.
        $uploader = $this->getUploaderName($type);

        /** @var UploadsInterface $service */
        return new $uploader();
    }

    /**
     * Function to return the class of the uploader. 
     */ 
    public function getUploaderName(string $type): string
    {
        // Must be defined in the types array above
        if (!isset($this::types[$type])) {
            throw new \Exception('Invalid type, please use existing type');
        }

        return $this::types[$type]; 
    }
}
