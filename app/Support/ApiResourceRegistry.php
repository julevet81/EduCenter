<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\Teacher;
use App\Models\User;

class ApiResourceRegistry
{
    public static function get(string $resource): array
    {
        $configs = self::all();
        abort_unless(isset($configs[$resource]), 404);

        return $configs[$resource];
    }

    public static function names(): array
    {
        return array_keys(self::all());
    }

    public static function all(): array
    {
        $tenant = ['tenant_fields' => ['tenant_id']];

        return [
            'users' => $tenant + ['model' => User::class, 'search' => ['full_name', 'email', 'phone'], 'includes' => ['branch', 'roles'], 'rules' => ['branch_id' => ['nullable', 'integer', 'exists:branches,id'], 'full_name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'phone' => ['nullable', 'string', 'max:30'], 'password' => ['required', 'string', 'min:8'], 'is_active' => ['boolean'], 'roles' => ['array'], 'roles.*' => ['string', 'exists:roles,name']]],
            'branches' => $tenant + ['model' => Branch::class, 'search' => ['name', 'phone'], 'includes' => ['manager'], 'rules' => ['name' => ['required', 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:30'], 'address' => ['nullable', 'string'], 'manager_id' => ['nullable', 'integer', 'exists:users,id']]],
            'academic-years' => $tenant + ['model' => AcademicYear::class, 'search' => ['name'], 'rules' => ['name' => ['required', 'string', 'max:255'], 'start_date' => ['required', 'date'], 'end_date' => ['required', 'date', 'after_or_equal:start_date'], 'is_current' => ['boolean']]],
            'levels' => $tenant + ['model' => Level::class, 'search' => ['name'], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'sections' => $tenant + ['model' => Section::class, 'search' => ['name'], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'students' => $tenant + ['model' => Student::class, 'search' => ['first_name', 'last_name', 'phone', 'parent_phone'], 'includes' => ['branch'], 'rules' => ['branch_id' => ['required', 'integer', 'exists:branches,id'], 'first_name' => ['required', 'string', 'max:255'], 'last_name' => ['required', 'string', 'max:255'], 'gender' => ['nullable', 'string', 'max:30'], 'birth_date' => ['nullable', 'date'], 'phone' => ['nullable', 'string', 'max:30'], 'parent_phone' => ['nullable', 'string', 'max:30'], 'parent_name' => ['nullable', 'string', 'max:255'], 'address' => ['nullable', 'string']]],
            'student-documents' => ['model' => StudentDocument::class, 'includes' => ['student'], 'rules' => ['student_id' => ['required', 'integer', 'exists:students,id'], 'title' => ['required', 'string', 'max:255'], 'file_path' => ['required', 'string', 'max:255']]],
            'teachers' => $tenant + ['model' => Teacher::class, 'search' => ['full_name', 'phone', 'email', 'specialization'], 'includes' => ['branch', 'user'], 'rules' => ['branch_id' => ['required', 'integer', 'exists:branches,id'], 'user_id' => ['nullable', 'integer', 'exists:users,id'], 'full_name' => ['required', 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:30'], 'email' => ['nullable', 'email', 'max:255'], 'specialization' => ['nullable', 'string', 'max:255'], 'salary_type' => ['required', 'string', 'max:50'], 'salary' => ['required', 'numeric', 'min:0']]],
            'course-categories' => $tenant + ['model' => CourseCategory::class, 'search' => ['name'], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'courses' => $tenant + ['model' => Course::class, 'search' => ['name', 'type'], 'includes' => ['category'], 'rules' => ['category_id' => ['required', 'integer', 'exists:course_categories,id'], 'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'type' => ['required', 'string', 'max:50'], 'duration_hours' => ['required', 'integer', 'min:0']]],
            'classrooms' => ['model' => Classroom::class, 'search' => ['name'], 'includes' => ['branch'], 'rules' => ['branch_id' => ['required', 'integer', 'exists:branches,id'], 'name' => ['required', 'string', 'max:255'], 'capacity' => ['required', 'integer', 'min:0']]],
            'groups' => $tenant + ['model' => Group::class, 'search' => ['name', 'status'], 'includes' => ['branch', 'course', 'teacher', 'academicYear'], 'rules' => ['branch_id' => ['required', 'integer', 'exists:branches,id'], 'course_id' => ['required', 'integer', 'exists:courses,id'], 'teacher_id' => ['required', 'integer', 'exists:teachers,id'], 'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'], 'level_id' => ['nullable', 'integer', 'exists:levels,id'], 'section_id' => ['nullable', 'integer', 'exists:sections,id'], 'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'], 'name' => ['required', 'string', 'max:255'], 'start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'max_students' => ['required', 'integer', 'min:0'], 'status' => ['required', 'string', 'max:50']]],
            'schedules' => ['model' => Schedule::class, 'includes' => ['group'], 'rules' => ['group_id' => ['required', 'integer', 'exists:groups,id'], 'day_of_week' => ['required', 'integer', 'between:1,7'], 'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i', 'after:start_time']]],
            'enrollments' => ['model' => Enrollment::class, 'includes' => ['student', 'group'], 'rules' => ['student_id' => ['required', 'integer', 'exists:students,id'], 'group_id' => ['required', 'integer', 'exists:groups,id'], 'enrollment_date' => ['required', 'date'], 'registration_fee' => ['numeric', 'min:0'], 'discount' => ['numeric', 'min:0'], 'status' => ['required', 'string', 'max:50']]],
            'attendance-sessions' => ['model' => AttendanceSession::class, 'includes' => ['group', 'records'], 'rules' => ['group_id' => ['required', 'integer', 'exists:groups,id'], 'session_date' => ['required', 'date']]],
            'attendance-records' => ['model' => AttendanceRecord::class, 'includes' => ['session', 'student'], 'rules' => ['session_id' => ['required', 'integer', 'exists:attendance_sessions,id'], 'student_id' => ['required', 'integer', 'exists:students,id'], 'status' => ['required', 'string', 'max:50']]],
            'invoices' => ['model' => Invoice::class, 'includes' => ['student', 'enrollment', 'payments'], 'rules' => ['student_id' => ['required', 'integer', 'exists:students,id'], 'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'], 'total' => ['required', 'numeric', 'min:0'], 'discount' => ['numeric', 'min:0'], 'due_date' => ['nullable', 'date'], 'status' => ['required', 'string', 'max:50']]],
            'payments' => ['model' => Payment::class, 'includes' => ['invoice'], 'rules' => ['invoice_id' => ['required', 'integer', 'exists:invoices,id'], 'amount' => ['required', 'numeric', 'min:0'], 'payment_method' => ['required', 'string', 'max:50'], 'paid_at' => ['required', 'date'], 'reference' => ['nullable', 'string', 'max:255']]],
            'exams' => ['model' => Exam::class, 'search' => ['title'], 'includes' => ['group', 'results'], 'rules' => ['group_id' => ['required', 'integer', 'exists:groups,id'], 'title' => ['required', 'string', 'max:255'], 'exam_date' => ['required', 'date'], 'total_mark' => ['required', 'numeric', 'min:0']]],
            'exam-results' => ['model' => ExamResult::class, 'includes' => ['exam', 'student'], 'rules' => ['exam_id' => ['required', 'integer', 'exists:exams,id'], 'student_id' => ['required', 'integer', 'exists:students,id'], 'mark' => ['required', 'numeric', 'min:0'], 'notes' => ['nullable', 'string']]],
            'expense-categories' => $tenant + ['model' => ExpenseCategory::class, 'search' => ['name'], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'expenses' => ['model' => Expense::class, 'includes' => ['branch', 'category'], 'rules' => ['branch_id' => ['required', 'integer', 'exists:branches,id'], 'category_id' => ['required', 'integer', 'exists:expense_categories,id'], 'amount' => ['required', 'numeric', 'min:0'], 'expense_date' => ['required', 'date'], 'description' => ['nullable', 'string']]],
            'payrolls' => ['model' => Payroll::class, 'includes' => ['teacher'], 'rules' => ['teacher_id' => ['required', 'integer', 'exists:teachers,id'], 'month' => ['required', 'integer', 'between:1,12'], 'year' => ['required', 'integer', 'between:2000,2100'], 'amount' => ['required', 'numeric', 'min:0'], 'status' => ['required', 'string', 'max:50']]],
            'notifications' => ['model' => Notification::class, 'includes' => ['user'], 'rules' => ['user_id' => ['required', 'integer', 'exists:users,id'], 'title' => ['required', 'string', 'max:255'], 'body' => ['required', 'string'], 'is_read' => ['boolean']]],
        ];
    }
}
