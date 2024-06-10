<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatisticsController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::controller(StatisticsController::class)->group(function () {
    Route::get('statistics', 'index');
    Route::post('statistics', 'store');
    Route::get('statistics/data', 'getData');
    Route::post('statistics/command', 'sendCommand');
});



