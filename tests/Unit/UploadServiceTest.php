<?php

namespace Tests\Unit;

use App\Http\Controllers\UploadController;
use App\Services\UploadService;
use PHPUnit\Framework\TestCase;

class UploadServiceTest extends TestCase
{
    public UploadService $service;

    public function setUp(): void
    {
        $this->service = new UploadService();
    }

    public function test_upload_service_constructs()
    {
        $this->assertIsObject($this->service);
    }

    public function test_can_get_uploader_name()
    {
        $uploader = $this->service->getUploaderName(UploadService::homeowner);

        $this->assertEquals($uploader, $this->service::types[UploadService::homeowner]);
    }

    public function test_get_uploader_name_throws_exception_on_bad_type()
    {
        $this->expectException(\Exception::class);

        $this->service->getUploaderName(UploadService::class);
    }

    public function test_upload_service_can_instantiate_uploader()
    {
        $uploader = $this->service->getUploader(UploadService::homeowner);

        $this->assertEquals($uploader::class, $this->service::types[UploadService::homeowner]);
    } 
}
