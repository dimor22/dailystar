<?php

use App\Http\Controllers\Api\TaskCompletionController;
use Illuminate\Support\Facades\Route;

Route::post('/complete-task', [TaskCompletionController::class, 'store']);
