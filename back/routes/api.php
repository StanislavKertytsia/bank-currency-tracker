<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RateController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/banks', [BankController::class, 'index']);
Route::get('/banks/{bank}', [BankController::class, 'show']);

Route::get('/currencies', [CurrencyController::class, 'index']);

Route::get('/rates', [RateController::class, 'index']);
Route::get('/rates/nbu', [RateController::class, 'nbu']);

Route::get('/branches/nearest', [BranchController::class, 'nearest']);

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/notifications', [ProfileController::class, 'updateNotifications']);

    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy']);

    Route::get('/history', [HistoryController::class, 'index']);
    Route::get('/statistics', [StatisticsController::class, 'index']);
});
