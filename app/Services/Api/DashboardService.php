<?php

namespace App\Services\Api;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    public function summary(User $user): array
    {
        $students = Student::where('tenant_id', $user->tenant_id);
        $teachers = Teacher::where('tenant_id', $user->tenant_id);
        $invoices = Invoice::whereHas('student', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id));
        $payments = Payment::whereHas('invoice.student', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id));
        $expenses = Expense::whereHas('branch', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id));

        return [
            'counts' => [
                'students' => (clone $students)->count(),
                'teachers' => (clone $teachers)->count(),
                'unpaid_invoices' => (clone $invoices)->where('status', '!=', 'paid')->count(),
            ],
            'finance' => [
                'invoice_total' => (float) (clone $invoices)->sum('total'),
                'paid_total' => (float) (clone $payments)->sum('amount'),
                'expenses_total' => (float) (clone $expenses)->sum('amount'),
            ],
            'recent_students' => (clone $students)->latest('id')->limit(5)->get(),
            'recent_payments' => (clone $payments)->latest('id')->limit(5)->get(),
        ];
    }
}
