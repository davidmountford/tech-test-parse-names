<?php

namespace App\Services\Uploads;

use App\Exceptions\InvalidFileTypeException;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\FileBag;

class HomeownerUploads extends BaseUploads implements UploadsInterface
{
    public array $honorifics = [
        'Mr',
        'Mister',
        'Mrs',
        'Miss',
        'Ms',
        'Dr',
        'Doctor',
        'Lord',
        'Lady',
        'Prof',
        'Professor',
        'Reverend',
        'Rev',
        'Capt',
        'Captain',
    ];

    public Collection $people;

    public Collection $invalid;

    public array $processed;

    public function convert(FileBag $files): self
    {        
        $this->people = new Collection();
        $this->invalid = new Collection();

        // Process each of the files with ProcessCSV.
        collect($files)->each(
            fn ($file) => $this->processed[$file->getClientOriginalName()] = $this->processCSV($file)
        );

        // One done, let's validate and combine the results.
        collect($this->processed)->each(function(array $array) {
          collect($array)->each(function ($person) {
            if($this->validatePerson($person)) {
                $this->people->add($person);
            } else {
                $this->invalid->add($person);
            }
          });  
        });

        return $this;
    }

    public function processCSV(File $file): array
    {
        // We only want to use .csv files, so can throw an Exception if it's not to get
        // caught.
        if ($file->guessExtension() !== 'csv') {
            throw new InvalidFileTypeException("File is not a .csv, please use the correct uploader");
        }

        /** 
         * @var Collection $contents 
         * Get the contents of the CSV file
         * This does assume everything is in one line as per the example,
         * perhaps should do some checking for that
        */
        return collect(str_getcsv($file->getContent()))
            // Let's parse each entry
            ->map(fn($item) => $this->parseEntryIntoPeople($item))
            // Get rid of any empty lines or junk that couldn't be parsed at all
            ->flatMap(fn($level) => $level)
            ->filter()
            ->toArray();
    }

    public function parseEntryIntoPeople(string $entry): ?array
    {
        $entry = $this->trimEntry($entry);
        // Check to see if it's one person by splitting by "and", "&" and "+"
        $owners = collect(preg_split('/(\+)|\b(and)\b|&/', $entry));

        // Either way, parse the first entry. If it's a full name, it'll work properly
        // and if it's just an honorific before another name we'll account for it later.
        $person = $this->parsePerson($owners->first());

        $people = new Collection();

        // if there's only one, then add it to people. We're done here.
        if ($owners->count() === 1) {
            $people->add($person);
        }

        // if there's more than one, there's more work to do.
        if ($owners->count() > 1) {
            // See ParseMultiplePeople for logic
            foreach($this->parseMultiplePeople($owners) as $owner) {
                $people->add($owner);
            };
        }   

        // Return the people!
        return $people->toArray();
    }

    /** Function to Create a Person Record */
    public function parsePerson(string $prospectivePerson)
    {
        // Template
        $personTemplate = [
            'title' => null,
            'first_name' => null,
            'initial' => null,
            'last_name' => null,
        ];

        $person = $personTemplate;

        // Let's look for the title
        $owner = collect(explode(' ', $this->trimEntry($prospectivePerson)));
        if (in_array($owner->first(), $this->honorifics)) {
            $person['title'] = $owner->first();
        };        
    
        // If there's a title and something else, let's assume it's the last name.
        if ($owner->count() >= 2) {
            $person['last_name'] = $owner->last();
        }

        // If there's three entries, we're assuming it's either a first name or an initial.
        if ($owner->count() >= 3) {
            // Get rid of the first and last entries we've parsed.
            $owner->shift();
            $owner->pop();

            if (strlen($owner->first()) === 1) {
                $person['initial'] = $owner->first();
            } else {
                $person['first_name'] = $owner->first();
            }
        }

        if ($person === $personTemplate) {
            return null;
        }

        return $person;
    }

    public function parseMultiplePeople(Collection $owners)
    {
        // Run all the people through the people parser.
        $owners = $owners->map(
            fn($owner) => $this->parsePerson($owner)
        );

        // If there are blanked entries for last name, use the others for 
        // last names. Could be problems if there are more than one though.
        // Ultimately, there's no way to know for certain.
        $lastNames = $owners->pluck('last_name')->filter();

        $owners = $owners->map(function ($person) use ($lastNames) {
            if ($person !== null && $person['last_name'] === null) {
                $person['last_name'] = $lastNames->first();
            }

            return $person;
        });

        return $owners;
    }
    
    public function trimEntry(string $entry): string
    {
        // Trim the whitespace and punctuation from the string,
        // with the notable exception of "&" (and) and "-" (for double barrelled names).
        return trim(preg_replace(['/[^\w\s]+/','/\s+/',], ' ', $entry));
    }

    public function validatePerson(array $person): bool
    {
        // Following the rules, you're only a valid person with a 
        // title and a last_name.
        if (empty($person['title'])) {
            return false;
        }

        if (empty($person['last_name'])) {
            return false;
        }

        return true;
    }

    public function outputToArray(): array
    {
        return $this->people->toArray();
    }

    /** Could be useful later to output invalid entries */
    public function outputInvalidToArray(): array
    {
        return $this->invalid->toArray();
    }
}
