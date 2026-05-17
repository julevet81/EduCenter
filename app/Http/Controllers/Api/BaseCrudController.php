<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiModelResource;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExpenseCategory;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\ApiResourceRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

abstract class BaseCrudController extends Controller
{
    private const MAX_PER_PAGE = 100;

    protected string $resource;

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizeAbility($request, 'view');

        $query = $this->scopedQuery($request->user());
        $this->applyIncludes($query, $request);
        $this->applySearch($query, $request);

        $perPage = min(max((int) $request->integer('per_page', 15), 1), self::MAX_PER_PAGE);

        return ApiModelResource::collection($query->latest('id')->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAbility($request, 'create');

        $data = $this->prepareData($request->validate($this->config()['rules']), $request->user());
        $this->assertTenantIntegrity($data, $request->user());
        $model = $this->config()['model']::create($data);

        return (new ApiModelResource($model->fresh()))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $id): ApiModelResource
    {
        $this->authorizeAbility($request, 'view');

        $query = $this->scopedQuery($request->user());
        $this->applyIncludes($query, $request);

        return new ApiModelResource($query->findOrFail($id));
    }

    public function update(Request $request, int $id): ApiModelResource
    {
        $this->authorizeAbility($request, 'update');

        $model = $this->scopedQuery($request->user())->findOrFail($id);
        $data = $this->prepareData($request->validate($this->updateRules($this->config()['rules'])), $request->user(), false);
        $this->assertTenantIntegrity(array_merge($model->getAttributes(), $data), $request->user());

        $model->update($data);

        return new ApiModelResource($model->fresh());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorizeAbility($request, 'delete');

        $model = $this->scopedQuery($request->user())->findOrFail($id);
        $model->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    protected function config(): array
    {
        return ApiResourceRegistry::get($this->resource);
    }

    protected function authorizeAbility(Request $request, string $ability): void
    {
        abort_unless($request->user()->can($this->resource.'.'.$ability), 403);
    }

    protected function scopedQuery(User $user): Builder
    {
        $model = $this->config()['model'];
        $query = $model::query();

        match ($this->resource) {
            'classrooms', 'expenses' => $query->whereHas('branch', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'student-documents' => $query->whereHas('student', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'schedules', 'attendance-sessions', 'exams' => $query->whereHas('group', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
            'enrollments' => $query->whereHas('group', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id)),
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

    protected function applyIncludes(Builder $query, Request $request): void
    {
        $includes = array_values(array_intersect(
            array_filter(explode(',', (string) $request->query('include'))),
            $this->config()['includes'] ?? []
        ));

        if ($includes !== []) {
            $query->with($includes);
        }
    }

    protected function applySearch(Builder $query, Request $request): void
    {
        $search = trim((string) $request->query('search', ''));

        if ($search === '' || empty($this->config()['search'])) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            foreach ($this->config()['search'] as $field) {
                $q->orWhere($field, 'like', '%'.$search.'%');
            }
        });
    }

    protected function prepareData(array $data, User $user, bool $creating = true): array
    {
        if ($creating && in_array('tenant_id', $this->config()['tenant_fields'] ?? [], true)) {
            $data['tenant_id'] = $user->tenant_id;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function assertTenantIntegrity(array $data, User $user): void
    {
        foreach (['branch_id' => Branch::class, 'student_id' => Student::class, 'group_id' => Group::class, 'teacher_id' => Teacher::class] as $field => $model) {
            if (isset($data[$field])) {
                abort_unless($model::whereKey($data[$field])->where('tenant_id', $user->tenant_id)->exists(), 422, "{$field} does not belong to the current tenant.");
            }
        }

        foreach (['course_id' => Course::class, 'academic_year_id' => AcademicYear::class, 'level_id' => Level::class, 'section_id' => Section::class] as $field => $model) {
            if (isset($data[$field])) {
                abort_unless($model::whereKey($data[$field])->where('tenant_id', $user->tenant_id)->exists(), 422, "{$field} does not belong to the current tenant.");
            }
        }

        if (isset($data['category_id'])) {
            $valid = CourseCategory::whereKey($data['category_id'])->where('tenant_id', $user->tenant_id)->exists()
                || ExpenseCategory::whereKey($data['category_id'])->where('tenant_id', $user->tenant_id)->exists();
            abort_unless($valid, 422, 'category_id does not belong to the current tenant.');
        }

        if (isset($data['classroom_id']) && $data['classroom_id'] !== null) {
            abort_unless(Classroom::whereKey($data['classroom_id'])->whereHas('branch', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id))->exists(), 422);
        }

        $this->assertNestedOwner($data, $user);
    }

    protected function assertNestedOwner(array $data, User $user): void
    {
        $checks = [
            'session_id' => [AttendanceSession::class, 'group'],
            'enrollment_id' => [Enrollment::class, 'group'],
            'invoice_id' => [Invoice::class, 'student'],
            'exam_id' => [Exam::class, 'group'],
            'user_id' => [User::class, null],
            'manager_id' => [User::class, null],
        ];

        foreach ($checks as $field => [$model, $relation]) {
            if (! isset($data[$field])) {
                continue;
            }

            $query = $model::whereKey($data[$field]);
            $relation
                ? $query->whereHas($relation, fn (Builder $q) => $q->where('tenant_id', $user->tenant_id))
                : $query->where('tenant_id', $user->tenant_id);

            abort_unless($query->exists(), 422, "{$field} does not belong to the current tenant.");
        }
    }

    protected function updateRules(array $rules): array
    {
        return collect($rules)->map(function (array|string $rule) {
            $items = is_array($rule) ? $rule : explode('|', $rule);
            $items = array_filter($items, fn ($item) => $item !== 'required' && ! (is_string($item) && str_starts_with($item, 'unique:')));

            return array_values(array_merge(['sometimes'], $items));
        })->all();
    }
}
