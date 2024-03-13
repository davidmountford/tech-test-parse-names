<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/upload');

// CSV Uploader prototype
Route::name('upload')
    ->controller(UploadController::class)
    ->group(function () {
        Route::get('/upload', 'show');
        Route::post('/upload', 'store');
    });