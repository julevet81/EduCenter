<?php

namespace App\Services\Api;

use App\Models\AttendanceRecord;
use App\Models\Invoice;
use App\Models\ParentNotification;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ParentNotificationService
{
    private const ABSENCE_STATUSES = ['absent', 'absence', 'غائب', 'غياب'];

    private const LATE_STATUSES = ['late', 'tardy', 'متأخر', 'تأخر'];

    private const UNPAID_STATUSES = ['unpaid', 'pending', 'partial', 'overdue'];

    public function paginate(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = ParentNotification::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['student:id,first_name,last_name,parent_name,parent_phone', 'invoice:id,due_date,status,total', 'attendanceRecord:id,status']);

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);

        return $query->latest('id')->paginate($perPage);
    }

    public function notifyAttendanceRecord(AttendanceRecord $record): ?ParentNotification
    {
        $record->loadMissing('student', 'session.group');
        $status = strtolower((string) $record->status);
        $type = $this->attendanceNotificationType($status);

        if (! $type) {
            return null;
        }

        $student = $record->student;
        $studentName = $this->studentName($student);
        $date = $record->session?->session_date;
        $formattedDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        return $this->createOrReuse([
            'tenant_id' => $student->tenant_id,
            'student_id' => $student->id,
            'attendance_record_id' => $record->id,
            'type' => $type,
            'recipient_name' => $student->parent_name,
            'recipient_phone' => $student->parent_phone,
            'title' => $type === 'attendance_absence' ? 'Student absence alert' : 'Student late arrival alert',
            'body' => $type === 'attendance_absence'
                ? "We would like to inform you that {$studentName} was marked absent on {$formattedDate}."
                : "We would like to inform you that {$studentName} was marked late on {$formattedDate}.",
            'metadata' => [
                'attendance_status' => $record->status,
                'session_id' => $record->session_id,
                'session_date' => $formattedDate,
                'group_id' => $record->session?->group_id,
            ],
            'idempotency_key' => "{$type}:attendance_record:{$record->id}:{$record->status}",
        ]);
    }

    public function notifyInvoiceDue(Invoice $invoice, string $type): ?ParentNotification
    {
        $invoice->loadMissing('student');
        $student = $invoice->student;

        if (! $student || ! $invoice->due_date || ! $this->isUnpaid($invoice)) {
            return null;
        }

        $dueDate = Carbon::parse($invoice->due_date);
        $studentName = $this->studentName($student);
        $amount = number_format((float) $invoice->total, 2);
        $title = $type === 'invoice_overdue' ? 'Payment overdue alert' : 'Payment due reminder';
        $body = $type === 'invoice_overdue'
            ? "The payment of {$amount} for {$studentName} was due on {$dueDate->toDateString()} and is now overdue."
            : "This is a reminder that the payment of {$amount} for {$studentName} is due on {$dueDate->toDateString()}.";

        return $this->createOrReuse([
            'tenant_id' => $student->tenant_id,
            'student_id' => $student->id,
            'invoice_id' => $invoice->id,
            'type' => $type,
            'recipient_name' => $student->parent_name,
            'recipient_phone' => $student->parent_phone,
            'title' => $title,
            'body' => $body,
            'metadata' => [
                'invoice_status' => $invoice->status,
                'due_date' => $dueDate->toDateString(),
                'total' => (float) $invoice->total,
            ],
            'idempotency_key' => "{$type}:invoice:{$invoice->id}:{$dueDate->toDateString()}",
        ]);
    }

    public function sendUpcomingAndOverdueInvoiceReminders(User $user, int $daysAhead = 3): array
    {
        $today = Carbon::today();
        $upcomingLimit = $today->copy()->addDays(max($daysAhead, 0));
        $created = ['invoice_due' => 0, 'invoice_overdue' => 0];

        $this->invoiceQuery($user)
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $upcomingLimit)
            ->each(function (Invoice $invoice) use (&$created) {
                if ($this->notifyInvoiceDue($invoice, 'invoice_due')) {
                    $created['invoice_due']++;
                }
            });

        $this->invoiceQuery($user)
            ->whereDate('due_date', '<', $today)
            ->each(function (Invoice $invoice) use (&$created) {
                if ($this->notifyInvoiceDue($invoice, 'invoice_overdue')) {
                    $created['invoice_overdue']++;
                }
            });

        return $created;
    }

    public function sendInvoiceReminder(Invoice $invoice, ?CarbonInterface $today = null): ?ParentNotification
    {
        if (! $invoice->due_date) {
            return null;
        }

        $today ??= Carbon::today();
        $dueDate = Carbon::parse($invoice->due_date);

        if ($dueDate->gt($today)) {
            return null;
        }

        $type = $dueDate->lt($today) ? 'invoice_overdue' : 'invoice_due';

        return $this->notifyInvoiceDue($invoice, $type);
    }

    private function createOrReuse(array $data): ?ParentNotification
    {
        $data['channel'] = $data['channel'] ?? 'database';
        $data['status'] = filled($data['recipient_phone'] ?? null) ? 'sent' : 'skipped';
        $data['sent_at'] = $data['status'] === 'sent' ? now() : null;

        $notification = ParentNotification::firstOrCreate(
            ['idempotency_key' => $data['idempotency_key']],
            $data
        );

        return $notification->wasRecentlyCreated ? $notification : null;
    }

    private function invoiceQuery(User $user): Builder
    {
        return Invoice::query()
            ->with('student')
            ->whereNotNull('due_date')
            ->whereIn('status', self::UNPAID_STATUSES)
            ->whereHas('student', fn (Builder $q) => $q->where('tenant_id', $user->tenant_id));
    }

    private function isUnpaid(Invoice $invoice): bool
    {
        return in_array(strtolower((string) $invoice->status), self::UNPAID_STATUSES, true);
    }

    private function attendanceNotificationType(string $status): ?string
    {
        return match (true) {
            in_array($status, self::ABSENCE_STATUSES, true) => 'attendance_absence',
            in_array($status, self::LATE_STATUSES, true) => 'attendance_late',
            default => null,
        };
    }

    private function studentName(Student $student): string
    {
        return trim($student->first_name.' '.$student->last_name);
    }
}
