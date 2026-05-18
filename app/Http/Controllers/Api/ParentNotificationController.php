<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiModelResource;
use App\Services\Api\ParentNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentNotificationController extends Controller
{
    public function __construct(
        private readonly ParentNotificationService $parentNotifications,
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('parent-notifications.view'), 403);

        $data = $request->validate([
            'type' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'max:100'],
            'student_id' => ['sometimes', 'integer'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json(ApiModelResource::collection(
            $this->parentNotifications->paginate($request->user(), $data)
        )->response()->getData(true));
    }

    public function sendInvoiceReminders(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('parent-notifications.create'), 403);

        $data = $request->validate([
            'days_ahead' => ['sometimes', 'integer', 'min:0', 'max:60'],
        ]);

        return response()->json([
            'message' => 'Parent payment reminders processed.',
            'created' => $this->parentNotifications->sendUpcomingAndOverdueInvoiceReminders(
                $request->user(),
                (int) ($data['days_ahead'] ?? 3)
            ),
        ]);
    }
}
