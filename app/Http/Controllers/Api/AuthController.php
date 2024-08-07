<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $loginRequest)
    {
        $usernameOrEmail = $loginRequest->usernameOrEmail;
        $password = $loginRequest->password;
        $identifierColumn = 'username';
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            $identifierColumn = 'email';
        }
        if (!Auth::guard('web')->attempt(["$identifierColumn" => $usernameOrEmail, 'password' => $password])) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
        $user = User::where($identifierColumn, $usernameOrEmail)->first();
        $token = $user->createToken('AuthToken')->accessToken;
        return $this->jsonResponse(message: 'Login Successful', data: ['user' => $user, 'token' => $token]);
    }
    public function register(RegisterRequest $registerRequest)
    {
        $user = User::create([
            'name' => $registerRequest->name,
            'email' => $registerRequest->email,
            'username' => $registerRequest->username,
            'password' => $registerRequest->password
        ]);
        $token = $user->createToken('AuthToken')->accessToken;
        return $this->jsonResponse(message: 'Registration Successful', data: ['user' => $user, 'token' => $token]);
    }
    public function logout()
    {
        Auth::user()->token()->revoke();
        return $this->jsonResponse(message: 'Logged Out', data: []);
    }
    public function me()
    {
        return $this->jsonResponse(message: 'User fetched', data: ['user' => auth()->user()]);
    }
}
