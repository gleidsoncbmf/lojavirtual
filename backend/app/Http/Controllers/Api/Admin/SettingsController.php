<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\StoreDomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $store = $request->user()->store->load(['theme', 'domains']);

        return response()->json([
            'data' => new StoreResource($store),
        ]);
    }

    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'primary_color' => 'sometimes|string|max:7',
            'secondary_color' => 'sometimes|string|max:7',
            'button_color' => 'sometimes|string|max:7',
            'text_color' => 'sometimes|string|max:7',
            'background_color' => 'sometimes|string|max:7',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ]);

        $store = $request->user()->store;
        $theme = $store->theme ?? $store->theme()->create([]);
        $theme->update($validated);

        return response()->json([
            'message' => 'Tema atualizado com sucesso.',
            'data' => $theme,
        ]);
    }

    public function updatePaymentConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stripe' => 'nullable|array',
            'stripe.enabled' => 'boolean',
            'stripe.public_key' => 'nullable|string',
            'stripe.secret_key' => 'nullable|string',
            'stripe.webhook_secret' => 'nullable|string',
            'mercadopago' => 'nullable|array',
            'mercadopago.enabled' => 'boolean',
            'mercadopago.access_token' => 'nullable|string',
            'mercadopago.public_key' => 'nullable|string',
        ]);

        $store = $request->user()->store;
        $store->update(['payment_config' => $validated]);

        return response()->json([
            'message' => 'Configuração de pagamento atualizada.',
        ]);
    }

    public function updateWhatsApp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'whatsapp' => 'required|string|max:20',
        ]);

        $store = $request->user()->store;
        $store->update($validated);

        return response()->json([
            'message' => 'WhatsApp atualizado com sucesso.',
        ]);
    }

    public function updateDomain(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $store = $request->user()->store;

        // Create or update primary domain
        StoreDomain::updateOrCreate(
            ['store_id' => $store->id, 'is_primary' => true],
            ['domain' => $validated['domain'], 'verified' => false]
        );

        return response()->json([
            'message' => 'Domínio configurado. Aguardando verificação.',
        ]);
    }

    public function updateStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'logo_url' => 'nullable|string',
        ]);

        $store = $request->user()->store;
        $store->update($validated);

        return response()->json([
            'message' => 'Dados da loja atualizados.',
            'data' => new StoreResource($store->fresh(['theme', 'domains'])),
        ]);
    }
}
