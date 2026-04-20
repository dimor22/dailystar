<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\KidSharedLinkController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ParentAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'home'])->name('marketing.home');
Route::get('/about', [MarketingController::class, 'about'])->name('marketing.about');
Route::get('/contact', [MarketingController::class, 'contact'])->name('marketing.contact');
Route::post('/contact', [MarketingController::class, 'submitContact'])->name('marketing.contact.submit');
Route::get('/terms', [MarketingController::class, 'terms'])->name('marketing.terms');
Route::get('/privacy', [MarketingController::class, 'privacy'])->name('marketing.privacy');
Route::get('/donate', [MarketingController::class, 'donate'])->name('marketing.donate');
Route::redirect('/pricing', '/')->name('marketing.pricing');

Route::get('/k/{publicId}', KidSharedLinkController::class)->name('kid.shared-login');

Route::view('/students', 'pages.kid-login')->name('kid.login');

Route::get('/parent/login', [ParentAuthController::class, 'showLogin'])->name('parent.login');
Route::view('/parent/register', 'pages.parent-register')->name('parent.register');
Route::view('/parent/pending', 'pages.pending-approval')->name('parent.pending');
Route::post('/parent/login', [ParentAuthController::class, 'login'])->name('parent.login.submit');
Route::post('/parent/logout', [ParentAuthController::class, 'logout'])->name('parent.logout');

Route::middleware('parent.auth')->group(function () {
	Route::view('/dashboard', 'pages.parent-dashboard')->name('parent.dashboard');
	Route::view('/kids', 'pages.manage-kids')->name('parent.kids');
	Route::view('/tasks', 'pages.manage-tasks')->name('parent.tasks');
	Route::redirect('/settings', '/settings/points-store')->name('parent.settings');
	Route::view('/settings/points-store', 'pages.settings-points-store')->name('parent.settings.points-store');
	Route::view('/settings/star-rewards', 'pages.settings-star-rewards')->name('parent.settings.star-rewards');
	Route::view('/settings/streak-bonuses', 'pages.settings-streak-bonuses')->name('parent.settings.streak-bonuses');

	// Billing & Subscription
	Route::get('/billing', [BillingController::class, 'show'])->name('parent.billing');
	Route::get('/billing/checkout', [BillingController::class, 'checkout'])->name('parent.billing.checkout');
	Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('parent.billing.cancel');
	Route::post('/billing/resume', [BillingController::class, 'resume'])->name('parent.billing.resume');
	Route::post('/billing/portal', [BillingController::class, 'portal'])->name('parent.billing.portal');
});

// Admin
Route::middleware(['parent.auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
	Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
});
