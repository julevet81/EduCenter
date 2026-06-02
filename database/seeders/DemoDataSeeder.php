<?php

namespace Database\Seeders;

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
use App\Models\Teacher;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'EduCenter Demo',
                'phone' => '+213000000000',
                'email' => 'admin@gmail.com',
                'address' => 'Main branch',
            ]
        );

        $this->ensurePermissions();

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

        $adminRole = Role::findOrCreate('super-admin', 'web');
        $adminRole->syncPermissions(Permission::all());
        $admin->assignRole($adminRole);

        $branches = $this->seedBranches($tenant, $admin);
        $admin->forceFill(['tenant_id' => $tenant->id, 'branch_id' => $branches->first()->id, 'is_active' => true])->save();

        $academicYear = AcademicYear::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '2026/2027'],
            ['start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'is_current' => true]
        );

        $levels = collect(['Primary', 'Middle', 'Secondary', 'Languages', 'Professional'])->map(
            fn (string $name) => Level::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name])
        );

        $sections = collect(['A', 'B', 'C', 'D'])->map(
            fn (string $name) => Section::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name])
        );

        $categories = collect(['School Support', 'Languages', 'Professional Training', 'Exam Preparation'])->map(
            fn (string $name) => CourseCategory::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name])
        );

        $expenseCategories = collect(['Rent and Utilities', 'Payroll', 'Marketing', 'Supplies', 'Maintenance'])->map(
            fn (string $name) => ExpenseCategory::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name])
        );

        $classrooms = $this->seedClassrooms($branches);
        $courses = $this->seedCourses($tenant, $categories);
        $teachers = $this->seedTeachers($tenant, $branches);
        $students = $this->seedStudents($tenant, $branches);
        $groups = $this->seedGroups($tenant, $branches, $courses, $teachers, $academicYear, $levels, $sections, $classrooms);

        $this->seedSchedules($groups);
        $enrollments = $this->seedEnrollments($students, $groups);
        $invoices = $this->seedInvoicesAndPayments($enrollments);
        $this->seedAttendance($groups, $enrollments);
        $this->seedExams($groups, $enrollments);
        $this->seedExpenses($branches, $expenseCategories);
        $this->seedPayrolls($teachers);
        $this->seedNotifications($admin, $invoices);
    }

    private function seedBranches(Tenant $tenant, User $admin)
    {
        return collect([
            ['name' => 'Main Center', 'phone' => '0550001001', 'address' => 'City center, second floor'],
            ['name' => 'Languages Branch', 'phone' => '0550001002', 'address' => 'University district'],
            ['name' => 'School Support Branch', 'phone' => '0550001003', 'address' => 'Independence avenue'],
            ['name' => 'West Training Branch', 'phone' => '0550001004', 'address' => 'West district'],
            ['name' => 'Evening Classes Branch', 'phone' => '0550001005', 'address' => 'New town'],
        ])->map(fn (array $data) => Branch::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $data['name']],
            $data + ['tenant_id' => $tenant->id, 'manager_id' => $admin->id]
        ));
    }

    private function seedClassrooms($branches)
    {
        $classrooms = collect();

        foreach ($branches as $branchIndex => $branch) {
            foreach (range(1, 2) as $room) {
                $classrooms->push(Classroom::updateOrCreate(
                    ['branch_id' => $branch->id, 'name' => 'Room '.($branchIndex + 1).'-'.$room],
                    ['capacity' => 16 + ($room * 4) + $branchIndex]
                ));
            }
        }

        return $classrooms;
    }

    private function seedCourses(Tenant $tenant, $categories)
    {
        $items = [
            [0, 'Mathematics Middle 4', 'school_support', 48],
            [0, 'Physics Secondary 3', 'school_support', 56],
            [0, 'Arabic Exam Support', 'school_support', 36],
            [0, 'Science Middle 3', 'school_support', 42],
            [1, 'English A1', 'language', 40],
            [1, 'English B1 Conversation', 'language', 44],
            [1, 'French A2', 'language', 42],
            [1, 'French B1 Writing', 'language', 44],
            [2, 'Computer Basics', 'training', 36],
            [2, 'Office Tools', 'training', 30],
            [2, 'Graphic Design Intro', 'training', 34],
            [3, 'Baccalaureate Intensive Pack', 'exam_preparation', 64],
        ];

        return collect($items)->map(fn (array $item) => Course::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $item[1]],
            [
                'tenant_id' => $tenant->id,
                'category_id' => $categories[$item[0]]->id,
                'type' => $item[2],
                'duration_hours' => $item[3],
                'description' => 'Demo course for center management workflows.',
            ]
        ));
    }

    private function seedTeachers(Tenant $tenant, $branches)
    {
        $items = [
            [0, 'Nawal Mourad', 'Mathematics', 'hourly', 1800],
            [2, 'Salim Ben Aissa', 'Physics', 'hourly', 2200],
            [1, 'Amina Hadji', 'English', 'fixed', 68000],
            [1, 'Karim Mansouri', 'French', 'fixed', 64000],
            [0, 'Leila Derradji', 'Computer Science', 'percentage', 35],
            [3, 'Rachid Belkacem', 'Arabic', 'fixed', 59000],
            [4, 'Samira Khaled', 'Science', 'hourly', 1700],
            [2, 'Yacine Amari', 'Mathematics', 'fixed', 62000],
            [3, 'Meriem Saidi', 'Graphic Design', 'percentage', 40],
            [4, 'Ilyes Cherif', 'Office Tools', 'hourly', 1600],
            [0, 'Nadia Gherbi', 'Exam Coaching', 'fixed', 72000],
            [1, 'Sofiane Kaci', 'Spanish', 'hourly', 1900],
        ];

        return collect($items)->map(function (array $item, int $index) use ($tenant, $branches) {
            $email = 'teacher'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'@example.com';

            return Teacher::updateOrCreate(
                ['tenant_id' => $tenant->id, 'email' => $email],
                [
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branches[$item[0]]->id,
                    'full_name' => $item[1],
                    'phone' => '055111'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'specialization' => $item[2],
                    'salary_type' => $item[3],
                    'salary' => $item[4],
                ]
            );
        });
    }

    private function seedStudents(Tenant $tenant, $branches)
    {
        $firstNames = ['Amine', 'Meriem', 'Yacine', 'Sara', 'Rayan', 'Lina', 'Nour', 'Ilyes', 'Aya', 'Adam', 'Malak', 'Anis', 'Rima', 'Walid', 'Hiba', 'Sami', 'Dina', 'Omar', 'Lamis', 'Nassim'];
        $lastNames = ['Benyoucef', 'Khaled', 'Amari', 'Belkacem', 'Maghrabi', 'Kacemi', 'Cherif', 'Boukermia', 'Saidi', 'Mansouri', 'Haddad', 'Brahimi', 'Kaci', 'Meziane', 'Dahmani', 'Touati', 'Ferradj', 'Rahmani', 'Ziani', 'Bensalem'];
        $students = collect();

        foreach (range(1, 40) as $index) {
            $branch = $branches[($index - 1) % $branches->count()];
            $firstName = $firstNames[($index - 1) % count($firstNames)];
            $lastName = $lastNames[(int) floor(($index - 1) / 2) % count($lastNames)];
            $phone = '055210'.str_pad((string) $index, 4, '0', STR_PAD_LEFT);

            $students->push(Student::updateOrCreate(
                ['tenant_id' => $tenant->id, 'phone' => $phone],
                [
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $index % 2 === 0 ? 'female' : 'male',
                    'birth_date' => Carbon::parse('2007-01-01')->addMonths($index * 3)->toDateString(),
                    'parent_name' => 'Parent '.$lastName,
                    'parent_phone' => '066210'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                    'address' => 'Demo address '.$index,
                ]
            ));
        }

        return $students;
    }

    private function seedGroups(Tenant $tenant, $branches, $courses, $teachers, AcademicYear $academicYear, $levels, $sections, $classrooms)
    {
        $groups = collect();

        foreach ($courses as $index => $course) {
            $branch = $branches[$index % $branches->count()];
            $teacher = $teachers[$index % $teachers->count()];
            $classroom = $classrooms->where('branch_id', $branch->id)->values()[$index % 2] ?? $classrooms->first();

            $groups->push(Group::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $course->name.' - Group '.chr(65 + ($index % 4))],
                [
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'course_id' => $course->id,
                    'teacher_id' => $teacher->id,
                    'academic_year_id' => $academicYear->id,
                    'level_id' => $levels[$index % $levels->count()]->id,
                    'section_id' => $sections[$index % $sections->count()]->id,
                    'classroom_id' => $classroom->id,
                    'start_date' => Carbon::parse('2026-09-10')->addDays($index)->toDateString(),
                    'end_date' => '2027-06-20',
                    'max_students' => 18 + ($index % 6),
                    'status' => $index % 11 === 0 ? 'pending' : 'active',
                ]
            ));
        }

        return $groups;
    }

    private function seedSchedules($groups): void
    {
        foreach ($groups as $index => $group) {
            $slots = [
                [($index % 6) + 1, '16:00', '18:00'],
                [(($index + 2) % 6) + 1, '09:00', '11:00'],
            ];

            foreach ($slots as [$day, $start, $end]) {
                Schedule::updateOrCreate(
                    ['group_id' => $group->id, 'day_of_week' => $day, 'start_time' => $start],
                    ['end_time' => $end]
                );
            }
        }
    }

    private function seedEnrollments($students, $groups)
    {
        $enrollments = collect();

        foreach ($groups as $groupIndex => $group) {
            $selectedStudents = $students
                ->slice(($groupIndex * 3) % max(1, $students->count()), 8)
                ->when(fn ($slice) => $slice->count() < 8, fn ($slice) => $slice->concat($students->take(8 - $slice->count())))
                ->values();

            foreach ($selectedStudents as $studentIndex => $student) {
                $enrollments->push(Enrollment::updateOrCreate(
                    ['student_id' => $student->id, 'group_id' => $group->id],
                    [
                        'enrollment_date' => Carbon::parse('2026-09-01')->addDays($groupIndex + $studentIndex),
                        'registration_fee' => 2500,
                        'discount' => $studentIndex % 5 === 0 ? 1000 : 0,
                        'status' => $studentIndex % 13 === 0 ? 'paused' : 'active',
                    ]
                ));
            }
        }

        return $enrollments;
    }

    private function seedInvoicesAndPayments($enrollments)
    {
        return $enrollments->values()->map(function (Enrollment $enrollment, int $index) {
            $status = match ($index % 5) {
                0 => 'paid',
                1 => 'unpaid',
                2 => 'partial',
                3 => 'pending',
                default => 'overdue',
            };

            $total = [12000, 15000, 18000, 22000, 26000][$index % 5];
            $invoice = Invoice::updateOrCreate(
                ['student_id' => $enrollment->student_id, 'enrollment_id' => $enrollment->id],
                [
                    'total' => $total,
                    'discount' => $enrollment->discount,
                    'due_date' => Carbon::today()->subDays(14 - ($index % 20)),
                    'status' => $status,
                ]
            );

            if (in_array($status, ['paid', 'partial'], true)) {
                Payment::updateOrCreate(
                    ['reference' => 'DEMO-PAY-'.$invoice->id],
                    [
                        'invoice_id' => $invoice->id,
                        'amount' => $status === 'paid' ? $total - $enrollment->discount : (int) (($total - $enrollment->discount) / 2),
                        'payment_method' => ['cash', 'bank', 'card', 'transfer'][$index % 4],
                        'paid_at' => Carbon::now()->subDays($index % 30),
                    ]
                );
            }

            return $invoice;
        });
    }

    private function seedAttendance($groups, $enrollments): void
    {
        foreach ($groups as $groupIndex => $group) {
            foreach (range(1, 3) as $sessionIndex) {
                $session = AttendanceSession::updateOrCreate(
                    ['group_id' => $group->id, 'session_date' => Carbon::today()->subDays(($groupIndex * 3) + $sessionIndex)],
                    []
                );

                foreach ($enrollments->where('group_id', $group->id)->values() as $index => $enrollment) {
                    AttendanceRecord::updateOrCreate(
                        ['session_id' => $session->id, 'student_id' => $enrollment->student_id],
                        ['status' => ['present', 'present', 'late', 'absent'][($index + $sessionIndex) % 4]]
                    );
                }
            }
        }
    }

    private function seedExams($groups, $enrollments): void
    {
        foreach ($groups as $groupIndex => $group) {
            foreach (['Diagnostic Test', 'Monthly Assessment'] as $examIndex => $title) {
                $exam = Exam::firstOrCreate(
                    ['group_id' => $group->id, 'title' => $title],
                    ['exam_date' => Carbon::today()->addDays(10 + $groupIndex + $examIndex), 'total_mark' => 20]
                );

                foreach ($enrollments->where('group_id', $group->id)->values() as $index => $enrollment) {
                    ExamResult::updateOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $enrollment->student_id],
                        ['mark' => min(20, 9 + (($index + $groupIndex + $examIndex) % 11)), 'notes' => $index % 4 === 0 ? 'Needs follow-up' : null]
                    );
                }
            }
        }
    }

    private function seedExpenses($branches, $expenseCategories): void
    {
        foreach ($branches as $branchIndex => $branch) {
            foreach ($expenseCategories as $categoryIndex => $category) {
                foreach (range(0, 2) as $monthOffset) {
                    Expense::updateOrCreate(
                        [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'expense_date' => Carbon::today()->subMonths($monthOffset)->subDays($branchIndex + $categoryIndex + 3),
                        ],
                        [
                            'amount' => [18000, 42000, 12000, 8000, 15000][$categoryIndex] + ($branchIndex * 1000),
                            'description' => 'Demo operating expense for reports and dashboards.',
                        ]
                    );
                }
            }
        }
    }

    private function seedPayrolls($teachers): void
    {
        foreach ($teachers as $teacher) {
            foreach ([4, 5, 6] as $month) {
                Payroll::updateOrCreate(
                    ['teacher_id' => $teacher->id, 'month' => $month, 'year' => 2026],
                    ['amount' => $teacher->salary_type === 'fixed' ? $teacher->salary : 52000 + ($month * 500), 'status' => $month === 6 ? 'pending' : 'paid']
                );
            }
        }
    }

    private function seedNotifications(User $admin, $invoices): void
    {
        Notification::firstOrCreate(
            ['user_id' => $admin->id, 'title' => 'Welcome to EduCenter'],
            ['body' => 'Demo data has been expanded for a richer management dashboard.', 'is_read' => false]
        );

        Notification::updateOrCreate(
            ['user_id' => $admin->id, 'title' => 'Invoices need follow-up'],
            ['body' => 'There are '.$invoices->where('status', '!=', 'paid')->count().' invoices not fully paid.', 'is_read' => false]
        );
    }

    private function ensurePermissions(): void
    {
        $resources = [
            'users', 'branches', 'academic-years', 'levels', 'sections', 'students',
            'student-documents', 'teachers', 'course-categories', 'courses', 'classrooms',
            'groups', 'schedules', 'enrollments', 'attendance-sessions', 'attendance-records',
            'invoices', 'payments', 'exams', 'exam-results', 'expense-categories', 'expenses',
            'payrolls', 'notifications', 'parent-notifications', 'roles', 'permissions',
        ];

        collect($resources)
            ->flatMap(fn (string $resource) => collect(['view', 'create', 'update', 'delete'])->map(fn (string $ability) => "{$resource}.{$ability}"))
            ->push('dashboard.view')
            ->each(fn (string $name) => Permission::findOrCreate($name, 'web'));
    }
}
