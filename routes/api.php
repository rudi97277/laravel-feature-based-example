<?php

use App\Features\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('test', [UserController::class, 'index']);
