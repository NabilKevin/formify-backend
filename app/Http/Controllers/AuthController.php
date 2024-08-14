<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        if($validate->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $request->all();

        if(!Auth::validate($data)) {
            return response()->json([
                'message' => 'Email or password incorrect'
            ], 401);
        }

        $user = User::firstWhere('email', $data['email']);

        $token = $user->createToken('user login')->plainTextToken;

        $user['accessToken'] = $token;

        return response()->json([
            'message' => 'Login success',
            'user' => $user->only(['name', 'email', 'accessToken'])
        ], 200);

    }
    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout success'
        ], 200);
    }
}
