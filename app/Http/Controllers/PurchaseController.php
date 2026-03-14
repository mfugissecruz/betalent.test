<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StoreRequest;
use App\Http\Resources\TransactionResource;
use App\Models\{Client, Product, Transaction};
use App\Services\Gateways\GatewayManager;

class PurchaseController extends Controller
{
    public function __construct(private readonly GatewayManager $gatewayManager)
    {
    }

    public function __invoke(StoreRequest $request): TransactionResource
    {
        $payload = $request->validated();

        $client = Client::query()->firstOrCreate(
            ['email' => $payload['client']['email']],
            ['name' => $payload['client']['name']],
        );

        $productIds = collect($payload['products'])->pluck('id');
        $products   = Product::query()->findMany($productIds)->keyBy('id');

        $amount = collect($payload['products'])->sum(
            fn ($item) => $products->get($item['id'])->amount * $item['quantity']
        );

        $result = $this->gatewayManager->process([
            'amount' => $amount,
            'name'   => $client->name,
            'email'  => $client->email,

            'cardNumber' => $payload['card']['number'],
            'cvv'        => $payload['card']['cvv'],
        ]);

        $transaction = Transaction::query()->create([
            'client_id'         => $client->id,
            'gateway_id'        => $result['gateway']->id,
            'external_id'       => $result['response']['id'],
            'status'            => $result['response']['status'],
            'amount'            => $amount,
            'card_last_numbers' => substr($payload['card']['number'], -4),
        ]);

        $transaction->products()->attach(
            collect($payload['products'])->mapWithKeys(
                fn ($item) => [$item['id'] => ['quantity' => $item['quantity']]]
            )->all()
        );

        $transaction->load(['gateway', 'products']);

        return TransactionResource::make($transaction);
    }
}
