<?php

namespace App\Http\Controllers;

use App\Models\{User};
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class AuthController extends Controller
{
    public function usernameExistsOrNot(Request $request)
    {
        $username = $request->input('username');
        $user = User::where('username', $username)->first();
        if ($user) {
            return ResponseHelper::success(['exists' => true]);
        } else {
            return ResponseHelper::success(['exists' => false]);
        }
    }

    public function register(Request $request)
    {
        
        $validation = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::error($validation->errors()->first());
        }

        $user = User::create([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            "role_type" => $request->input('role')
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return ResponseHelper::success(['user' => $user, 'token' => $token]);
    }

    public function login(Request $request){
        $validation = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::error($validation->errors()->first());
        }

        $user = User::where('username', $request->input('username'))->orWhere('email', $request->input('username'))->first();
        if (!$user) {
            return ResponseHelper::error('User not found');
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            return ResponseHelper::error('Invalid password');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return ResponseHelper::success(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request){
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return ResponseHelper::success('Logged out successfully');
    }
}
