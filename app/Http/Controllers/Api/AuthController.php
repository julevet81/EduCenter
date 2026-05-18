<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = $this->auth->register($data);

            return response()->json([
                'message' => 'Registration successful.',
                'user' => $this->auth->payload($user),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $result = $this->auth->login($credentials);

            if (! $result) {
                return response()->json(['message' => 'Invalid email or password.'], 401);
            }

            return response()->json([
                'token' => $result['token'],
                'user' => $this->auth->payload($result['user']),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->auth->payload($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validate([
                'full_name' => ['sometimes', 'string', 'max:255'],
                'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
                'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            ]);

            $user = $this->auth->updateProfile($user, $data);

            return response()->json([
                'message' => 'Profile updated.',
                'user' => $this->auth->payload($user),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
