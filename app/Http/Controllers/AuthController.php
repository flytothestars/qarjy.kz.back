<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\AdminLoginRequest;
use App\Http\Requests\Auth\ConfirmCodeRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PinLoginRequest;
use App\Http\Requests\Auth\SetPinRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function adminLogin(AdminLoginRequest $request): JsonResponse
    {
        $user = User::manager()->where("email", $request->email)->first();
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => [
                    'password' => 'Неправильный пароль'
                ]
            ], 422);
        }
        $token = $user->createToken('app');
        return response()->json([
            'status' => 'success',
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }

    public function login(LoginRequest $request)
    {
        $phone = User::phoneToDigits($request->phone);
        $user = User::where("phone", $phone)->first();

        if (!$user) {
            $user = new User();
            $user->phone = $phone;
            $user->role = 'customer';
            $user->save();
        }

        // $user->sendSMSCode($request->test);

        $token = $user->createToken('app');
        return response()->json([
            'status' => 'success',
            'sms' => true,
            'token' => $token->plainTextToken
        ]);
    }

    public function confirmCode(ConfirmCodeRequest $request)
    {
        $user = User::where("phone", User::phoneToDigits($request->phone))
            ->where("sms_code", $request->code)
            ->first();
        if (!$user) {
            return response()->json([
                'errors' => [
                    'code' => "Неправильный код, попробуйте еще раз."
                ]
            ], 422);
        }

        $token = $user->createToken('app');
        return response()->json([
            'status' => 'success',
            'token' => $token->plainTextToken
        ]);
    }

    public function setPinCode(SetPinRequest $request)
    {
        $user = auth()->user();
        $user->pin_code = Hash::make($request->code);
        $user->save();
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function loginByPinCode(PinLoginRequest $request)
    {
        $user = User::where("phone", User::phoneToDigits($request->phone))->first();
        if (!$user) {
            return response()->json([
                'errors' => [
                    'phone' => 'User not found'
                ]
            ], 422);
        }
        if (!Hash::check($request->code, $user->pin_code)) {
            return response()->json([
                'errors' => [
                    'code' => 'Wrong pin code'
                ]
            ], 422);
        }
        $token = $user->createToken('app');
        return response()->json([
            'status' => 'success',
            'token' => $token->plainTextToken
        ]);
    }

    public function tokenCheck(): JsonResponse
    {
        return response()->json([
            'auth' => auth()->check(),
        ]);
    }

    public function getMe(): JsonResponse
    {
        return response()->json([
            'user' => auth()->user(),
        ]);
    }

}
