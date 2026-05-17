<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiModelResource;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('dashboard.view'), 403);

        $tenantId = $request->user()->tenant_id;
        $students = Student::where('tenant_id', $tenantId);
        $teachers = Teacher::where('tenant_id', $tenantId);
        $invoices = Invoice::whereHas('student', fn (Builder $q) => $q->where('tenant_id', $tenantId));
        $payments = Payment::whereHas('invoice.student', fn (Builder $q) => $q->where('tenant_id', $tenantId));
        $expenses = Expense::whereHas('branch', fn (Builder $q) => $q->where('tenant_id', $tenantId));

        return response()->json([
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
            'recent' => [
                'students' => ApiModelResource::collection((clone $students)->latest('id')->limit(5)->get()),
                'payments' => ApiModelResource::collection((clone $payments)->latest('id')->limit(5)->get()),
            ],
        ]);
    }
}
