<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
            $table->index(['tenant_id', 'is_current']);
        });

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('parent_name')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['last_name', 'first_name']);
        });

        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('specialization')->nullable();
            $table->string('salary_type')->default('fixed');
            $table->decimal('salary', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['tenant_id', 'branch_id']);
        });

        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('course_categories')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('support');
            $table->unsignedInteger('duration_hours')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('capacity')->default(0);
            $table->timestamps();
            $table->unique(['branch_id', 'name']);
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('max_students')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->unique(['group_id', 'day_of_week', 'start_time']);
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->date('enrollment_date');
            $table->decimal('registration_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['student_id', 'group_id']);
            $table->index(['group_id', 'status']);
        });

        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->timestamps();
            $table->unique(['group_id', 'session_date']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('present');
            $table->timestamps();
            $table->unique(['session_id', 'student_id']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->decimal('total', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid');
            $table->timestamps();
            $table->index(['student_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash');
            $table->dateTime('paid_at');
            $table->string('reference')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('exam_date');
            $table->decimal('total_mark', 8, 2);
            $table->timestamps();
        });

        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('mark', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['branch_id', 'expense_date']);
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['teacher_id', 'month', 'year']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('classrooms');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('course_categories');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('students');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('academic_years');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });
        Schema::dropIfExists('branches');
    }
};
