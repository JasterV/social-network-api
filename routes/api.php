<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix("/users")->group(function () {
  Route::get("/{username}", [UserController::class, 'get_user']);
  Route::get("/{username}/search", [UserController::class, 'search_by_name']);
  Route::delete("/account", [UserController::class, 'delete_account']);
  Route::put("/edit", [UserController::class, 'edit_user']);
  Route::get("/{username}/followers", [UserController::class, 'get_followers']);
  Route::get("/{username}/following", [UserController::class, 'get_following']);
  Route::post("/follow", [UserController::class, 'follow_user']);
  Route::post("/unfollow", [UserController::class, 'unfollow_user']);
  // Route::get("/{username}/following/online", [UserController::class, 'get_online_users']);
  // Route::get("/{username}/following/offline", [UserController::class, 'get_offline_users']);
  // Route::get("/{username}/closest", [UserController::class, 'get_closest_users']);
});

Route::prefix("/posts")->group(function () {
  Route::post("/publish", [PostController::class, 'new_post']);
  Route::get("/{text}/search", [PostController::class, 'search_by_description']);
  Route::get("/{username}/all", [PostController::class, 'user_posts']);
  Route::get("/{username}/tagged", [PostController::class, 'user_tagged_posts']);
  Route::get("/{username}/shared", [PostController::class, 'user_shared_posts']);
  Route::get("/home", [PostController::class, 'home_posts']);
  Route::get("/{id}", [PostController::class, 'get_post']);
  Route::get("/{id}/tags", [PostController::class, 'get_tags']);
  Route::get("/{id}/likers", [PostController::class, 'get_likers']);
  Route::get("/{id}/commenters", [PostController::class, 'get_commenters']);
  Route::get("/{id}/comments", [PostController::class, 'get_comments']);
  Route::put("/{id}/edit", [PostController::class, 'edit_post']);
  Route::delete("/{id}", [PostController::class, 'delete_post']);
  Route::post("/{id}/like", [PostController::class, 'like']);
  Route::post("/{id}/unlike", [PostController::class, 'unlike']);
  Route::post("/{id}/comment", [PostController::class, 'comment']);
  Route::post("/{id}/share", [PostController::class, 'share']);
});

Route::prefix("/comments")->group(function () {
  Route::delete("/{id}", [PostController::class, 'delete_comment']);
  Route::put("/{id}/edit", [PostController::class, 'edit_comment']);
});

Route::prefix("/notifications")->group(function () {
  Route::get("/all", [NotificationController::class, "get_notifications"]);
  Route::post("/{id}/seen", [NotificationController::class, "mark_not_as_seen"]);
});
