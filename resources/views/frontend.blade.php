<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduCenter | تسيير مراكز التكوين</title>
    <style>
        :root {
            --bg: #f6f7fb;
            --panel: #ffffff;
            --panel-soft: #f0f7f5;
            --text: #17211f;
            --muted: #66736f;
            --line: #dfe7e3;
            --brand: #0f766e;
            --brand-dark: #115e59;
            --accent: #c2410c;
            --ok: #15803d;
            --warn: #b45309;
            --danger: #b91c1c;
            --shadow: 0 18px 50px rgba(17, 24, 39, .08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            background:
                linear-gradient(135deg, rgba(15, 118, 110, .08), transparent 38%),
                linear-gradient(315deg, rgba(194, 65, 12, .07), transparent 36%),
                var(--bg);
            font-family: Tahoma, "Segoe UI", Arial, sans-serif;
        }

        button, input, select, textarea { font: inherit; }
        button { cursor: pointer; }

        .hidden { display: none !important; }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(320px, 440px) minmax(0, 1fr);
        }

        .auth-panel {
            background: var(--panel);
            padding: 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid var(--line);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 34px;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: white;
            font-weight: 800;
            background: linear-gradient(135deg, var(--brand), var(--accent));
            box-shadow: 0 12px 28px rgba(15, 118, 110, .22);
        }

        .brand strong { display: block; font-size: 18px; }
        .brand span { color: var(--muted); font-size: 13px; }

        h1, h2, h3, p { margin: 0; }

        .auth-panel h1 {
            font-size: 30px;
            line-height: 1.35;
            margin-bottom: 12px;
        }

        .auth-panel p {
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 28px;
        }

        .form-grid {
            display: grid;
            gap: 14px;
        }

        label {
            display: grid;
            gap: 7px;
            color: #33423f;
            font-size: 13px;
            font-weight: 700;
        }

        input, select, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: white;
            color: var(--text);
            padding: 11px 12px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        textarea { min-height: 92px; resize: vertical; }

        input:focus, select:focus, textarea:focus {
            border-color: rgba(15, 118, 110, .75);
            box-shadow: 0 0 0 4px rgba(15, 118, 110, .12);
        }

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 11px 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            background: #e7eeeb;
            color: var(--text);
            font-weight: 800;
        }

        .btn.primary { background: var(--brand); color: white; }
        .btn.primary:hover { background: var(--brand-dark); }
        .btn.ghost { background: transparent; border: 1px solid var(--line); }
        .btn.warn { background: #fff4e8; color: var(--warn); }

        .auth-visual {
            padding: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview {
            width: min(780px, 100%);
            background: rgba(255, 255, 255, .74);
            border: 1px solid rgba(255, 255, 255, .82);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .preview-top {
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            border-bottom: 1px solid var(--line);
            background: white;
        }

        .preview-body {
            padding: 24px;
            display: grid;
            gap: 18px;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .mini-card {
            background: white;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
        }

        .mini-card b { display: block; font-size: 24px; margin-top: 10px; }
        .mini-card span { color: var(--muted); font-size: 13px; }

        .bar-list { display: grid; gap: 12px; }
        .bar { height: 12px; border-radius: 999px; background: #e5ede9; overflow: hidden; }
        .bar i { display: block; height: 100%; background: var(--brand); }

        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
        }

        aside {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 22px;
            background: #10201d;
            color: white;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        aside .brand { margin: 0; }
        aside .brand span { color: #a7bab5; }

        .nav {
            display: grid;
            gap: 7px;
            overflow: auto;
            padding-left: 4px;
        }

        .nav button {
            width: 100%;
            border: 0;
            border-radius: 8px;
            padding: 11px 12px;
            color: #dbe7e4;
            background: transparent;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: right;
        }

        .nav button.active,
        .nav button:hover {
            background: rgba(255,255,255,.1);
            color: white;
        }

        .user-box {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,.14);
            padding-top: 16px;
            display: grid;
            gap: 10px;
        }

        main {
            min-width: 0;
            padding: 24px;
        }

        .topbar {
            display: flex;
            gap: 14px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .topbar h1 { font-size: 24px; }
        .topbar p { color: var(--muted); margin-top: 5px; }

        .actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search {
            min-width: 260px;
            background: white;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 10px 28px rgba(17, 24, 39, .04);
        }

        .stat span { color: var(--muted); font-size: 13px; }
        .stat b { display: block; font-size: 27px; margin-top: 9px; }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 18px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(17, 24, 39, .04);
            overflow: hidden;
        }

        .panel-head {
            min-height: 58px;
            padding: 15px 18px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .panel-head h2 { font-size: 16px; }
        .panel-head span { color: var(--muted); font-size: 13px; }

        .table-wrap { overflow: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th, td {
            padding: 13px 16px;
            text-align: right;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
            white-space: nowrap;
        }

        th {
            color: #40504c;
            font-size: 12px;
            background: #f8faf9;
        }

        td { font-size: 14px; }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 25px;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            background: #e7eeeb;
            color: #34433f;
        }

        .badge.paid, .badge.active, .badge.present { background: #dcfce7; color: var(--ok); }
        .badge.pending, .badge.unpaid, .badge.absent { background: #fff7ed; color: var(--warn); }
        .badge.cancelled, .badge.inactive { background: #fee2e2; color: var(--danger); }

        .side-stack {
            display: grid;
            gap: 18px;
        }

        .quick-list {
            display: grid;
            gap: 10px;
            padding: 16px;
        }

        .quick-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbfdfc;
        }

        .quick-item strong { display: block; font-size: 14px; }
        .quick-item span { color: var(--muted); font-size: 12px; }

        .empty {
            padding: 36px 18px;
            color: var(--muted);
            text-align: center;
            line-height: 1.8;
        }

        .toast {
            position: fixed;
            left: 20px;
            bottom: 20px;
            z-index: 30;
            max-width: 360px;
            background: #10201d;
            color: white;
            border-radius: 8px;
            padding: 13px 15px;
            box-shadow: var(--shadow);
        }

        .modal {
            position: fixed;
            inset: 0;
            z-index: 20;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(12, 20, 18, .42);
        }

        .modal-card {
            width: min(760px, 100%);
            max-height: min(820px, 92vh);
            overflow: auto;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .modal-body { padding: 18px; }
        .modal-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
            padding-top: 18px;
        }

        .mobile-menu { display: none; }

        @media (max-width: 1100px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 840px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-visual { display: none; }
            .app-shell { grid-template-columns: 1fr; }
            aside {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(320px, 88vw);
                z-index: 15;
                transform: translateX(-100%);
                transition: transform .2s ease;
            }
            aside.open { transform: translateX(0); }
            .mobile-menu { display: inline-flex; }
            main { padding: 16px; }
            .topbar { align-items: stretch; flex-direction: column; }
            .actions { flex-wrap: wrap; }
            .search { min-width: 0; flex: 1; }
            .modal-fields { grid-template-columns: 1fr; }
        }

        @media (max-width: 560px) {
            .auth-panel { padding: 26px; }
            .stats { grid-template-columns: 1fr; }
            .topbar h1 { font-size: 20px; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <section id="authScreen" class="auth-shell">
        <div class="auth-panel">
            <div class="brand">
                <div class="brand-mark">EC</div>
                <div>
                    <strong>EduCenter</strong>
                    <span>منصة تسيير مراكز التكوين والدعم</span>
                </div>
            </div>

            <h1>واجهة إدارية احترافية مرتبطة مباشرة بالباكند.</h1>
            <p>سجل الدخول بحساب المدير ثم انتقل بين الطلاب، الأساتذة، الدورات، الأفواج، الفواتير، الحضور والمالية من نفس اللوحة.</p>

            <form id="loginForm" class="form-grid">
                <label>
                    البريد الإلكتروني
                    <input name="email" type="email" value="admin@gmail.com" autocomplete="email" required>
                </label>
                <label>
                    كلمة المرور
                    <input name="password" type="password" value="12345678" autocomplete="current-password" required>
                </label>
                <button class="btn primary" type="submit">دخول إلى لوحة التحكم</button>
                <button class="btn ghost" type="button" id="demoButton">معاينة بدون تسجيل دخول</button>
            </form>
        </div>

        <div class="auth-visual">
            <div class="preview">
                <div class="preview-top">
                    <strong>لوحة مراقبة المركز</strong>
                    <span class="badge active">نشط</span>
                </div>
                <div class="preview-body">
                    <div class="mini-grid">
                        <div class="mini-card"><span>الطلاب</span><b>248</b></div>
                        <div class="mini-card"><span>الأساتذة</span><b>32</b></div>
                        <div class="mini-card"><span>فواتير معلقة</span><b>18</b></div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>نشاط التسجيل والدفع</h2>
                            <span>هذا الشهر</span>
                        </div>
                        <div class="quick-list">
                            <div class="bar"><i style="width: 82%"></i></div>
                            <div class="bar"><i style="width: 61%; background: var(--accent)"></i></div>
                            <div class="bar"><i style="width: 74%"></i></div>
                            <div class="bar"><i style="width: 48%; background: var(--warn)"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="appScreen" class="app-shell hidden">
        <aside id="sidebar">
            <div class="brand">
                <div class="brand-mark">EC</div>
                <div>
                    <strong>EduCenter</strong>
                    <span id="tenantName">لوحة الإدارة</span>
                </div>
            </div>

            <nav id="nav" class="nav"></nav>

            <div class="user-box">
                <div>
                    <strong id="userName">مستخدم</strong>
                    <span id="userEmail" style="display:block;color:#a7bab5;font-size:12px;margin-top:4px"></span>
                </div>
                <button id="logoutButton" class="btn warn" type="button">تسجيل الخروج</button>
            </div>
        </aside>

        <main>
            <div class="topbar">
                <div>
                    <button id="menuButton" class="btn ghost mobile-menu" type="button">القائمة</button>
                    <h1 id="pageTitle">لوحة التحكم</h1>
                    <p id="pageSubtitle">نظرة عامة على أداء المركز والعمليات اليومية.</p>
                </div>
                <div class="actions">
                    <input id="searchInput" class="search" type="search" placeholder="بحث سريع..." disabled>
                    <button id="refreshButton" class="btn ghost" type="button">تحديث</button>
                    <button id="createButton" class="btn primary" type="button">إضافة</button>
                </div>
            </div>

            <div id="dashboardView">
                <div id="stats" class="stats"></div>
                <div class="content-grid">
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>آخر الطلاب المسجلين</h2>
                                <span>بيانات مباشرة من `/api/dashboard/summary`</span>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead><tr><th>الطالب</th><th>الهاتف</th><th>ولي الأمر</th><th>الفرع</th></tr></thead>
                                <tbody id="recentStudents"></tbody>
                            </table>
                        </div>
                    </section>

                    <div class="side-stack">
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>مؤشر المالية</h2>
                                    <span>مدفوعات، مصاريف، وفواتير</span>
                                </div>
                            </div>
                            <div id="financeList" class="quick-list"></div>
                        </section>

                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>آخر المدفوعات</h2>
                                    <span>أحدث العمليات</span>
                                </div>
                            </div>
                            <div id="recentPayments" class="quick-list"></div>
                        </section>
                    </div>
                </div>
            </div>

            <section id="resourceView" class="panel hidden">
                <div class="panel-head">
                    <div>
                        <h2 id="resourceTitle">الموارد</h2>
                        <span id="resourceMeta">تحميل البيانات...</span>
                    </div>
                </div>
                <div id="resourceTable" class="table-wrap"></div>
            </section>
        </main>
    </section>

    <div id="modal" class="modal hidden">
        <div class="modal-card">
            <div class="panel-head">
                <div>
                    <h2 id="modalTitle">إضافة</h2>
                    <span>سيتم حفظ البيانات عبر API الباكند</span>
                </div>
                <button id="closeModal" class="btn ghost" type="button">إغلاق</button>
            </div>
            <form id="createForm" class="modal-body">
                <div id="modalFields" class="modal-fields"></div>
                <div class="form-actions">
                    <button class="btn primary" type="submit">حفظ</button>
                    <button id="cancelModal" class="btn ghost" type="button">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="toast hidden"></div>

    <script>
        const apiBase = '/api';
        const tokenKey = 'educenter_token';
        const state = {
            token: localStorage.getItem(tokenKey),
            user: null,
            active: 'dashboard',
            demo: false,
            resources: {
                students: {
                    label: 'الطلاب', icon: 'طلاب',
                    endpoint: 'students',
                    columns: ['id', 'first_name', 'last_name', 'phone', 'parent_phone', 'created_at'],
                    fields: [
                        ['branch_id', 'number', 'الفرع', true],
                        ['first_name', 'text', 'الاسم', true],
                        ['last_name', 'text', 'اللقب', true],
                        ['gender', 'select', 'الجنس', false, ['male', 'female']],
                        ['birth_date', 'date', 'تاريخ الميلاد', false],
                        ['phone', 'text', 'هاتف الطالب', false],
                        ['parent_name', 'text', 'ولي الأمر', false],
                        ['parent_phone', 'text', 'هاتف الولي', false],
                        ['address', 'textarea', 'العنوان', false],
                    ],
                },
                teachers: {
                    label: 'الأساتذة', endpoint: 'teachers',
                    columns: ['id', 'full_name', 'specialization', 'phone', 'email', 'salary_type', 'salary'],
                    fields: [
                        ['branch_id', 'number', 'الفرع', true],
                        ['full_name', 'text', 'الاسم الكامل', true],
                        ['specialization', 'text', 'التخصص', false],
                        ['phone', 'text', 'الهاتف', false],
                        ['email', 'email', 'البريد', false],
                        ['salary_type', 'select', 'نوع الراتب', true, ['fixed', 'hourly', 'percentage']],
                        ['salary', 'number', 'الراتب', true],
                    ],
                },
                branches: {
                    label: 'الفروع', endpoint: 'branches',
                    columns: ['id', 'name', 'phone', 'address', 'manager_id'],
                    fields: [
                        ['name', 'text', 'اسم الفرع', true],
                        ['phone', 'text', 'الهاتف', false],
                        ['address', 'textarea', 'العنوان', false],
                        ['manager_id', 'number', 'معرف المدير', false],
                    ],
                },
                courses: {
                    label: 'الدورات', endpoint: 'courses',
                    columns: ['id', 'name', 'type', 'duration_hours', 'category_id'],
                    fields: [
                        ['category_id', 'number', 'التصنيف', true],
                        ['name', 'text', 'اسم الدورة', true],
                        ['type', 'select', 'النوع', true, ['school_support', 'language', 'training']],
                        ['duration_hours', 'number', 'عدد الساعات', true],
                        ['description', 'textarea', 'الوصف', false],
                    ],
                },
                groups: {
                    label: 'الأفواج', endpoint: 'groups',
                    columns: ['id', 'name', 'status', 'course_id', 'teacher_id', 'max_students', 'start_date'],
                    fields: [
                        ['branch_id', 'number', 'الفرع', true],
                        ['course_id', 'number', 'الدورة', true],
                        ['teacher_id', 'number', 'الأستاذ', true],
                        ['academic_year_id', 'number', 'السنة الدراسية', true],
                        ['name', 'text', 'اسم الفوج', true],
                        ['max_students', 'number', 'العدد الأقصى', true],
                        ['status', 'select', 'الحالة', true, ['active', 'pending', 'inactive']],
                        ['start_date', 'date', 'تاريخ البداية', false],
                        ['end_date', 'date', 'تاريخ النهاية', false],
                    ],
                },
                invoices: {
                    label: 'الفواتير', endpoint: 'invoices',
                    columns: ['id', 'student_id', 'total', 'discount', 'due_date', 'status'],
                    fields: [
                        ['student_id', 'number', 'الطالب', true],
                        ['enrollment_id', 'number', 'التسجيل', true],
                        ['total', 'number', 'المبلغ', true],
                        ['discount', 'number', 'التخفيض', false],
                        ['due_date', 'date', 'آخر أجل', false],
                        ['status', 'select', 'الحالة', true, ['unpaid', 'paid', 'pending', 'cancelled']],
                    ],
                },
                payments: {
                    label: 'المدفوعات', endpoint: 'payments',
                    columns: ['id', 'invoice_id', 'amount', 'payment_method', 'paid_at', 'reference'],
                    fields: [
                        ['invoice_id', 'number', 'الفاتورة', true],
                        ['amount', 'number', 'المبلغ', true],
                        ['payment_method', 'select', 'طريقة الدفع', true, ['cash', 'bank', 'card', 'transfer']],
                        ['paid_at', 'date', 'تاريخ الدفع', true],
                        ['reference', 'text', 'المرجع', false],
                    ],
                },
                schedules: {
                    label: 'الجداول', endpoint: 'schedules',
                    columns: ['id', 'group_id', 'day_of_week', 'start_time', 'end_time'],
                    fields: [
                        ['group_id', 'number', 'الفوج', true],
                        ['day_of_week', 'number', 'اليوم 1-7', true],
                        ['start_time', 'time', 'البداية', true],
                        ['end_time', 'time', 'النهاية', true],
                    ],
                },
                expenses: {
                    label: 'المصاريف', endpoint: 'expenses',
                    columns: ['id', 'branch_id', 'category_id', 'amount', 'expense_date', 'description'],
                    fields: [
                        ['branch_id', 'number', 'الفرع', true],
                        ['category_id', 'number', 'التصنيف', true],
                        ['amount', 'number', 'المبلغ', true],
                        ['expense_date', 'date', 'التاريخ', true],
                        ['description', 'textarea', 'الوصف', false],
                    ],
                },
                exams: {
                    label: 'الاختبارات', endpoint: 'exams',
                    columns: ['id', 'group_id', 'title', 'exam_date', 'total_mark'],
                    fields: [
                        ['group_id', 'number', 'الفوج', true],
                        ['title', 'text', 'العنوان', true],
                        ['exam_date', 'date', 'تاريخ الاختبار', true],
                        ['total_mark', 'number', 'العلامة الكاملة', true],
                    ],
                },
                notifications: {
                    label: 'الإشعارات', endpoint: 'notifications',
                    columns: ['id', 'user_id', 'title', 'is_read', 'created_at'],
                    fields: [
                        ['user_id', 'number', 'المستخدم', true],
                        ['title', 'text', 'العنوان', true],
                        ['body', 'textarea', 'المحتوى', true],
                        ['is_read', 'select', 'مقروء', false, ['0', '1']],
                    ],
                },
            },
        };

        const $ = (id) => document.getElementById(id);
        const money = (value) => new Intl.NumberFormat('ar-DZ', { style: 'currency', currency: 'DZD', maximumFractionDigits: 0 }).format(Number(value || 0));
        const cleanDate = (value) => value ? String(value).slice(0, 10) : '-';

        function toast(message) {
            $('toast').textContent = message;
            $('toast').classList.remove('hidden');
            clearTimeout(window.toastTimer);
            window.toastTimer = setTimeout(() => $('toast').classList.add('hidden'), 3600);
        }

        async function request(path, options = {}) {
            if (state.demo) throw new Error('demo');
            const headers = {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(options.headers || {}),
            };
            if (state.token) headers.Authorization = `Bearer ${state.token}`;

            const response = await fetch(`${apiBase}/${path}`, { ...options, headers });
            const text = await response.text();
            const data = text ? JSON.parse(text) : {};
            if (!response.ok) {
                const message = data.message || Object.values(data.errors || {}).flat().join('، ') || 'تعذر تنفيذ العملية';
                throw new Error(message);
            }
            return data;
        }

        function showApp() {
            $('authScreen').classList.add('hidden');
            $('appScreen').classList.remove('hidden');
            renderNav();
            renderUser();
            setActive(state.active || 'dashboard');
        }

        function showAuth() {
            $('authScreen').classList.remove('hidden');
            $('appScreen').classList.add('hidden');
        }

        function renderUser() {
            $('userName').textContent = state.user?.full_name || 'معاينة تجريبية';
            $('userEmail').textContent = state.user?.email || 'بدون اتصال API';
        }

        function renderNav() {
            const items = [
                ['dashboard', 'لوحة التحكم'],
                ...Object.entries(state.resources).map(([key, config]) => [key, config.label]),
            ];
            $('nav').innerHTML = items.map(([key, label]) => `
                <button type="button" class="${state.active === key ? 'active' : ''}" data-key="${key}">
                    <span>${label}</span><small>${key === 'dashboard' ? 'عام' : 'API'}</small>
                </button>
            `).join('');

            $('nav').querySelectorAll('button').forEach((button) => {
                button.addEventListener('click', () => {
                    $('sidebar').classList.remove('open');
                    setActive(button.dataset.key);
                });
            });
        }

        function setActive(key) {
            state.active = key;
            renderNav();
            $('searchInput').value = '';
            $('searchInput').disabled = key === 'dashboard';
            $('createButton').classList.toggle('hidden', key === 'dashboard');
            $('dashboardView').classList.toggle('hidden', key !== 'dashboard');
            $('resourceView').classList.toggle('hidden', key === 'dashboard');
            if (key === 'dashboard') loadDashboard();
            else loadResource(key);
        }

        function demoSummary() {
            return {
                counts: { students: 248, teachers: 32, unpaid_invoices: 18 },
                finance: { invoice_total: 1860000, paid_total: 1295000, expenses_total: 345000 },
                recent_students: [
                    { first_name: 'أمين', last_name: 'بن يوسف', phone: '0550123456', parent_phone: '0660111222', branch_id: 1 },
                    { first_name: 'مريم', last_name: 'خالد', phone: '0550654321', parent_phone: '0660222333', branch_id: 1 },
                    { first_name: 'سارة', last_name: 'علي', phone: '0550789456', parent_phone: '0660444555', branch_id: 2 },
                ],
                recent_payments: [
                    { amount: 12000, payment_method: 'cash', paid_at: '2026-05-24', reference: 'PAY-204' },
                    { amount: 9000, payment_method: 'bank', paid_at: '2026-05-22', reference: 'PAY-203' },
                    { amount: 15000, payment_method: 'cash', paid_at: '2026-05-20', reference: 'PAY-202' },
                ],
            };
        }

        async function loadDashboard() {
            $('pageTitle').textContent = 'لوحة التحكم';
            $('pageSubtitle').textContent = 'نظرة عامة على أداء المركز والعمليات اليومية.';
            let summary;
            try {
                summary = state.demo ? demoSummary() : await request('dashboard/summary');
            } catch (error) {
                toast(error.message);
                summary = demoSummary();
            }
            renderDashboard(summary);
        }

        function renderDashboard(summary) {
            const profit = Number(summary.finance?.paid_total || 0) - Number(summary.finance?.expenses_total || 0);
            const stats = [
                ['الطلاب', summary.counts?.students || 0],
                ['الأساتذة', summary.counts?.teachers || 0],
                ['فواتير غير مدفوعة', summary.counts?.unpaid_invoices || 0],
                ['الصافي', money(profit)],
            ];
            $('stats').innerHTML = stats.map(([label, value]) => `<div class="stat"><span>${label}</span><b>${value}</b></div>`).join('');

            const students = summary.recent_students || [];
            $('recentStudents').innerHTML = students.length
                ? students.map((student) => `
                    <tr>
                        <td><strong>${student.first_name || ''} ${student.last_name || ''}</strong></td>
                        <td>${student.phone || '-'}</td>
                        <td>${student.parent_phone || student.parent_name || '-'}</td>
                        <td>${student.branch_id || '-'}</td>
                    </tr>
                `).join('')
                : `<tr><td colspan="4"><div class="empty">لا توجد تسجيلات حديثة بعد.</div></td></tr>`;

            $('financeList').innerHTML = [
                ['إجمالي الفواتير', money(summary.finance?.invoice_total)],
                ['المبالغ المدفوعة', money(summary.finance?.paid_total)],
                ['المصاريف', money(summary.finance?.expenses_total)],
            ].map(([label, value]) => `<div class="quick-item"><span>${label}</span><strong>${value}</strong></div>`).join('');

            const payments = summary.recent_payments || [];
            $('recentPayments').innerHTML = payments.length
                ? payments.map((payment) => `
                    <div class="quick-item">
                        <div><strong>${money(payment.amount)}</strong><span>${payment.payment_method || '-'} · ${cleanDate(payment.paid_at)}</span></div>
                        <span class="badge paid">${payment.reference || 'دفع'}</span>
                    </div>
                `).join('')
                : `<div class="empty">لا توجد مدفوعات حديثة.</div>`;
        }

        function demoRows(key) {
            const demos = {
                students: [
                    { id: 1, first_name: 'أمين', last_name: 'بن يوسف', phone: '0550123456', parent_phone: '0660111222', created_at: '2026-05-20' },
                    { id: 2, first_name: 'مريم', last_name: 'خالد', phone: '0550654321', parent_phone: '0660222333', created_at: '2026-05-21' },
                ],
                teachers: [
                    { id: 1, full_name: 'أستاذة نوال مراد', specialization: 'إنجليزية', phone: '0550999888', email: 'teacher@example.com', salary_type: 'fixed', salary: 68000 },
                ],
                invoices: [
                    { id: 1, student_id: 1, total: 18000, discount: 0, due_date: '2026-05-30', status: 'unpaid' },
                    { id: 2, student_id: 2, total: 15000, discount: 1000, due_date: '2026-05-29', status: 'paid' },
                ],
            };
            return demos[key] || [{ id: 1, name: 'نموذج تجريبي', status: 'active', created_at: '2026-05-25' }];
        }

        async function loadResource(key, search = '') {
            const config = state.resources[key];
            $('pageTitle').textContent = config.label;
            $('pageSubtitle').textContent = `إدارة ${config.label} عبر /api/${config.endpoint}`;
            $('resourceTitle').textContent = config.label;
            $('resourceMeta').textContent = 'جاري تحميل البيانات...';
            $('resourceTable').innerHTML = `<div class="empty">جاري تحميل البيانات...</div>`;
            try {
                const params = new URLSearchParams({ per_page: '15' });
                if (search) params.set('search', search);
                const payload = state.demo ? { data: demoRows(key), meta: { total: demoRows(key).length } } : await request(`${config.endpoint}?${params}`);
                renderResourceTable(key, payload.data || []);
                $('resourceMeta').textContent = `${payload.meta?.total ?? (payload.data || []).length} سجل`;
            } catch (error) {
                $('resourceTable').innerHTML = `<div class="empty">${error.message}</div>`;
                $('resourceMeta').textContent = 'تعذر تحميل البيانات';
            }
        }

        function valueFor(row, column) {
            const value = row[column];
            if (value === null || value === undefined || value === '') return '-';
            if (column.includes('date') || column.includes('_at')) return cleanDate(value);
            if (column === 'status' || column === 'is_active' || column === 'is_read') {
                return `<span class="badge ${String(value).toLowerCase()}">${value}</span>`;
            }
            if (column === 'salary' || column === 'total' || column === 'amount' || column === 'discount') return money(value);
            return String(value);
        }

        function renderResourceTable(key, rows) {
            const columns = state.resources[key].columns;
            if (!rows.length) {
                $('resourceTable').innerHTML = `<div class="empty">لا توجد بيانات بعد. استخدم زر الإضافة لإنشاء أول سجل.</div>`;
                return;
            }

            $('resourceTable').innerHTML = `
                <table>
                    <thead><tr>${columns.map((column) => `<th>${column}</th>`).join('')}</tr></thead>
                    <tbody>
                        ${rows.map((row) => `<tr>${columns.map((column) => `<td>${valueFor(row, column)}</td>`).join('')}</tr>`).join('')}
                    </tbody>
                </table>
            `;
        }

        function openCreateModal() {
            const config = state.resources[state.active];
            if (!config) return;
            $('modalTitle').textContent = `إضافة ${config.label}`;
            $('modalFields').innerHTML = config.fields.map(([name, type, label, required, options]) => {
                if (type === 'textarea') {
                    return `<label>${label}<textarea name="${name}" ${required ? 'required' : ''}></textarea></label>`;
                }
                if (type === 'select') {
                    return `<label>${label}<select name="${name}" ${required ? 'required' : ''}>
                        <option value="">اختر...</option>
                        ${(options || []).map((option) => `<option value="${option}">${option}</option>`).join('')}
                    </select></label>`;
                }
                return `<label>${label}<input name="${name}" type="${type}" ${required ? 'required' : ''}></label>`;
            }).join('');
            $('modal').classList.remove('hidden');
        }

        async function submitCreate(event) {
            event.preventDefault();
            const config = state.resources[state.active];
            const form = new FormData(event.currentTarget);
            const data = {};
            for (const [key, value] of form.entries()) {
                if (value === '') continue;
                data[key] = event.currentTarget.elements[key].type === 'number' ? Number(value) : value;
            }
            if (state.demo) {
                toast('هذه معاينة فقط. سجل الدخول لحفظ البيانات في الباكند.');
                $('modal').classList.add('hidden');
                return;
            }
            try {
                await request(config.endpoint, { method: 'POST', body: JSON.stringify(data) });
                toast('تم حفظ السجل بنجاح');
                $('modal').classList.add('hidden');
                await loadResource(state.active);
            } catch (error) {
                toast(error.message);
            }
        }

        $('loginForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = new FormData(event.currentTarget);
            try {
                const payload = await request('auth/login', {
                    method: 'POST',
                    body: JSON.stringify(Object.fromEntries(form.entries())),
                });
                state.token = payload.token;
                state.user = payload.user;
                state.demo = false;
                localStorage.setItem(tokenKey, payload.token);
                showApp();
                toast('تم تسجيل الدخول بنجاح');
            } catch (error) {
                toast(error.message);
            }
        });

        $('demoButton').addEventListener('click', () => {
            state.demo = true;
            state.user = null;
            state.token = null;
            state.active = 'dashboard';
            showApp();
        });

        $('logoutButton').addEventListener('click', async () => {
            try {
                if (state.token && !state.demo) await request('auth/logout', { method: 'POST' });
            } catch (error) {
                console.warn(error);
            }
            localStorage.removeItem(tokenKey);
            state.token = null;
            state.user = null;
            state.demo = false;
            showAuth();
        });

        $('refreshButton').addEventListener('click', () => setActive(state.active));
        $('createButton').addEventListener('click', openCreateModal);
        $('closeModal').addEventListener('click', () => $('modal').classList.add('hidden'));
        $('cancelModal').addEventListener('click', () => $('modal').classList.add('hidden'));
        $('createForm').addEventListener('submit', submitCreate);
        $('menuButton').addEventListener('click', () => $('sidebar').classList.add('open'));
        $('searchInput').addEventListener('input', (event) => {
            clearTimeout(window.searchTimer);
            window.searchTimer = setTimeout(() => loadResource(state.active, event.target.value.trim()), 350);
        });

        (async function init() {
            if (!state.token) {
                showAuth();
                return;
            }
            try {
                state.user = await request('auth/me');
                showApp();
            } catch (error) {
                localStorage.removeItem(tokenKey);
                state.token = null;
                showAuth();
            }
        })();
    </script>
</body>
</html>
