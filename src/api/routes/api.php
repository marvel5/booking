<?php

use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ResourceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('resources', [ResourceController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
});
