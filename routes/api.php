<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GenerateImage;
use App\Http\Controllers\RecoverPdf;
use App\Http\Controllers\RecoverPromotions;
use App\Http\Controllers\RecoverTemplates;
use App\Http\Middleware\TokenAuth;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(TokenAuth::class)->group(function () {
    Route::post('generate-image', GenerateImage::class);
    Route::post('print', [GenerateImage::class, 'print']);
    Route::get('recoverPdf', RecoverPdf::class);
    Route::get('recoverPromotions', RecoverPromotions::class);
    Route::get('recoverTemplates', RecoverTemplates::class);
});


