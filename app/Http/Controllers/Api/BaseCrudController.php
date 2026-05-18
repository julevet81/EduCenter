<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiModelResource;
use App\Services\Api\CrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

abstract class BaseCrudController extends Controller
{
    protected string $resource;

    public function __construct(
        protected readonly CrudService $crud,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizeAbility($request, 'view');

        return ApiModelResource::collection($this->crud->paginate($this->resource, $request->user(), [
            'include' => $request->query('include'),
            'search' => $request->query('search'),
            'per_page' => $request->integer('per_page', 15),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAbility($request, 'create');

        $model = $this->crud->create(
            $this->resource,
            $request->validate($this->crud->storeRules($this->resource)),
            $request->user()
        );

        return (new ApiModelResource($model))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): ApiModelResource
    {
        $this->authorizeAbility($request, 'view');

        return new ApiModelResource($this->crud->find(
            $this->resource,
            $request->user(),
            $id,
            $request->query('include')
        ));
    }

    public function update(Request $request, int $id): ApiModelResource
    {
        $this->authorizeAbility($request, 'update');

        return new ApiModelResource($this->crud->update(
            $this->resource,
            $request->user(),
            $id,
            $request->validate($this->crud->updateRules($this->resource))
        ));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorizeAbility($request, 'delete');

        $this->crud->delete($this->resource, $request->user(), $id);

        return response()->json(['message' => 'Deleted successfully.']);
    }

    protected function authorizeAbility(Request $request, string $ability): void
    {
        abort_unless($request->user()->can($this->resource.'.'.$ability), 403);
    }
}
