<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Api\ParentNotificationService;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        app(ParentNotificationService::class)->sendInvoiceReminder($invoice);
    }

    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged(['due_date', 'status', 'total'])) {
            app(ParentNotificationService::class)->sendInvoiceReminder($invoice);
        }
    }
}
