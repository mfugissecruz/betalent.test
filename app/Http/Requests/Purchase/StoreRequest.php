<?php

declare(strict_types = 1);

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client'              => ['required', 'array'],
            'client.name'         => ['required', 'string', 'max:255'],
            'client.email'        => ['required', 'email', 'max:255'],
            'card'                => ['required', 'array'],
            'card.number'         => ['required', 'string', 'size:16'],
            'card.cvv'            => ['required', 'string'],
            'products'            => ['required', 'array', 'min:1'],
            'products.*.id'       => ['required', 'integer', 'exists:products,id', 'distinct'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
