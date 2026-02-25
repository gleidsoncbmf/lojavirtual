<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'compare_price' => $this->compare_price ? (float) $this->compare_price : null,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'active' => $this->active,
            'category_id' => $this->category_id,
            'weight' => $this->weight ? (float) $this->weight : null,
            'length' => $this->length ? (float) $this->length : null,
            'width' => $this->width ? (float) $this->width : null,
            'height' => $this->height ? (float) $this->height : null,
            'images' => $this->images ?? [],
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'variations' => $this->whenLoaded(
                'variations',
                fn() =>
                $this->variations->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'price' => $v->price ? (float) $v->price : null,
                    'stock' => $v->stock,
                    'sku' => $v->sku,
                    'image' => $v->image,
                    'weight' => $v->weight ? (float) $v->weight : null,
                    'length' => $v->length ? (float) $v->length : null,
                    'width' => $v->width ? (float) $v->width : null,
                    'height' => $v->height ? (float) $v->height : null,
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
