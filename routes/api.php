<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('articles', ArticleController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('users', UserController::class);
});
