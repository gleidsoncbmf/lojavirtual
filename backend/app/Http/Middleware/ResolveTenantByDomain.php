<?php

namespace App\Http\Middleware;

use App\Contracts\Repositories\StoreRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantByDomain
{
    public function __construct(
        private StoreRepositoryInterface $storeRepository,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Priority: X-Store-Id header > X-Store-Slug header > domain resolution
        $storeId = $request->header('X-Store-Id');
        $storeSlug = $request->header('X-Store-Slug');

        $store = null;

        if ($storeId) {
            $store = $this->storeRepository->findById((int) $storeId);
        } elseif ($storeSlug) {
            $store = $this->storeRepository->findBySlug($storeSlug);
        } else {
            $host = $request->getHost();
            $store = $this->storeRepository->findByDomain($host);
        }

        if (!$store || $store->status !== 'active') {
            return response()->json([
                'message' => 'Loja não encontrada ou inativa.',
            ], 404);
        }

        // Bind store to the request and container
        $request->merge(['store' => $store]);
        app()->instance('currentStore', $store);

        return $next($request);
    }
}
