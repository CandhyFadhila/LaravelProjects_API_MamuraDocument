<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Documents\DocumentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'docs'], function () {
    Route::post('/login', [LoginController::class, 'login']);

    Route::middleware(['auth:sanctum', 'custom.throttle:60,1'])->group(function () {
        Route::get('/logout', [LoginController::class, 'logout']);
        Route::post('/get-file', [DocumentController::class, 'getFile']);
        Route::post('/upload-file-multiple', [DocumentController::class, 'uploadMultipleFiles']);
        Route::post('/delete-file-multiple', [DocumentController::class, 'deleteFile']);
    });
});
