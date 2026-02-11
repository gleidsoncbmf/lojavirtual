<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'folder' => 'nullable|string|in:products,categories,logos',
        ]);

        $user = $request->user();
        $storeId = $user->store_id;

        if (!$storeId) {
            return response()->json([
                'message' => 'Usuário não pertence a nenhuma loja.',
            ], 403);
        }

        $folder = $request->input('folder', 'products');
        $urls = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store("{$folder}/{$storeId}", 'public');
            $urls[] = asset("storage/{$path}");
        }

        return response()->json([
            'message' => 'Imagens enviadas com sucesso.',
            'urls' => $urls,
        ]);
    }
}
