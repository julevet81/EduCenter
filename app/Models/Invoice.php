<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = ['student_id', 'enrollment_id', 'total', 'discount', 'due_date', 'status'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'discount' => 'decimal:2', 'due_date' => 'date'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function enrollment(): BelongsTo { return $this->belongsTo(Enrollment::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
