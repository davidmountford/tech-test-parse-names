<?php

namespace App\Services\Uploads;

use Symfony\Component\HttpFoundation\FileBag;

interface UploadsInterface {
    public function convert(FileBag $files): self;

    public function outputToArray(): array;
}