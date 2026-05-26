<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductPublicController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category'])
            ->where('slug', $slug)
            ->where('visible', true)
            ->where('archived', false)
            ->first();

        if (! $product) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $apiBase  = rtrim(config('app.url'), '/');
        $domain   = rtrim(config('configurazione.domain', $apiBase), '/');
        $canonical = $domain . '/prodotto/' . $product->slug;

        $imageUrl = null;
        if ($product->image) {
            if (str_starts_with((string) $product->image, 'http')) {
                $imageUrl = $product->image;
            } else {
                $imageUrl = $apiBase . '/public/storage/' . ltrim((string) $product->image, '/');
            }
        }

        return response()->json([
            'id'            => $product->id,
            'slug'          => $product->slug,
            'name'          => $product->name,
            'description'   => $product->description ?? '',
            'price'         => round((float) $product->price, 2),
            'image_url'     => $imageUrl,
            'availability'  => 'InStock',
            'category'      => optional($product->category)->name ?? '',
            'canonical_url' => $canonical,
        ]);
    }
}
