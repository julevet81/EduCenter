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

        $branches = collect([
            ['name' => 'المركز الرئيسي', 'phone' => '0550001001', 'address' => 'وسط المدينة، الطابق الثاني'],
            ['name' => 'فرع اللغات', 'phone' => '0550001002', 'address' => 'حي الجامعة، قرب المكتبة'],
            ['name' => 'فرع الدعم المدرسي', 'phone' => '0550001003', 'address' => 'شارع الاستقلال، مقابل المتوسطة'],
        ])->map(fn (array $data) => Branch::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $data['name']],
            $data + ['tenant_id' => $tenant->id, 'manager_id' => $admin->id]
        ));

        $admin->forceFill(['tenant_id' => $tenant->id, 'branch_id' => $branches->first()->id, 'is_active' => true])->save();

        $academicYear = AcademicYear::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '2026/2027'],
            ['start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'is_current' => true]
        );

        $levels = collect(['ابتدائي', 'متوسط', 'ثانوي', 'لغات'])->map(
            fn (string $name) => Level::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name])
        );

        $sections = collect(['A', 'B', 'C'])->map(
            fn (string $name) => Section::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name])
        );

        $categories = collect([
            'دعم مدرسي',
            'تعليم اللغات',
            'تكوين مهني قصير',
        ])->map(fn (string $name) => CourseCategory::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name]));

        $expenseCategories = collect(['كراء وتجهيزات', 'أجور', 'تسويق', 'مصاريف عامة'])->map(
            fn (string $name) => ExpenseCategory::firstOrCreate(['tenant_id' => $tenant->id, 'name' => $name])
        );

        $classrooms = collect([
            ['branch' => 0, 'name' => 'قاعة 01', 'capacity' => 24],
            ['branch' => 0, 'name' => 'قاعة الإعلام الآلي', 'capacity' => 18],
            ['branch' => 1, 'name' => 'Language Lab', 'capacity' => 16],
            ['branch' => 2, 'name' => 'قاعة الدعم', 'capacity' => 22],
        ])->map(fn (array $data) => Classroom::updateOrCreate(
            ['branch_id' => $branches[$data['branch']]->id, 'name' => $data['name']],
            ['capacity' => $data['capacity']]
        ));

        $courses = collect([
            ['category' => 0, 'name' => 'رياضيات الرابعة متوسط', 'type' => 'school_support', 'duration_hours' => 48, 'description' => 'مراجعة منظمة وتمارين تطبيقية للتحضير للفروض والاختبارات.'],
            ['category' => 0, 'name' => 'فيزياء الثالثة ثانوي', 'type' => 'school_support', 'duration_hours' => 56, 'description' => 'دروس دعم مكثفة مع حل مواضيع البكالوريا.'],
            ['category' => 1, 'name' => 'اللغة الإنجليزية A1', 'type' => 'language', 'duration_hours' => 40, 'description' => 'محادثة وقواعد أساسية للمبتدئين.'],
            ['category' => 1, 'name' => 'اللغة الفرنسية B1', 'type' => 'language', 'duration_hours' => 44, 'description' => 'تطوير التعبير الشفهي والكتابي.'],
            ['category' => 2, 'name' => 'أساسيات الإعلام الآلي', 'type' => 'training', 'duration_hours' => 36, 'description' => 'مهارات مكتبية واستخدام الحاسوب للمبتدئين.'],
        ])->map(fn (array $data) => Course::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $data['name']],
            [
                'tenant_id' => $tenant->id,
                'category_id' => $categories[$data['category']]->id,
                'type' => $data['type'],
                'duration_hours' => $data['duration_hours'],
                'description' => $data['description'],
            ]
        ));

        $teachers = collect([
            ['branch' => 0, 'full_name' => 'نوال مراد', 'phone' => '0551112001', 'email' => 'nawal.teacher@example.com', 'specialization' => 'رياضيات', 'salary_type' => 'hourly', 'salary' => 1800],
            ['branch' => 2, 'full_name' => 'سليم بن عيسى', 'phone' => '0551112002', 'email' => 'salim.teacher@example.com', 'specialization' => 'فيزياء', 'salary_type' => 'hourly', 'salary' => 2200],
            ['branch' => 1, 'full_name' => 'أمينة حاجي', 'phone' => '0551112003', 'email' => 'amina.teacher@example.com', 'specialization' => 'إنجليزية', 'salary_type' => 'fixed', 'salary' => 68000],
            ['branch' => 1, 'full_name' => 'كريم منصوري', 'phone' => '0551112004', 'email' => 'karim.teacher@example.com', 'specialization' => 'فرنسية', 'salary_type' => 'fixed', 'salary' => 64000],
            ['branch' => 0, 'full_name' => 'ليلى دراجي', 'phone' => '0551112005', 'email' => 'leila.teacher@example.com', 'specialization' => 'إعلام آلي', 'salary_type' => 'percentage', 'salary' => 35],
        ])->map(fn (array $data) => Teacher::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => $data['email']],
            [
                'tenant_id' => $tenant->id,
                'branch_id' => $branches[$data['branch']]->id,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'specialization' => $data['specialization'],
                'salary_type' => $data['salary_type'],
                'salary' => $data['salary'],
            ]
        ));

        $students = collect([
            ['branch' => 0, 'first_name' => 'أمين', 'last_name' => 'بن يوسف', 'gender' => 'male', 'birth_date' => '2011-03-12', 'phone' => '0552100001', 'parent_name' => 'محمد بن يوسف', 'parent_phone' => '0662100001', 'address' => 'حي النصر'],
            ['branch' => 0, 'first_name' => 'مريم', 'last_name' => 'خالد', 'gender' => 'female', 'birth_date' => '2010-11-04', 'phone' => '0552100002', 'parent_name' => 'سميرة خالد', 'parent_phone' => '0662100002', 'address' => 'وسط المدينة'],
            ['branch' => 2, 'first_name' => 'ياسين', 'last_name' => 'عماري', 'gender' => 'male', 'birth_date' => '2008-07-20', 'phone' => '0552100003', 'parent_name' => 'فاطمة عماري', 'parent_phone' => '0662100003', 'address' => 'حي السلام'],
            ['branch' => 2, 'first_name' => 'سارة', 'last_name' => 'بلقاسم', 'gender' => 'female', 'birth_date' => '2007-02-16', 'phone' => '0552100004', 'parent_name' => 'علي بلقاسم', 'parent_phone' => '0662100004', 'address' => 'شارع الاستقلال'],
            ['branch' => 1, 'first_name' => 'ريان', 'last_name' => 'مغربي', 'gender' => 'male', 'birth_date' => '2013-09-01', 'phone' => '0552100005', 'parent_name' => 'نادية مغربي', 'parent_phone' => '0662100005', 'address' => 'حي الجامعة'],
            ['branch' => 1, 'first_name' => 'لينا', 'last_name' => 'قاسمي', 'gender' => 'female', 'birth_date' => '2012-12-22', 'phone' => '0552100006', 'parent_name' => 'مراد قاسمي', 'parent_phone' => '0662100006', 'address' => 'حي الزهور'],
            ['branch' => 0, 'first_name' => 'نور', 'last_name' => 'شريف', 'gender' => 'female', 'birth_date' => '2009-05-09', 'phone' => '0552100007', 'parent_name' => 'كمال شريف', 'parent_phone' => '0662100007', 'address' => 'حي البساتين'],
            ['branch' => 1, 'first_name' => 'إلياس', 'last_name' => 'بوكرمة', 'gender' => 'male', 'birth_date' => '2014-01-18', 'phone' => '0552100008', 'parent_name' => 'سعاد بوكرمة', 'parent_phone' => '0662100008', 'address' => 'حي النخيل'],
        ])->map(fn (array $data) => Student::updateOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => $data['phone']],
            [
                'tenant_id' => $tenant->id,
                'branch_id' => $branches[$data['branch']]->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'parent_name' => $data['parent_name'],
                'parent_phone' => $data['parent_phone'],
                'address' => $data['address'],
            ]
        ));

        $groups = collect([
            ['branch' => 0, 'course' => 0, 'teacher' => 0, 'level' => 1, 'section' => 0, 'classroom' => 0, 'name' => 'رياضيات 4 متوسط - فوج A', 'max_students' => 18],
            ['branch' => 2, 'course' => 1, 'teacher' => 1, 'level' => 2, 'section' => 1, 'classroom' => 3, 'name' => 'فيزياء بكالوريا - فوج B', 'max_students' => 16],
            ['branch' => 1, 'course' => 2, 'teacher' => 2, 'level' => 3, 'section' => 0, 'classroom' => 2, 'name' => 'English A1 - Evening', 'max_students' => 14],
            ['branch' => 1, 'course' => 3, 'teacher' => 3, 'level' => 3, 'section' => 1, 'classroom' => 2, 'name' => 'Français B1 - Weekend', 'max_students' => 14],
            ['branch' => 0, 'course' => 4, 'teacher' => 4, 'level' => 3, 'section' => 2, 'classroom' => 1, 'name' => 'إعلام آلي للمبتدئين', 'max_students' => 12],
        ])->map(fn (array $data) => Group::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $data['name']],
            [
                'tenant_id' => $tenant->id,
                'branch_id' => $branches[$data['branch']]->id,
                'course_id' => $courses[$data['course']]->id,
                'teacher_id' => $teachers[$data['teacher']]->id,
                'academic_year_id' => $academicYear->id,
                'level_id' => $levels[$data['level']]->id,
                'section_id' => $sections[$data['section']]->id,
                'classroom_id' => $classrooms[$data['classroom']]->id,
                'start_date' => '2026-09-10',
                'end_date' => '2027-06-20',
                'max_students' => $data['max_students'],
                'status' => 'active',
            ]
        ));

        $this->seedSchedules($groups);
        $enrollments = $this->seedEnrollments($students, $groups);
        $invoices = $this->seedInvoicesAndPayments($enrollments);
        $this->seedAttendance($groups, $enrollments);
        $this->seedExams($groups, $enrollments);
        $this->seedExpenses($branches, $expenseCategories);
        $this->seedPayrolls($teachers);
        $this->seedNotifications($admin, $invoices);
    }

    private function seedSchedules($groups): void
    {
        $plans = [
            0 => [[2, '16:00', '18:00'], [5, '09:00', '11:00']],
            1 => [[1, '17:00', '19:00'], [4, '17:00', '19:00']],
            2 => [[3, '18:00', '19:30'], [6, '10:00', '11:30']],
            3 => [[5, '14:00', '16:00'], [6, '14:00', '16:00']],
            4 => [[2, '09:00', '11:00'], [4, '09:00', '11:00']],
        ];

        foreach ($plans as $groupIndex => $items) {
            foreach ($items as [$day, $start, $end]) {
                Schedule::updateOrCreate(
                    ['group_id' => $groups[$groupIndex]->id, 'day_of_week' => $day, 'start_time' => $start],
                    ['end_time' => $end]
                );
            }
        }
    }

    private function seedEnrollments($students, $groups)
    {
        $map = [
            [0, 0], [1, 0], [6, 0],
            [2, 1], [3, 1],
            [4, 2], [5, 2], [7, 2],
            [5, 3], [7, 3],
            [0, 4], [2, 4],
        ];

        return collect($map)->map(fn (array $item) => Enrollment::updateOrCreate(
            ['student_id' => $students[$item[0]]->id, 'group_id' => $groups[$item[1]]->id],
            [
                'enrollment_date' => Carbon::parse('2026-09-01')->addDays($item[0] + $item[1]),
                'registration_fee' => 2500,
                'discount' => in_array($item[0], [1, 5], true) ? 1000 : 0,
                'status' => 'active',
            ]
        ));
    }

    private function seedInvoicesAndPayments($enrollments)
    {
        return $enrollments->values()->map(function (Enrollment $enrollment, int $index) {
            $status = match ($index % 4) {
                0 => 'paid',
                1 => 'unpaid',
                2 => 'partial',
                default => 'pending',
            };

            $total = [12000, 15000, 18000, 22000][$index % 4];
            $invoice = Invoice::updateOrCreate(
                ['student_id' => $enrollment->student_id, 'enrollment_id' => $enrollment->id],
                [
                    'total' => $total,
                    'discount' => $enrollment->discount,
                    'due_date' => Carbon::today()->subDays(8 - ($index % 6)),
                    'status' => $status,
                ]
            );

            if (in_array($status, ['paid', 'partial'], true)) {
                Payment::updateOrCreate(
                    ['reference' => 'DEMO-PAY-'.$invoice->id],
                    [
                        'invoice_id' => $invoice->id,
                        'amount' => $status === 'paid' ? $total - $enrollment->discount : (int) (($total - $enrollment->discount) / 2),
                        'payment_method' => $index % 2 === 0 ? 'cash' : 'bank',
                        'paid_at' => Carbon::now()->subDays($index + 1),
                    ]
                );
            }

            return $invoice;
        });
    }

    private function seedAttendance($groups, $enrollments): void
    {
        foreach ($groups as $groupIndex => $group) {
            $session = AttendanceSession::updateOrCreate(
                ['group_id' => $group->id, 'session_date' => Carbon::today()->subDays($groupIndex + 1)],
                []
            );

            $groupEnrollments = $enrollments->where('group_id', $group->id)->values();

            foreach ($groupEnrollments as $index => $enrollment) {
                AttendanceRecord::updateOrCreate(
                    ['session_id' => $session->id, 'student_id' => $enrollment->student_id],
                    ['status' => $index === 1 ? 'absent' : ($index === 2 ? 'late' : 'present')]
                );
            }
        }
    }

    private function seedExams($groups, $enrollments): void
    {
        foreach ($groups as $groupIndex => $group) {
            $exam = Exam::firstOrCreate(
                ['group_id' => $group->id, 'title' => 'اختبار تشخيصي'],
                ['exam_date' => Carbon::today()->addDays(10 + $groupIndex), 'total_mark' => 20]
            );

            foreach ($enrollments->where('group_id', $group->id)->values() as $index => $enrollment) {
                ExamResult::updateOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $enrollment->student_id],
                    ['mark' => min(20, 11 + $index + $groupIndex), 'notes' => $index === 0 ? 'مستوى جيد' : null]
                );
            }
        }
    }

    private function seedExpenses($branches, $expenseCategories): void
    {
        foreach ($branches as $branchIndex => $branch) {
            foreach ($expenseCategories as $categoryIndex => $category) {
                Expense::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'category_id' => $category->id,
                        'expense_date' => Carbon::today()->subDays($branchIndex + $categoryIndex + 3),
                    ],
                    [
                        'amount' => [18000, 42000, 12000, 8000][$categoryIndex],
                        'description' => 'مصروف تجريبي للواجهة ولوحة المالية',
                    ]
                );
            }
        }
    }

    private function seedPayrolls($teachers): void
    {
        foreach ($teachers as $teacher) {
            Payroll::updateOrCreate(
                ['teacher_id' => $teacher->id, 'month' => 5, 'year' => 2026],
                ['amount' => $teacher->salary_type === 'fixed' ? $teacher->salary : 52000, 'status' => 'paid']
            );
        }
    }

    private function seedNotifications(User $admin, $invoices): void
    {
        Notification::firstOrCreate(
            ['user_id' => $admin->id, 'title' => 'مرحبا بك في EduCenter'],
            ['body' => 'تم تجهيز بيانات تجريبية متكاملة لتجربة لوحة التحكم.', 'is_read' => false]
        );

        Notification::firstOrCreate(
            ['user_id' => $admin->id, 'title' => 'فواتير تحتاج متابعة'],
            ['body' => 'يوجد '.$invoices->where('status', '!=', 'paid')->count().' فواتير غير مدفوعة أو جزئية.', 'is_read' => false]
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
