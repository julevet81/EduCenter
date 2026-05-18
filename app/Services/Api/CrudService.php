<?php

namespace App\Services\Api;

use App\Models\User;
use App\Support\ApiResourceRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CrudService
{
    private const MAX_PER_PAGE = 100;

    public function __construct(
        private readonly TenantIntegrityService $tenantIntegrity,
    ) {}

    public function config(string $resource): array
    {
        return ApiResourceRegistry::get($resource);
    }

    public function paginate(string $resource, User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $this->scopedQuery($resource, $user);
        $this->applyIncludes($query, $resource, $filters['include'] ?? null);
        $this->applySearch($query, $resource, $filters['search'] ?? null);

        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), self::MAX_PER_PAGE);

        return $query->latest('id')->paginate($perPage);
    }

    public function create(string $resource, array $data, User $user): Model
    {
        $data = $this->prepareData($resource, $data, $user);
        $this->tenantIntegrity->assert($data, $user);

        return $this->config($resource)['model']::create($data)->fresh();
    }

    public function find(string $resource, User $user, int $id, ?string $include = null): Model
    {
        $query = $this->scopedQuery($resource, $user);
        $this->applyIncludes($query, $resource, $include);

        return $query->findOrFail($id);
    }

    public function update(string $resource, User $user, int $id, array $data): Model
    {
        $model = $this->scopedQuery($resource, $user)->findOrFail($id);
        $data = $this->prepareData($resource, $data, $user, false);

        $this->tenantIntegrity->assert(array_merge($model->getAttributes(), $data), $user);

        $model->update($data);

        return $model->fresh();
    }

    public function delete(string $resource, User $user, int $id): void
    {
        $this->scopedQuery($resource, $user)->findOrFail($id)->delete();
    }

    public function scopedQuery(string $resource, User $user): Builder
    {
        $model = $this->config($resource)['model'];
        $query = $model::query();

        match ($resource) {
            'classrooms', 'expenses' => $query->whereHas('branch', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'student-documents' => $query->whereHas('student', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'schedules', 'attendance-sessions', 'exams', 'enrollments' => $query->whereHas('group', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'attendance-records' => $query->whereHas('session.group', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'invoices' => $query->whereHas('student', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'payments' => $query->whereHas('invoice.student', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'exam-results' => $query->whereHas('exam.group', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'payrolls' => $query->whereHas('teacher', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'notifications' => $query->whereHas('user', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            default => $query->where('tenant_id', $user->tenant_id),
        };

        return $query;
    }

    public function storeRules(string $resource): array
    {
        return $this->config($resource)['rules'];
    }

    public function updateRules(string $resource): array
    {
        return collect($this->storeRules($resource))->map(function (array|string $rule) {
            $items = is_array($rule) ? $rule : explode('|', $rule);
            $items = array_filter($items, fn ($item) => $item !== 'required' && ! (is_string($item) && str_starts_with($item, 'unique:')));

            return array_values(array_merge(['sometimes'], $items));
        })->all();
    }

    public function prepareData(string $resource, array $data, User $user, bool $creating = true): array
    {
        if ($creating && in_array('tenant_id', $this->config($resource)['tenant_fields'] ?? [], true)) {
            $data['tenant_id'] = $user->tenant_id;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    public function assertTenantIntegrity(array $data, User $user): void
    {
        $this->tenantIntegrity->assert($data, $user);
    }

    private function applyIncludes(Builder $query, string $resource, ?string $include): void
    {
        $includes = array_values(array_intersect(
            array_filter(explode(',', (string) $include)),
            $this->config($resource)['includes'] ?? []
        ));

        if ($includes !== []) {
            $query->with($includes);
        }
    }

    private function applySearch(Builder $query, string $resource, ?string $search): void
    {
        $search = trim((string) $search);

        if ($search === '' || empty($this->config($resource)['search'])) {
            return;
        }

        $query->where(function (Builder $q) use ($resource, $search) {
            foreach ($this->config($resource)['search'] as $field) {
                $q->orWhere($field, 'like', '%'.$search.'%');
            }
        });
    }
}
