<?php

namespace App\Services\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Role::with('permissions:id,name')
            ->orderBy('name')
            ->paginate(min(max($perPage, 1), 100));
    }

    public function create(array $data): Role
    {
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return $role->load('permissions:id,name');
    }

    public function find(int $id): Role
    {
        return Role::with('permissions:id,name')->findOrFail($id);
    }

    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);

        if (isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions:id,name');
    }

    public function delete(int $id): void
    {
        $role = Role::findOrFail($id);

        abort_if($role->name === 'super-admin', 422, 'The super-admin role cannot be deleted.');

        $role->delete();
    }
}
