<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\TicketCategoryController;
use App\Http\Controllers\TicketController;

Route::middleware(['auth:sanctum'])->get('/user', \App\Http\Controllers\UserController::class);

// Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('events', EventController::class);
    Route::apiResource('ticket-categories', TicketCategoryController::class);
});

// Customer/Transaction Routes
Route::get('/tickets/quota/{categoryId}', [TicketController::class, 'getQuota']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings', [TicketController::class, 'store']);
    Route::get('/bookings/my', [TicketController::class, 'myBookings']);
});

require __DIR__ . '/auth.php';

