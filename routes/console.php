<?php

use App\Models\User;
use App\Services\Api\ParentNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('parents:payment-reminders {--tenant_id=} {--days=3}', function (ParentNotificationService $notifications) {
    $query = User::query()
        ->whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))
        ->when($this->option('tenant_id'), fn ($q, $tenantId) => $q->where('tenant_id', $tenantId));

    $total = ['invoice_due' => 0, 'invoice_overdue' => 0];

    $query->get()
        ->unique('tenant_id')
        ->each(function (User $user) use ($notifications, &$total) {
            $created = $notifications->sendUpcomingAndOverdueInvoiceReminders($user, (int) $this->option('days'));
            $total['invoice_due'] += $created['invoice_due'];
            $total['invoice_overdue'] += $created['invoice_overdue'];
        });

    $this->info("Created {$total['invoice_due']} due reminders and {$total['invoice_overdue']} overdue reminders.");
})->purpose('Create parent notifications for due and overdue invoices');
