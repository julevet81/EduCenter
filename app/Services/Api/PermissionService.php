<?php

namespace App\Services\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function paginate(int $perPage = 100): LengthAwarePaginator
    {
        return Permission::orderBy('name')
            ->paginate(min(max($perPage, 1), 200));
    }
}
