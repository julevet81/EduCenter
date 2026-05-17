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
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', '=', $credentials['email'], 'and')->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                return response()->json(['message' => 'Email ou mot de passe incorrect.'], 401);
            }

            if (! $user->is_active) {
                return response()->json(['message' => 'Ce compte est désactivé.'], 403);
            }

            // One-session policy: revoke old tokens
            $user->tokens()->delete();

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => $this->userPayload($user),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur serveur.'], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            return response()->json($this->userPayload($request->user()));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur serveur.'], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->user()->currentAccessToken();

            if ($token) {
                // Use query delete to avoid static analysis issue about delete() on the model
                $request->user()->tokens()->where('id', $token->id)->delete();
            }

            return response()->json(['message' => 'Déconnecté avec succès.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur serveur.'], 500);
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $data = $request->validate([
                'full_name' => 'sometimes|string|max:255',
                'phone'     => 'sometimes|nullable|string|max:30',
                'password'  => 'sometimes|string|min:8|confirmed',
            ]);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            return response()->json([
                'message' => 'Profil mis à jour.',
                'user'    => $this->userPayload($user->fresh()),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur serveur.'], 500);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function userPayload(User $user): array
    {
        return [
            'id'          => $user->id,
            'full_name'   => $user->full_name,
            'email'       => $user->email,
            'phone'       => $user->phone,
            'is_active'   => $user->is_active,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }
}
