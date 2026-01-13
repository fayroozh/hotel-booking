<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Auth\SocialController;

// redirect to provider
Route::get('/auth/redirect/{provider}', [SocialController::class, 'redirect']);

// callback
Route::get('/auth/callback/{provider}', [SocialController::class, 'callback']);
