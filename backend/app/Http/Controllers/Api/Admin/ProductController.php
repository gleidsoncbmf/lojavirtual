<?php

namespace App\Http\Controllers\Api\Admin;

use App\DTOs\ProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\Contracts\Repositories\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['category_id', 'search', 'active', 'sort_by', 'sort_dir']);

        $products = $this->productService->getProducts(
            $user->store_id,
            $filters,
            $request->integer('per_page', 15)
        );

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'stock' => 'integer|min:0',
            'active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'variations' => 'nullable|array',
            'variations.*.name' => 'required_with:variations|string',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'integer|min:0',
            'variations.*.sku' => 'nullable|string',
            'variations.*.image' => 'nullable|string',
            'variations.*.weight' => 'nullable|numeric|min:0',
            'variations.*.length' => 'nullable|numeric|min:0',
            'variations.*.width' => 'nullable|numeric|min:0',
            'variations.*.height' => 'nullable|numeric|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $user = $request->user();
        $dto = ProductDTO::fromRequest($validated);
        $product = $this->productService->createProduct($user->store, $dto);

        return response()->json([
            'message' => 'Produto criado com sucesso.',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product || $product->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product || $product->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'stock' => 'sometimes|integer|min:0',
            'active' => 'sometimes|boolean',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array',
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.name' => 'required_with:variations|string',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'integer|min:0',
            'variations.*.sku' => 'nullable|string',
            'variations.*.image' => 'nullable|string',
            'variations.*.weight' => 'nullable|numeric|min:0',
            'variations.*.length' => 'nullable|numeric|min:0',
            'variations.*.width' => 'nullable|numeric|min:0',
            'variations.*.height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
        ]);

        $dto = ProductDTO::fromRequest(array_merge($product->toArray(), $validated));
        $product = $this->productService->updateProduct($product, $dto);

        return response()->json([
            'message' => 'Produto atualizado com sucesso.',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product || $product->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        $this->productService->deleteProduct($product);

        return response()->json(['message' => 'Produto excluído com sucesso.']);
    }
}
