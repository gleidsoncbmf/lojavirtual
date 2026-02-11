<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreDomain;
use App\Models\StoreTheme;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // Create Demo Store
        // =============================================
        $store = Store::create([
            'name' => 'Moda Style',
            'slug' => 'moda-style',
            'email' => 'contato@modastyle.com',
            'whatsapp' => '5511999999999',
            'status' => 'active',
            'payment_config' => [
                'stripe' => [
                    'enabled' => false,
                    'public_key' => '',
                    'secret_key' => '',
                ],
                'mercadopago' => [
                    'enabled' => false,
                    'access_token' => '',
                    'public_key' => '',
                ],
            ],
        ]);

        // Store Theme
        StoreTheme::create([
            'store_id' => $store->id,
            'primary_color' => '#6366f1',
            'secondary_color' => '#8b5cf6',
            'button_color' => '#6366f1',
            'text_color' => '#111827',
            'background_color' => '#ffffff',
            'seo_title' => 'Moda Style - Sua loja de moda online',
            'seo_description' => 'Encontre as melhores peças de moda com os melhores preços. Entrega rápida e segura.',
        ]);

        // Store Domain
        StoreDomain::create([
            'store_id' => $store->id,
            'domain' => 'moda-style.localhost',
            'is_primary' => true,
            'verified' => true,
        ]);

        // =============================================
        // Create Store Owner
        // =============================================
        $owner = User::create([
            'name' => 'João Silva',
            'email' => 'joao@modastyle.com',
            'password' => 'password',
            'store_id' => $store->id,
            'role' => 'store_owner',
        ]);

        // =============================================
        // Create Platform Admin
        // =============================================
        User::create([
            'name' => 'Admin Plataforma',
            'email' => 'admin@plataforma.com',
            'password' => 'password',
            'role' => 'platform_admin',
        ]);

        // =============================================
        // Create Categories
        // =============================================
        $camisetas = Category::create([
            'store_id' => $store->id,
            'name' => 'Camisetas',
            'slug' => 'camisetas',
            'description' => 'Camisetas masculinas e femininas',
            'sort_order' => 1,
            'active' => true,
        ]);

        $calcas = Category::create([
            'store_id' => $store->id,
            'name' => 'Calças',
            'slug' => 'calcas',
            'description' => 'Calças jeans e sociais',
            'sort_order' => 2,
            'active' => true,
        ]);

        $acessorios = Category::create([
            'store_id' => $store->id,
            'name' => 'Acessórios',
            'slug' => 'acessorios',
            'description' => 'Relógios, cintos e mais',
            'sort_order' => 3,
            'active' => true,
        ]);

        $tenis = Category::create([
            'store_id' => $store->id,
            'name' => 'Tênis',
            'slug' => 'tenis',
            'description' => 'Tênis esportivos e casuais',
            'sort_order' => 4,
            'active' => true,
        ]);

        // =============================================
        // Create Products
        // =============================================
        $products = [
            [
                'store_id' => $store->id,
                'category_id' => $camisetas->id,
                'name' => 'Camiseta Básica Preta',
                'slug' => 'camiseta-basica-preta',
                'description' => 'Camiseta básica de algodão, confortável e versátil para o dia a dia.',
                'price' => 49.90,
                'compare_price' => 69.90,
                'sku' => 'CAM-001',
                'stock' => 50,
                'active' => true,
                'images' => ['https://placehold.co/600x800/111827/ffffff?text=Camiseta+Preta'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $camisetas->id,
                'name' => 'Camiseta Estampada Urban',
                'slug' => 'camiseta-estampada-urban',
                'description' => 'Camiseta com estampa exclusiva, estilo urbano moderno.',
                'price' => 79.90,
                'compare_price' => null,
                'sku' => 'CAM-002',
                'stock' => 30,
                'active' => true,
                'images' => ['https://placehold.co/600x800/4f46e5/ffffff?text=Camiseta+Urban'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $camisetas->id,
                'name' => 'Polo Premium',
                'slug' => 'polo-premium',
                'description' => 'Camisa polo premium, tecido de alta qualidade com bordado.',
                'price' => 129.90,
                'compare_price' => 159.90,
                'sku' => 'CAM-003',
                'stock' => 20,
                'active' => true,
                'images' => ['https://placehold.co/600x800/059669/ffffff?text=Polo+Premium'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $calcas->id,
                'name' => 'Calça Jeans Slim',
                'slug' => 'calca-jeans-slim',
                'description' => 'Calça jeans slim fit, lavagem moderna e confortável.',
                'price' => 159.90,
                'compare_price' => 199.90,
                'sku' => 'CAL-001',
                'stock' => 25,
                'active' => true,
                'images' => ['https://placehold.co/600x800/1e40af/ffffff?text=Jeans+Slim'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $calcas->id,
                'name' => 'Calça Cargo Moderna',
                'slug' => 'calca-cargo-moderna',
                'description' => 'Calça cargo estilo moderno com bolsos laterais funcionais.',
                'price' => 189.90,
                'compare_price' => null,
                'sku' => 'CAL-002',
                'stock' => 15,
                'active' => true,
                'images' => ['https://placehold.co/600x800/78716c/ffffff?text=Cargo+Moderna'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $acessorios->id,
                'name' => 'Relógio Digital Esportivo',
                'slug' => 'relogio-digital-esportivo',
                'description' => 'Relógio digital com cronômetro, alarme e resistência à água.',
                'price' => 249.90,
                'compare_price' => 349.90,
                'sku' => 'ACE-001',
                'stock' => 10,
                'active' => true,
                'images' => ['https://placehold.co/600x800/0f172a/ffffff?text=Relogio+Digital'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $acessorios->id,
                'name' => 'Cinto de Couro Legítimo',
                'slug' => 'cinto-couro-legitimo',
                'description' => 'Cinto de couro legítimo, fivela de metal polido.',
                'price' => 89.90,
                'compare_price' => null,
                'sku' => 'ACE-002',
                'stock' => 40,
                'active' => true,
                'images' => ['https://placehold.co/600x800/451a03/ffffff?text=Cinto+Couro'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $tenis->id,
                'name' => 'Tênis Runner Pro',
                'slug' => 'tenis-runner-pro',
                'description' => 'Tênis para corrida com amortecimento avançado e design aerodinâmico.',
                'price' => 299.90,
                'compare_price' => 399.90,
                'sku' => 'TEN-001',
                'stock' => 18,
                'active' => true,
                'images' => ['https://placehold.co/600x800/dc2626/ffffff?text=Runner+Pro'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $tenis->id,
                'name' => 'Tênis Casual Street',
                'slug' => 'tenis-casual-street',
                'description' => 'Tênis casual inspirado no estilo urbano, couro sintético premium.',
                'price' => 219.90,
                'compare_price' => null,
                'sku' => 'TEN-002',
                'stock' => 22,
                'active' => true,
                'images' => ['https://placehold.co/600x800/f59e0b/000000?text=Casual+Street'],
            ],
            [
                'store_id' => $store->id,
                'category_id' => $camisetas->id,
                'name' => 'Camiseta Oversized',
                'slug' => 'camiseta-oversized',
                'description' => 'Camiseta oversized, tendência da moda streetwear.',
                'price' => 89.90,
                'compare_price' => 109.90,
                'sku' => 'CAM-004',
                'stock' => 35,
                'active' => true,
                'images' => ['https://placehold.co/600x800/7c3aed/ffffff?text=Oversized'],
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Add variations to some products
            if (in_array($product->slug, ['camiseta-basica-preta', 'camiseta-estampada-urban', 'camiseta-oversized', 'polo-premium'])) {
                $product->variations()->createMany([
                    ['name' => 'P', 'price' => null, 'stock' => 10, 'sku' => $product->sku . '-P'],
                    ['name' => 'M', 'price' => null, 'stock' => 15, 'sku' => $product->sku . '-M'],
                    ['name' => 'G', 'price' => null, 'stock' => 10, 'sku' => $product->sku . '-G'],
                    ['name' => 'GG', 'price' => null, 'stock' => 5, 'sku' => $product->sku . '-GG'],
                ]);
            }

            if (in_array($product->slug, ['tenis-runner-pro', 'tenis-casual-street'])) {
                $product->variations()->createMany([
                    ['name' => '38', 'price' => null, 'stock' => 5, 'sku' => $product->sku . '-38'],
                    ['name' => '39', 'price' => null, 'stock' => 5, 'sku' => $product->sku . '-39'],
                    ['name' => '40', 'price' => null, 'stock' => 4, 'sku' => $product->sku . '-40'],
                    ['name' => '41', 'price' => null, 'stock' => 3, 'sku' => $product->sku . '-41'],
                    ['name' => '42', 'price' => null, 'stock' => 3, 'sku' => $product->sku . '-42'],
                ]);
            }
        }

        // =============================================
        // Create Second Demo Store
        // =============================================
        $store2 = Store::create([
            'name' => 'Tech Shop',
            'slug' => 'tech-shop',
            'email' => 'contato@techshop.com',
            'whatsapp' => '5511888888888',
            'status' => 'active',
        ]);

        StoreTheme::create([
            'store_id' => $store2->id,
            'primary_color' => '#0ea5e9',
            'secondary_color' => '#06b6d4',
            'button_color' => '#0ea5e9',
            'text_color' => '#0f172a',
            'background_color' => '#f8fafc',
            'seo_title' => 'Tech Shop - Gadgets e Tecnologia',
            'seo_description' => 'Os melhores gadgets e acessórios de tecnologia.',
        ]);

        User::create([
            'name' => 'Maria Tech',
            'email' => 'maria@techshop.com',
            'password' => 'password',
            'store_id' => $store2->id,
            'role' => 'store_owner',
        ]);

        $gadgets = Category::create([
            'store_id' => $store2->id,
            'name' => 'Gadgets',
            'slug' => 'gadgets',
            'description' => 'Gadgets inovadores',
            'sort_order' => 1,
            'active' => true,
        ]);

        Product::create([
            'store_id' => $store2->id,
            'category_id' => $gadgets->id,
            'name' => 'Fone Bluetooth Premium',
            'slug' => 'fone-bluetooth-premium',
            'description' => 'Fone de ouvido bluetooth com cancelamento de ruído ativo.',
            'price' => 349.90,
            'compare_price' => 499.90,
            'sku' => 'TECH-001',
            'stock' => 30,
            'active' => true,
            'images' => ['https://placehold.co/600x800/0ea5e9/ffffff?text=Fone+BT'],
        ]);
    }
}
