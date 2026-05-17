<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = ['group_id', 'title', 'exam_date', 'total_mark'];

    protected function casts(): array
    {
        return ['exam_date' => 'date', 'total_mark' => 'decimal:2'];
    }

    public function group(): BelongsTo { return $this->belongsTo(Group::class); }
    public function results(): HasMany { return $this->hasMany(ExamResult::class); }
}
