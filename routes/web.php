<?php

use App\Http\Controllers\ParentAuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('/login', 'pages.kid-login')->name('kid.login');

Route::get('/parent/login', [ParentAuthController::class, 'showLogin'])->name('parent.login');
Route::post('/parent/login', [ParentAuthController::class, 'login'])->name('parent.login.submit');
Route::post('/parent/logout', [ParentAuthController::class, 'logout'])->name('parent.logout');

Route::middleware('parent.auth')->group(function () {
	Route::view('/dashboard', 'pages.parent-dashboard')->name('parent.dashboard');
});
