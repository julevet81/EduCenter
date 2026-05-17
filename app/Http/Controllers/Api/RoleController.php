<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('roles.view'), 403);

        return response()->json(Role::with('permissions:id,name')->orderBy('name')->paginate(
            min(max((int) $request->integer('per_page', 15), 1), 100)
        ));
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('roles.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json($role->load('permissions:id,name'), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->can('roles.view'), 403);

        return response()->json(Role::with('permissions:id,name')->findOrFail($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->can('roles.update'), 403);

        $role = Role::findOrFail($id);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if (isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions']);
        }

        return response()->json($role->load('permissions:id,name'));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->can('roles.delete'), 403);

        $role = Role::findOrFail($id);
        abort_if($role->name === 'super-admin', 422, 'The super-admin role cannot be deleted.');

        $role->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
