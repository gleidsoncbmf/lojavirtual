<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'logo_url' => $this->logo_url,
            'banner_url' => $this->banner_url,
            'banner_position' => $this->banner_position,
            'status' => $this->status,
            'theme' => $this->whenLoaded('theme', fn() => $this->theme ? [
                'primary_color' => $this->theme->primary_color,
                'secondary_color' => $this->theme->secondary_color,
                'button_color' => $this->theme->button_color,
                'text_color' => $this->theme->text_color,
                'background_color' => $this->theme->background_color,
                'seo_title' => $this->theme->seo_title,
                'seo_description' => $this->theme->seo_description,
            ] : null),
            'domains' => $this->whenLoaded(
                'domains',
                fn() =>
                $this->domains->map(fn($d) => [
                    'domain' => $d->domain,
                    'is_primary' => $d->is_primary,
                    'verified' => $d->verified,
                ])
            ),
            'owner' => $this->whenLoaded('owner', fn() => $this->owner ? [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
            ] : null),
            'products_count' => $this->when($this->products_count !== null, $this->products_count),
            'orders_count' => $this->when($this->orders_count !== null, $this->orders_count),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
