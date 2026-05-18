<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ApiModelResource;
use App\Services\Api\CrudService;
use App\Services\Api\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseCrudController
{
    protected string $resource = 'users';

    public function __construct(
        CrudService $crud,
        private readonly UserService $users,
    ) {
        parent::__construct($crud);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAbility($request, 'create');

        return (new ApiModelResource(
            $this->users->create($request->validate($this->users->storeRules()), $request->user())
        ))->response()->setStatusCode(201);
    }

    public function update(Request $request, int $id): ApiModelResource
    {
        $this->authorizeAbility($request, 'update');

        $user = $this->users->findForUpdate($request->user(), $id);

        return new ApiModelResource($this->users->update(
            $user,
            $request->validate($this->users->updateRules($user)),
            $request->user()
        ));
    }
}
