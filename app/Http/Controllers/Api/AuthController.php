<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                'usernameOrEmail' => __('auth.failed'),
            ]);
        }
        $user = User::where($identifierColumn, $usernameOrEmail)->first();
        $token = $user->createToken('AuthToken')->accessToken;
        return $this->jsonResponse(message: 'Login Successful', data: ['user' => $user, 'token' => $token]);
    }
    public function register(RegisterRequest $registerRequest)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $registerRequest->name,
                'email' => $registerRequest->email,
                'username' => $registerRequest->username,
                'password' => $registerRequest->password
            ]);
            $openingBalanceAccount = Account::create([
                'name' => $user->username . '-OpeningBalance',
                'account_group_id' => 4,
            ]);
            $transferChargeAccount = Account::create([
                'name' => $user->username . '-TransferCharge',
                'account_group_id' => 4,
            ]);
            $user->update(['opening_balance_account_id' => $openingBalanceAccount->id, 'transfer_charge_account_id' => $transferChargeAccount->id]);
            $token = $user->createToken('AuthToken')->accessToken;
            DB::commit();
            return $this->jsonResponse(message: 'Registration Successful', data: ['user' => $user, 'token' => $token]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return $this->jsonResponse(message: __('errors.general_error'), status: 500);
        }
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
