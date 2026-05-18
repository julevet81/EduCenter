<?php

namespace App\Services\Api;

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
use Illuminate\Database\Eloquent\Builder;

class TenantIntegrityService
{
    public function assert(array $data, User $user): void
    {
        foreach ($this->directTenantChecks() as $field => $model) {
            if (isset($data[$field])) {
                abort_unless(
                    $model::whereKey($data[$field])->where('tenant_id', $user->tenant_id)->exists(),
                    422,
                    "{$field} does not belong to the current tenant."
                );
            }
        }

        if (isset($data['category_id'])) {
            $valid = CourseCategory::whereKey($data['category_id'])->where('tenant_id', $user->tenant_id)->exists()
                || ExpenseCategory::whereKey($data['category_id'])->where('tenant_id', $user->tenant_id)->exists();

            abort_unless($valid, 422, 'category_id does not belong to the current tenant.');
        }

        if (isset($data['classroom_id']) && $data['classroom_id'] !== null) {
            abort_unless(
                Classroom::whereKey($data['classroom_id'])
                    ->whereHas('branch', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id))
                    ->exists(),
                422,
                'classroom_id does not belong to the current tenant.'
            );
        }

        $this->assertNestedOwner($data, $user);
    }

    private function directTenantChecks(): array
    {
        return [
            'branch_id' => Branch::class,
            'student_id' => Student::class,
            'group_id' => Group::class,
            'teacher_id' => Teacher::class,
            'course_id' => Course::class,
            'academic_year_id' => AcademicYear::class,
            'level_id' => Level::class,
            'section_id' => Section::class,
        ];
    }

    private function assertNestedOwner(array $data, User $user): void
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
}
