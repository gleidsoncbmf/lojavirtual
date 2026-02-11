<?php

namespace App\Http\Controllers\Api;

use App\DTOs\StoreConfigDTO;
use App\Http\Controllers\Controller;
use App\Models\StoreTheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $store = $request->get('store');
        $config = StoreConfigDTO::fromStore($store);

        return response()->json([
            'data' => $config->toArray(),
        ]);
    }
}
