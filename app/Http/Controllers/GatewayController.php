<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Requests\GatewayPriority\{UpdatePriorityRequest, UpdateStatusRequest};
use App\Models\Gateway;
use Illuminate\Http\JsonResponse;

class GatewayController extends Controller
{
    public function activate(UpdateStatusRequest $request, Gateway $gateway): JsonResponse
    {
        $request->validated();

        $gateway->update(['is_active' => true]);

        return response()->json(['message' => 'Gateway  successfully']);
    }

    public function deactivate(UpdateStatusRequest $request, Gateway $gateway): JsonResponse
    {
        $request->validated();

        $gateway->update(['is_active' => false]);

        return response()->json(['message' => 'Gateway status updated successfully']);
    }

    public function updatePriority(UpdatePriorityRequest $request, Gateway $gateway): JsonResponse
    {
        $data = $request->validated();

        $gateway->update(['priority' => $data['priority']]);

        return response()->json(['message' => 'Gateway priority updated successfully']);
    }
}
