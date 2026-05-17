<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    protected $fillable = ['group_id', 'session_date'];

    protected function casts(): array
    {
        return ['session_date' => 'date'];
    }

    public function group(): BelongsTo { return $this->belongsTo(Group::class); }
    public function records(): HasMany { return $this->hasMany(AttendanceRecord::class, 'session_id'); }
}
