<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{id}', [TicketController::class, 'show']);
    });
    Route::prefix('ticket-categories')->group(function () {
        Route::get('/', [TicketController::class, 'getCategories']);
    });
    Route::prefix('activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::get('/{idOrSlug}', [ActivityController::class, 'show']);
    });
});
