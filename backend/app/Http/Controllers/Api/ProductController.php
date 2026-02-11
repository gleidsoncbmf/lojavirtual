<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $store = $request->get('store');

        $filters = array_merge(
            $request->only(['category_id', 'search', 'min_price', 'max_price', 'sort_by', 'sort_dir']),
            ['active' => true]
        );

        $products = $this->productService->getProducts(
            $store->id,
            $filters,
            $request->integer('per_page', 15)
        );

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $store = $request->get('store');
        $product = $this->productService->getProductBySlug($store->id, $slug);

        if (!$product || !$product->active) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }
}
