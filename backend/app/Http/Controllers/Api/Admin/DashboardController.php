<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = $user->store;

        $stats = $this->orderService->getDashboardStats($store->id);

        $productsCount = $store->products()->count();
        $activeProducts = $store->products()->where('active', true)->count();
        $categoriesCount = $store->categories()->count();

        return response()->json([
            'data' => [
                'orders' => $stats,
                'products' => [
                    'total' => $productsCount,
                    'active' => $activeProducts,
                ],
                'categories_count' => $categoriesCount,
            ],
        ]);
    }
}
