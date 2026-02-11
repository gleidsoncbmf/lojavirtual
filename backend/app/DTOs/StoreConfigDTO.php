<?php

namespace App\DTOs;

class StoreConfigDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,
        public readonly ?string $email,
        public readonly ?string $whatsapp,
        public readonly ?string $logoUrl,
        public readonly ?string $primaryColor,
        public readonly ?string $secondaryColor,
        public readonly ?string $buttonColor,
        public readonly ?string $textColor,
        public readonly ?string $backgroundColor,
        public readonly ?string $seoTitle,
        public readonly ?string $seoDescription,
    ) {
    }

    public static function fromStore(\App\Models\Store $store): self
    {
        $theme = $store->theme;

        return new self(
            name: $store->name,
            slug: $store->slug,
            email: $store->email,
            whatsapp: $store->whatsapp,
            logoUrl: $store->logo_url,
            primaryColor: $theme?->primary_color ?? '#6366f1',
            secondaryColor: $theme?->secondary_color ?? '#8b5cf6',
            buttonColor: $theme?->button_color ?? '#6366f1',
            textColor: $theme?->text_color ?? '#111827',
            backgroundColor: $theme?->background_color ?? '#ffffff',
            seoTitle: $theme?->seo_title ?? $store->name,
            seoDescription: $theme?->seo_description,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'logo_url' => $this->logoUrl,
            'theme' => [
                'primary_color' => $this->primaryColor,
                'secondary_color' => $this->secondaryColor,
                'button_color' => $this->buttonColor,
                'text_color' => $this->textColor,
                'background_color' => $this->backgroundColor,
            ],
            'seo' => [
                'title' => $this->seoTitle,
                'description' => $this->seoDescription,
            ],
        ];
    }
}
