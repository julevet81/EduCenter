<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roles,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('roles.view'), 403);

        return response()->json($this->roles->paginate((int) $request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('roles.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        return response()->json($this->roles->create($data), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->can('roles.view'), 403);

        return response()->json($this->roles->find($id));
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

        return response()->json($this->roles->update($id, $data));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->can('roles.delete'), 403);

        $this->roles->delete($id);

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
