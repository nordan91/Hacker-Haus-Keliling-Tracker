<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * GET /api/products
     *
     * List all products. Drivers see only active products.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query();

        // Drivers only see active products
        if ($request->user()->isDriver()) {
            $query->active();
        }

        // Optional category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('category')->orderBy('name')->get();

        return ProductResource::collection($products);
    }

    /**
     * POST /api/products (Admin only)
     *
     * Create a new product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'data'    => new ProductResource($product),
        ], 201);
    }

    /**
     * GET /api/products/{product}
     *
     * Show a single product.
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    /**
     * PUT /api/products/{product} (Admin only)
     *
     * Update a product.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * DELETE /api/products/{product} (Admin only)
     *
     * Soft-deactivate a product (set is_active to false).
     * We don't hard-delete to preserve transaction history.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->update(['is_active' => false]);

        return response()->json([
            'message' => 'Produk berhasil dinonaktifkan.',
        ]);
    }
}

