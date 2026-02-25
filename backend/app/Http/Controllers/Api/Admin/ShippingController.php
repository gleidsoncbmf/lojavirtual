<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingOption;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        private ShippingService $shippingService,
    ) {
    }

    /**
     * List all shipping options for the store.
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        return response()->json([
            'data' => $this->shippingService->getStoreOptions($store),
            'shipping_zip' => $store->shipping_zip,
        ]);
    }

    /**
     * Create a new fixed-rate shipping option.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'price' => 'required|numeric|min:0',
            'delivery_days' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        $store = $request->user()->store;
        $option = $this->shippingService->createOption($store, $validated);

        return response()->json([
            'message' => 'Opção de frete criada com sucesso.',
            'data' => $option,
        ], 201);
    }

    /**
     * Update a shipping option.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $store = $request->user()->store;
        $option = ShippingOption::where('store_id', $store->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'price' => 'sometimes|numeric|min:0',
            'delivery_days' => 'nullable|integer|min:0',
            'active' => 'sometimes|boolean',
        ]);

        $option = $this->shippingService->updateOption($option, $validated);

        return response()->json([
            'message' => 'Opção de frete atualizada.',
            'data' => $option,
        ]);
    }

    /**
     * Delete a shipping option.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $store = $request->user()->store;
        $option = ShippingOption::where('store_id', $store->id)->findOrFail($id);

        $this->shippingService->deleteOption($option);

        return response()->json([
            'message' => 'Opção de frete excluída.',
        ]);
    }

    /**
     * Update the store's origin ZIP code for shipping.
     */
    public function updateStoreZip(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_zip' => 'required|string|max:9',
        ]);

        $store = $request->user()->store;
        $store->update($validated);

        return response()->json([
            'message' => 'CEP de origem atualizado.',
            'shipping_zip' => $store->shipping_zip,
        ]);
    }
}
