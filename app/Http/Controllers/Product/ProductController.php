<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\{StoreRequest, UpdateRequest};
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(Product::paginate());
    }

    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product);
    }

    public function store(StoreRequest $request): ProductResource
    {
        $data = $request->validated();

        return ProductResource::make(Product::create($data));
    }

    public function update(UpdateRequest $request, Product $product): ProductResource
    {
        $data = $request->validated();

        $product->update($data);

        return ProductResource::make($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
