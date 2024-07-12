<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FollowerController;
use App\Http\Controllers\Api\FollowingController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 1. Authentication Endpoint

Route::post('v1/auth/register', [AuthController::class, 'register']);

Route::post('v1/auth/login', [AuthController::class, 'login']);

Route::post('v1/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);

// 2. Posts Endpoint

Route::post('v1/posts', [PostController::class, 'create_post'])->middleware(['auth:sanctum']);

Route::delete('v1/posts/{id}', [PostController::class, 'delete_post'])->middleware(['auth:sanctum']);

Route::get('v1/posts', [PostController::class, 'all_post'])->middleware(['auth:sanctum']);

// 3. Following Endpoint

Route::post('v1/users/{username}/follow', [FollowingController::class, 'follow_user'])->middleware(['auth:sanctum']);

Route::delete('v1/users/{username}/unfollow', [FollowingController::class, 'unfollow_user'])->middleware(['auth:sanctum']);

Route::get('v1/users/{username}/following', [FollowingController::class, 'see_following'])->middleware(['auth:sanctum']);

// 4. Followers Endpoint

Route::put('v1/users/{username}/accept', [FollowerController::class, 'accept_request'])->middleware(['auth:sanctum']);

Route::get('v1/users/{username}/followers', [FollowerController::class, 'see_follower'])->middleware(['auth:sanctum']);

// 5. Users Endpoint

Route::get('v1/users', [UserController::class, 'all_users'])->middleware(['auth:sanctum']);

Route::get('v1/users/{username}', [UserController::class, 'detail_users'])->middleware(['auth:sanctum']);
