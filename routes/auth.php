<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;

Route::middleware("auth:api")->get("/logged", function (ApiRequest $request) {
    $user = $request->user();
    return Controller::sendResponse(["user" => $user], "Authorized");
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
