<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $user = User::where('email', '=', $credentials['email'], 'and')->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                return response()->json(['message' => 'Invalid email or password.'], 401);
            }

            if (! $user->is_active) {
                return response()->json(['message' => 'This account is disabled.'], 403);
            }

            $user->tokens()->delete();

            return response()->json([
                'token' => $user->createToken('api-token')->plainTextToken,
                'user' => $this->userPayload($user),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->userPayload($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $request->user()->tokens()->where('id', $token->id)->delete();
        }

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

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            return response()->json([
                'message' => 'Profile updated.',
                'user' => $this->userPayload($user->fresh()),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'branch_id' => $user->branch_id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }
}
