<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private ProductService $productService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $store = $request->get('store');
        $categories = $this->categoryRepository->findByStoreId($store->id);

        return response()->json([
            'data' => $categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'image_url' => $cat->image_url,
                'products_count' => $cat->products()->where('active', true)->count(),
            ]),
        ]);
    }

    public function products(Request $request, string $slug): JsonResponse
    {
        $store = $request->get('store');
        $products = $this->productService->getProductsByCategory(
            $store->id,
            $slug,
            $request->integer('per_page', 15)
        );

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }
}
