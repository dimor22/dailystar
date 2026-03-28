<?php

use App\Http\Controllers\KidSharedLinkController;
use App\Http\Controllers\ParentAuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/parent/login');

Route::get('/k/{publicId}', KidSharedLinkController::class)->name('kid.shared-login');

Route::view('/students', 'pages.kid-login')->name('kid.login');

Route::get('/parent/login', [ParentAuthController::class, 'showLogin'])->name('parent.login');
Route::view('/parent/register', 'pages.parent-register')->name('parent.register');
Route::post('/parent/login', [ParentAuthController::class, 'login'])->name('parent.login.submit');
Route::post('/parent/logout', [ParentAuthController::class, 'logout'])->name('parent.logout');

Route::middleware('parent.auth')->group(function () {
	Route::view('/dashboard', 'pages.parent-dashboard')->name('parent.dashboard');
	Route::view('/kids', 'pages.manage-kids')->name('parent.kids');
	Route::view('/tasks', 'pages.manage-tasks')->name('parent.tasks');
});
