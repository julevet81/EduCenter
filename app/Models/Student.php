<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'branch_id', 'first_name', 'last_name', 'qr_code', 'gender', 'birth_date',
        'phone', 'parent_phone', 'parent_name', 'address',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function parentNotifications(): HasMany
    {
        return $this->hasMany(ParentNotification::class);
    }
}
