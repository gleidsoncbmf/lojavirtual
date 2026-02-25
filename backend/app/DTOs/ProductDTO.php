<?php

namespace App\DTOs;

class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly float $price,
        public readonly ?float $comparePrice,
        public readonly ?string $sku,
        public readonly int $stock,
        public readonly bool $active,
        public readonly ?int $categoryId,
        public readonly ?array $images,
        public readonly ?float $weight = null,
        public readonly ?float $length = null,
        public readonly ?float $width = null,
        public readonly ?float $height = null,
        public readonly ?array $variations = [],
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']),
            description: $data['description'] ?? null,
            price: (float) $data['price'],
            comparePrice: isset($data['compare_price']) ? (float) $data['compare_price'] : null,
            sku: $data['sku'] ?? null,
            stock: (int) ($data['stock'] ?? 0),
            active: (bool) ($data['active'] ?? true),
            categoryId: $data['category_id'] ?? null,
            images: $data['images'] ?? null,
            weight: isset($data['weight']) ? (float) $data['weight'] : null,
            length: isset($data['length']) ? (float) $data['length'] : null,
            width: isset($data['width']) ? (float) $data['width'] : null,
            height: isset($data['height']) ? (float) $data['height'] : null,
            variations: $data['variations'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'compare_price' => $this->comparePrice,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'active' => $this->active,
            'category_id' => $this->categoryId,
            'images' => $this->images,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'variations' => $this->variations,
        ];
    }
}

