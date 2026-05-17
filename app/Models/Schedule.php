<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = ['group_id', 'day_of_week', 'start_time', 'end_time'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
