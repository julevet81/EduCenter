<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\ExpenseCategory;
use App\Models\Level;
use App\Models\Section;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $resources = [
            'users', 'branches', 'academic-years', 'levels', 'sections', 'students',
            'student-documents', 'teachers', 'course-categories', 'courses', 'classrooms',
            'groups', 'schedules', 'enrollments', 'attendance-sessions', 'attendance-records',
            'invoices', 'payments', 'exams', 'exam-results', 'expense-categories', 'expenses',
            'payrolls', 'notifications',
            'parent-notifications',
            'roles', 'permissions',
        ];

        $permissions = collect($resources)
            ->flatMap(fn (string $resource) => collect(['view', 'create', 'update', 'delete'])->map(fn (string $ability) => "{$resource}.{$ability}"));

        $permissions->push('dashboard.view');
        $permissions->each(fn (string $name) => Permission::findOrCreate($name, 'web'));

        $adminRole = Role::findOrCreate('super-admin', 'web');
        $adminRole->syncPermissions(Permission::all());

        $tenant = Tenant::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'EduCenter Demo', 'phone' => '+213000000000', 'email' => 'admin@gmail.com', 'address' => 'Main branch']
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'tenant_id' => $tenant->id,
                'full_name' => 'System Administrator',
                'phone' => '+213000000000',
                'password' => Hash::make('12345678'),
                'is_active' => true,
            ]
        );

        $branch = Branch::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Main Branch'],
            ['phone' => '+213000000000', 'address' => 'Main branch', 'manager_id' => $admin->id]
        );

        $admin->forceFill(['branch_id' => $branch->id])->save();
        $admin->assignRole($adminRole);

        AcademicYear::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '2026/2027'],
            ['start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'is_current' => true]
        );

        foreach (['Primary', 'Middle', 'Secondary'] as $name) {
            Level::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name]);
        }

        foreach (['A', 'B', 'C'] as $name) {
            Section::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name]);
        }

        CourseCategory::firstOrCreate(['tenant_id' => $tenant->id, 'name' => 'School Support']);
        ExpenseCategory::firstOrCreate(['tenant_id' => $tenant->id, 'name' => 'General']);
    }
}
