<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Requests\GatewayPriority\UpdatePriorityRequest;
use App\Http\Resources\GatewayResource;
use App\Models\Gateway;

class GatewayController extends Controller
{
    public function activate(Gateway $gateway): GatewayResource
    {
        $gateway->update(['is_active' => true]);

        return GatewayResource::make($gateway);
    }

    public function deactivate(Gateway $gateway): GatewayResource
    {
        $gateway->update(['is_active' => false]);

        return GatewayResource::make($gateway);
    }

    public function updatePriority(UpdatePriorityRequest $request, Gateway $gateway): GatewayResource
    {
        $gateway->update($request->validated());

        return GatewayResource::make($gateway);
    }
}
