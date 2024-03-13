<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class UploadTest extends TestCase
{
    use WithFaker;

    /**
     * Upload Page Loads
     *
     * @return void
     */
    public function test_upload_page_loads()
    {
        $response = $this->get('/upload');

        $response->assertStatus(200);
    }

    public function test_uploaded_page_fails_without_payload()
    {
        $response = $this->post('/upload');

        $response->assertStatus(302);
    }

    public function test_uploaded_page_works_with_payload()
    {
        $response = $this->post('/upload', [
            'csv' => UploadedFile::fake()
                ->createWithContent(
                    $this->faker()->word() . '.csv',
                    implode("\n", ['homeowner,', 'Dr Bloggs,', 'Mr Man'])
                )
        ]);
        
        $response->assertStatus(200);
        $response->assertSeeTextInOrder(['Dr', 'Bloggs', 'Mr', 'Man']);
        $response->assertDontSeeText('homeowner');
    }
}
