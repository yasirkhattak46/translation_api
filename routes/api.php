<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;
Route::get('/test', function () {
    return response()->json(['status' => 'API is working!']);
});
Route::post('token/create', [AuthController::class, 'createToken']); // admin-only usage

Route::middleware(['api.key'])->group(function () {
    Route::get('translations/export/{locale}', [TranslationController::class, 'export']);
    Route::get('/translations/search', [TranslationController::class, 'search'])
        ->name('translations.search');
    Route::apiResource('translations', TranslationController::class)->only(['index','show','store','update','destroy']);

});
