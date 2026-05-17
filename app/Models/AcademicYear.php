<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'start_date', 'end_date', 'is_current'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'is_current' => 'boolean'];
    }
}
