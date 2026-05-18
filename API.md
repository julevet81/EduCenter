# EduCenter API

Base URL:

```text
/api
```

## Authentication

Login:

```http
POST /api/auth/login
```

```json
{
  "email": "admin@gmail.com",
  "password": "12345678"
}
```

Use the returned token as:

```http
Authorization: Bearer <token>
```

Current user:

```http
GET /api/auth/me
```

Logout:

```http
POST /api/auth/logout
```

## Common Query Parameters

Most list endpoints support:

```text
?per_page=15
?search=ali
?include=branch,roles
```

`per_page` is capped to protect memory usage.

## Main Resources

All resources are protected by Sanctum and Spatie permissions.

```text
GET    /api/{resource}
POST   /api/{resource}
GET    /api/{resource}/{id}
PUT    /api/{resource}/{id}
PATCH  /api/{resource}/{id}
DELETE /api/{resource}/{id}
```

Resources:

```text
users
roles
permissions
branches
academic-years
levels
sections
students
student-documents
teachers
course-categories
courses
classrooms
groups
schedules
enrollments
attendance-sessions
attendance-records
invoices
payments
exams
exam-results
expense-categories
expenses
payrolls
notifications
parent-notifications
```

Dashboard:

```http
GET /api/dashboard/summary
```

Parent notification tools:

```http
GET /api/parent-notifications
POST /api/parent-notifications/payment-reminders
```

```json
{
  "days_ahead": 3
}
```

Attendance records with `status` equal to `absent`, `absence`, `late`, `tardy`, `غائب`, `غياب`, `متأخر`, or `تأخر` create parent notifications automatically. Payment reminders can also be generated from the CLI:

```bash
php artisan parents:payment-reminders --days=3
```

## Permissions

Permission names follow this pattern:

```text
students.view
students.create
students.update
students.delete
```

The default seeder creates a `super-admin` role with all permissions.
