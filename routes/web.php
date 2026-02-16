<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/login', function () {
    return response()->json(['message' => 'Please login using POST /login'], 401);
})->name('login');

require __DIR__.'/auth.php';
