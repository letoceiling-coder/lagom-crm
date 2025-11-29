<?php

use Illuminate\Support\Facades\Route;

// Единая точка входа для SPA (все маршруты обрабатываются Vue Router на клиенте)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
