<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Gateways\GatewayManager;
use Illuminate\Http\{JsonResponse, Response};

class RefundController extends Controller
{
    public function __construct(private readonly GatewayManager $gatewayManager)
    {
    }

    public function __invoke(Transaction $transaction): JsonResponse
    {
        if ($transaction->status === 'charged_back') {
            return response()->json(['message' => 'Transaction already refunded.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $transaction->load('gateway');

        $this->gatewayManager->refund($transaction->external_id, $transaction->gateway->name);

        $transaction->update(['status' => 'charged_back']);

        return response()->json(['message' => 'Transaction refunded successfully.']);
    }
}
