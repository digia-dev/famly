<?php

use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\FinancialController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\CmsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);
Route::middleware('auth:sanctum')->post('/logout', [AuthApiController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// CMS Master Routes
Route::prefix('cms')->middleware(['auth:sanctum', 'role:dins'])->group(function () {
    Route::apiResource('families', FamilyController::class);
    Route::apiResource('financials', FinancialController::class);
    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/impersonate', [UserController::class, 'impersonate']);
    Route::apiResource('activities', ActivityController::class);

    
    // Content & Categories
    Route::get('content/categories', [ContentController::class, 'categories']);
    Route::post('content/names', [ContentController::class, 'storeName']);
    
    // System & Backup
    Route::get('system/stats', [CmsApiController::class, 'stats']);
    Route::get('transactions', [CmsApiController::class, 'transactions']);
    Route::get('analytics', [CmsApiController::class, 'analytics']);
    Route::get('wallets', [CmsApiController::class, 'wallets']);
    Route::post('system/backup', [CmsApiController::class, 'download']);
});

Route::post('/telegram-webhook', [\App\Http\Controllers\TelegramBotController::class, 'handleWebhook']);


