<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiModelResource;
use App\Services\Api\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('dashboard.view'), 403);

        $summary = $this->dashboard->summary($request->user());

        return response()->json([
            'counts' => $summary['counts'],
            'finance' => $summary['finance'],
            'recent' => [
                'students' => ApiModelResource::collection($summary['recent_students']),
                'payments' => ApiModelResource::collection($summary['recent_payments']),
            ],
        ]);
    }
}
