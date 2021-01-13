<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterUserRequest $request)
    {
        $input = $request->validated();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        return Controller::sendResponse(["user" => $user], 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginUserRequest $request)
    {
        if (
            !Auth::attempt(['username' => $request->name_email, 'password' => $request->password]) &&
            !Auth::attempt(['email' => $request->name_email, 'password' => $request->password])
        ) {
            return Controller::sendError('Unauthorized', [], 401);
        }

        $authToken =  Auth::user()->createToken('authToken')->accessToken;
        return Controller::sendResponse(["user" => Auth::user(), "token" => $authToken], 'User login successfully.');
    }

    public function logout(ApiRequest $_) {
        Auth::user()->token()->revoke();
        return Controller::sendResponse([], 'User logout successfully.');
    }
}
