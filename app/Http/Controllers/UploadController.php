<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidFileTypeException;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use App\Services\Uploads\HomeownerUploads;

class UploadController extends Controller
{
    public function show(): View
    {
        return view('upload');
    }

    public function store(Request $request, UploadService $uploadServiceProvider): View
    {
        $request->validate([
            'csv' => ['required', 'mimes:csv,txt'],
        ]);

        // Pass the configuration to the service and get the specific uploader
        // using the global UploadserviceProvider
        /** @var HomeownerUploads $service */
        $service = $uploadServiceProvider
            ->getUploader(UploadService::homeowner);

        try {
            $output = $service
                // Convert the files
                ->convert($request->files)
                // Output the conversion to an array
                ->outputToArray();
        } catch (InvalidFileTypeException $e) {
            return response()->view('uploaded', [
                'errors' => [
                    'csv' => 'File type must be a .csv file, please check your file and try again'
                ]
            ], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        // Serve the output
        return view('uploaded', ['output' => $output]);
    }
}
