<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ApiModelResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends BaseCrudController
{
    protected string $resource = 'users';

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAbility($request, 'create');

        $data = $this->prepareData($request->validate($this->config()['rules']), $request->user());
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $this->assertTenantIntegrity($data, $request->user());
        $user = $this->config()['model']::create($data);
        $user->syncRoles($roles);

        return (new ApiModelResource($user->fresh()->load('roles:id,name')))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): ApiModelResource
    {
        $this->authorizeAbility($request, 'update');

        $model = $this->scopedQuery($request->user())->findOrFail($id);
        $rules = $this->updateRules($this->config()['rules']);
        $rules['email'] = ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($model->id)];

        $data = $this->prepareData($request->validate($rules), $request->user(), false);
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $this->assertTenantIntegrity(array_merge($model->getAttributes(), $data), $request->user());

        $model->update($data);

        if ($roles !== null) {
            $model->syncRoles($roles);
        }

        return new ApiModelResource($model->fresh()->load('roles:id,name'));
    }
}
