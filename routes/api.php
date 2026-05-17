<?php

use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\AttendanceRecordController;
use App\Http\Controllers\Api\AttendanceSessionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\CourseCategoryController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ExamResultController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentDocumentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);
    Route::get('dashboard/summary', [DashboardController::class, 'summary']);

    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('academic-years', AcademicYearController::class);
    Route::apiResource('levels', LevelController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('student-documents', StudentDocumentController::class);
    Route::apiResource('teachers', TeacherController::class);
    Route::apiResource('course-categories', CourseCategoryController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('classrooms', ClassroomController::class);
    Route::apiResource('groups', GroupController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('enrollments', EnrollmentController::class);
    Route::apiResource('attendance-sessions', AttendanceSessionController::class);
    Route::apiResource('attendance-records', AttendanceRecordController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('exams', ExamController::class);
    Route::apiResource('exam-results', ExamResultController::class);
    Route::apiResource('expense-categories', ExpenseCategoryController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('payrolls', PayrollController::class);
    Route::apiResource('notifications', NotificationController::class);
});
