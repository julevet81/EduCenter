<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = ['student_id', 'group_id', 'enrollment_date', 'registration_fee', 'discount', 'status'];

    protected function casts(): array
    {
        return ['enrollment_date' => 'date', 'registration_fee' => 'decimal:2', 'discount' => 'decimal:2'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function group(): BelongsTo { return $this->belongsTo(Group::class); }
}
