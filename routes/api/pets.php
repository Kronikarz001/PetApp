<?php

use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

Route::post('pets/{pet}/upload', [PetController::class, 'uploadFile'])
    ->name('pets.upload');

Route::apiResource('pets', PetController::class);
