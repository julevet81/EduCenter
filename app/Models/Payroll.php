<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = ['teacher_id', 'month', 'year', 'amount', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
}
