<?php

namespace App\Observers;

use App\Models\AttendanceRecord;
use App\Services\Api\ParentNotificationService;

class AttendanceRecordObserver
{
    public function created(AttendanceRecord $attendanceRecord): void
    {
        app(ParentNotificationService::class)->notifyAttendanceRecord($attendanceRecord);
    }

    public function updated(AttendanceRecord $attendanceRecord): void
    {
        if ($attendanceRecord->wasChanged('status')) {
            app(ParentNotificationService::class)->notifyAttendanceRecord($attendanceRecord);
        }
    }
}
