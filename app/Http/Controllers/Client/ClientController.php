<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\{ClientDetailResource, ClientResource};
use App\Models\Client;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ClientResource::collection(Client::paginate());
    }

    public function show(Client $client): ClientDetailResource
    {
        $client->load(['transactions.gateway', 'transactions.products']);

        return ClientDetailResource::make($client);
    }
}
