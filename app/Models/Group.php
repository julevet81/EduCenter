<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'branch_id', 'course_id', 'teacher_id', 'academic_year_id',
        'level_id', 'section_id', 'classroom_id', 'name', 'start_date', 'end_date',
        'max_students', 'status',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function schedules(): HasMany { return $this->hasMany(Schedule::class); }
}
