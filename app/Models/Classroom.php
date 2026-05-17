<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Classroom extends Model
{
    protected $fillable = ['branch_id', 'name', 'capacity'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
