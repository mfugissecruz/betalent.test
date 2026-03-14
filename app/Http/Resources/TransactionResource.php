<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'external_id'       => $this->external_id,
            'status'            => $this->status,
            'amount'            => $this->amount,
            'card_last_numbers' => $this->card_last_numbers,
            'gateway'           => [
                'id'   => $this->gateway->id,
                'name' => $this->gateway->name,
            ],
            'products' => $this->products->map(fn ($product) => [
                'id'       => $product->id,
                'name'     => $product->name,
                'amount'   => $product->amount,
                'quantity' => $product->pivot->quantity,
            ]),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
