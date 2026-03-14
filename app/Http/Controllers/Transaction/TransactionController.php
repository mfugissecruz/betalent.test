<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TransactionResource::collection(Transaction::with(['gateway', 'products'])->paginate());
    }

    public function show(Transaction $transaction): TransactionResource
    {
        $transaction->load(['gateway', 'products']);

        return TransactionResource::make($transaction);
    }
}
