<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\ParentNotification;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_absent_attendance_record_creates_parent_notification(): void
    {
        $this->seed();

        $student = $this->studentWithParentPhone();
        $group = $this->groupFor($student->branch);
        $session = AttendanceSession::create([
            'group_id' => $group->id,
            'session_date' => now()->toDateString(),
        ]);

        $token = $this->login();

        $this->withToken($token)
            ->postJson('/api/attendance-records', [
                'session_id' => $session->id,
                'student_id' => $student->id,
                'status' => 'absent',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('parent_notifications', [
            'student_id' => $student->id,
            'type' => 'attendance_absence',
            'status' => 'sent',
        ]);
    }

    public function test_overdue_invoice_reminder_creates_parent_notification(): void
    {
        $this->seed();

        $student = $this->studentWithParentPhone();
        $group = $this->groupFor($student->branch);
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_date' => now()->subMonth()->toDateString(),
            'registration_fee' => 0,
            'discount' => 0,
            'status' => 'active',
        ]);

        Invoice::withoutEvents(fn () => Invoice::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'total' => 2500,
            'discount' => 0,
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'unpaid',
        ]));

        $token = $this->login();

        $this->withToken($token)
            ->postJson('/api/parent-notifications/payment-reminders')
            ->assertOk()
            ->assertJsonPath('created.invoice_overdue', 1);

        $this->assertSame(1, ParentNotification::where('type', 'invoice_overdue')->count());
    }

    private function login(): string
    {
        return $this->postJson('/api/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '12345678',
        ])->assertOk()->json('token');
    }

    private function studentWithParentPhone(): Student
    {
        $branch = Branch::firstOrFail();

        return Student::create([
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'first_name' => 'Sara',
            'last_name' => 'Mansouri',
            'parent_name' => 'Mr Mansouri',
            'parent_phone' => '+213555111222',
        ]);
    }

    private function groupFor(Branch $branch): Group
    {
        $category = CourseCategory::firstOrFail();
        $course = Course::firstOrCreate(
            ['tenant_id' => $branch->tenant_id, 'name' => 'Mathematics'],
            ['category_id' => $category->id, 'type' => 'support', 'duration_hours' => 40]
        );
        $teacher = Teacher::create([
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'full_name' => 'Teacher One',
            'salary_type' => 'fixed',
            'salary' => 1000,
        ]);

        return Group::create([
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'academic_year_id' => AcademicYear::firstOrFail()->id,
            'name' => 'Group '.uniqid(),
            'max_students' => 20,
            'status' => 'active',
        ]);
    }
}
