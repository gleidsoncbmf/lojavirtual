<?php

namespace App\Http\Controllers\Api\Admin;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $categories = $this->categoryRepository->findByStoreId($user->store_id);

        return response()->json([
            'data' => $categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'image_url' => $cat->image_url,
                'sort_order' => $cat->sort_order,
                'active' => $cat->active,
                'products_count' => $cat->products()->count(),
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'active' => 'boolean',
        ]);

        $user = $request->user();
        $validated['store_id'] = $user->store_id;
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $category = $this->categoryRepository->create($validated);

        return response()->json([
            'message' => 'Categoria criada com sucesso.',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category || $category->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Categoria não encontrada.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'active' => 'boolean',
        ]);

        $category = $this->categoryRepository->update($category, $validated);

        return response()->json([
            'message' => 'Categoria atualizada com sucesso.',
            'data' => $category,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category || $category->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Categoria não encontrada.'], 404);
        }

        $this->categoryRepository->delete($category);

        return response()->json(['message' => 'Categoria excluída com sucesso.']);
    }
}
