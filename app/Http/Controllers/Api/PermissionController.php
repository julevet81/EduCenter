<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('permissions.view'), 403);

        return response()->json(Permission::orderBy('name')->paginate(
            min(max((int) $request->integer('per_page', 100), 1), 200)
        ));
    }
}
