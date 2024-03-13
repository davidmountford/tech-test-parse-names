<?php

namespace Tests\Unit;

use App\Exceptions\InvalidFileTypeException;
use App\Services\Uploads\HomeownerUploads;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class HomeownerUploadsTest extends TestCase
{
    private HomeownerUploads $class;

    private UploadedFile $file;

    private UploadedFile $badFile;

    public function setUp(): void
    {
        $this->class = new HomeownerUploads();
        $this->file = UploadedFile::fake()
        ->createWithContent(
            'Good File.csv',
            implode("\n", ['homeowner,', 'Dr Bloggs,', 'Mr Man'])
        );

        $this->badFile = UploadedFile::fake()
        ->createWithContent(
            'Bad File.png',
            null,
        );
    }

    public function test_exception_thrown_when_non_csv()
    {
        $this->expectException(InvalidFileTypeException::class);

        $this->class->processCSV($this->badFile);
    }

    public function test_can_process_csv()
    {
        $output = $this->class->processCSV($this->file);
        
        $this->assertIsArray($output);
        // Should have 2 "people"
        $this->assertCount(2, $output);
    }

    public function test_can_parse_entry_into_people()
    {
        $person = $this->class->parseEntryIntoPeople('Mr Joe Bloggs');
        $this->assertIsArray($person);
        $this->assertCount(1, $person);

        $firstPerson = collect($person)->first();
        $this->assertIsArray($firstPerson);
        $this->assertCount(4, $firstPerson);
        $this->assertArrayHasKey('title', $firstPerson);
        $this->assertArrayHasKey('first_name', $firstPerson);
        $this->assertArrayHasKey('initial', $firstPerson);
        $this->assertArrayHasKey('last_name', $firstPerson);

        $this->assertEquals('Mr', $firstPerson['title']);
        $this->assertEquals('Joe', $firstPerson['first_name']);
        $this->assertEquals(null, $firstPerson['initial']);
        $this->assertEquals('Bloggs', $firstPerson['last_name']);

        $person = $this->class->parseEntryIntoPeople('Mr and Mrs Joe Bloggs');
        $this->assertIsArray($person);
        $this->assertCount(2, $person);
    
        $firstPerson = collect($person)->first();
        $this->assertIsArray($firstPerson);
        $this->assertCount(4, $firstPerson);
        $this->assertArrayHasKey('title', $firstPerson);
        $this->assertArrayHasKey('first_name', $firstPerson);
        $this->assertArrayHasKey('initial', $firstPerson);
        $this->assertArrayHasKey('last_name', $firstPerson);

        $this->assertEquals('Mr', $firstPerson['title']);
        $this->assertEquals(null, $firstPerson['first_name']);
        $this->assertEquals(null, $firstPerson['initial']);
        $this->assertEquals('Bloggs', $firstPerson['last_name']);

        $secondPerson = collect($person)->last();
        $this->assertIsArray($secondPerson);
        $this->assertCount(4, $secondPerson);
        $this->assertArrayHasKey('title', $secondPerson);
        $this->assertArrayHasKey('first_name', $secondPerson);
        $this->assertArrayHasKey('initial', $secondPerson);
        $this->assertArrayHasKey('last_name', $secondPerson);

        $this->assertEquals('Mrs', $secondPerson['title']);
        $this->assertEquals('Joe', $secondPerson['first_name']);
        $this->assertEquals(null, $secondPerson['initial']);
        $this->assertEquals('Bloggs', $secondPerson['last_name']);
    }

    public function testCanParsePerson()
    {
        $person = 'Capt Kaizad Doctor';
        $output = $this->class->parsePerson($person);
        
        $this->assertIsArray($output);
        $this->assertCount(4, $output);
        $this->assertArrayHasKey('title', $output);
        $this->assertArrayHasKey('first_name', $output);
        $this->assertArrayHasKey('initial', $output);
        $this->assertArrayHasKey('last_name', $output);

        
        $this->assertEquals('Capt', $output['title']);
        $this->assertEquals('Kaizad', $output['first_name']);
        $this->assertEquals(null, $output['initial']);
        $this->assertEquals('Doctor', $output['last_name']);
    }

    public function test_can_parse_entry_into_multiple_people()
    {
        $people = ['Capt Kaizad Doctor', 'Mr Adrian Economakis'];
        $output = $this->class->parseMultiplePeople(collect($people));

        $this->assertIsIterable($output);
        $this->assertCount(2, $output);
        
        $firstPerson = collect($output)->first();
        $this->assertArrayHasKey('title', $firstPerson);
        $this->assertArrayHasKey('first_name', $firstPerson);
        $this->assertArrayHasKey('initial', $firstPerson);
        $this->assertArrayHasKey('last_name', $firstPerson);

        
        $this->assertEquals('Capt', $firstPerson['title']);
        $this->assertEquals('Kaizad', $firstPerson['first_name']);
        $this->assertEquals(null, $firstPerson['initial']);
        $this->assertEquals('Doctor', $firstPerson['last_name']);

        $secondPerson = collect($output)->last();
        $this->assertArrayHasKey('title', $secondPerson);
        $this->assertArrayHasKey('first_name', $secondPerson);
        $this->assertArrayHasKey('initial', $secondPerson);
        $this->assertArrayHasKey('last_name', $secondPerson);

        $this->assertEquals('Mr', $secondPerson['title']);
        $this->assertEquals('Adrian', $secondPerson['first_name']);
        $this->assertEquals(null, $secondPerson['initial']);
        $this->assertEquals('Economakis', $secondPerson['last_name']);
    }

    public function test_can_trim_entry()
    {
        $output = $this->class->trimEntry('     $$#     lots....!!! $£"! of. spaces!           ');
        $this->assertEquals('lots of spaces', $output);
    }

    public function test_can_validate_person()
    {
        $output = $this->class->validatePerson([
            'Not a person'
        ]);

        $this->assertEquals(false, $output);

        $output = $this->class->validatePerson([
            'title' => 'Valid',
            'last_name' => 'Person',
        ]);

        $this->assertEquals(true, $output);
    }
}
