<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'data' => [
                'user' => [
                    'id' => base64_encode($user->uuid),
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
            'meta' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Could not create token',
            ], 500);
        }

        $user = auth()->user();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => base64_encode($user->uuid),
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
            'meta' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Successfully logged out',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Could not invalidate token',
            ], 500);
        }
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'data' => [
                'id' => base64_encode($user->uuid),
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'meta' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Could not refresh token',
            ], 500);
        }
    }
}