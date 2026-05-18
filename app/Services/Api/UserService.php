<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class UserService
{
    private const RESOURCE = 'users';

    public function __construct(
        private readonly CrudService $crud,
    ) {}

    public function storeRules(): array
    {
        return $this->crud->storeRules(self::RESOURCE);
    }

    public function updateRules(User $user): array
    {
        $rules = $this->crud->updateRules(self::RESOURCE);
        $rules['email'] = ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];

        return $rules;
    }

    public function findForUpdate(User $actor, int $id): User
    {
        return $this->crud->scopedQuery(self::RESOURCE, $actor)->findOrFail($id);
    }

    public function create(array $data, User $actor): Model
    {
        $data = $this->crud->prepareData(self::RESOURCE, $data, $actor);
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $this->crud->assertTenantIntegrity($data, $actor);

        $user = $this->crud->config(self::RESOURCE)['model']::create($data);
        $user->syncRoles($roles);

        return $user->fresh()->load('roles:id,name');
    }

    public function update(User $user, array $data, User $actor): Model
    {
        $data = $this->crud->prepareData(self::RESOURCE, $data, $actor, false);
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $this->crud->assertTenantIntegrity(array_merge($user->getAttributes(), $data), $actor);

        $user->update($data);

        if ($roles !== null) {
            $user->syncRoles($roles);
        }

        return $user->fresh()->load('roles:id,name');
    }
}
