<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name'];
}
