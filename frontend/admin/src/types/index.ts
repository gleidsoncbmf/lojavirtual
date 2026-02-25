// ============================
// API Response Generics
// ============================
export interface ApiResponse<T> {
    data: T;
    message?: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
}

// ============================
// Auth
// ============================
export interface User {
    id: number;
    name: string;
    email: string;
    role: 'platform_admin' | 'store_owner' | 'customer';
    store_id: number | null;
    store?: StoreConfig;
}

export interface LoginCredentials {
    email: string;
    password: string;
}

export interface AuthResponse {
    user: User;
    token: string;
}

// ============================
// Store & Theme
// ============================
export interface StoreTheme {
    id: number;
    primary_color: string;
    secondary_color: string;
    button_color: string;
    text_color: string;
    background_color: string;
    seo_title: string | null;
    seo_description: string | null;
    custom_css?: string | null;
}

export interface StoreDomain {
    id: number;
    domain: string;
    is_primary: boolean;
    verified: boolean;
}

export interface StoreConfig {
    id: number;
    name: string;
    slug: string;
    email: string | null;
    whatsapp: string | null;
    logo_url: string | null;
    banner_url?: string | null;
    banner_position?: string;
    status: string;
    theme: StoreTheme | null;
    domains?: StoreDomain[];
    payment_config?: PaymentConfig;
}

// ============================
// Dashboard
// ============================
export interface DashboardStats {
    orders: {
        total: number;
        pending: number;
        paid: number;
        cancelled: number;
        total_revenue: number;
        recent: OrderSummary[];
    };
    products: {
        total: number;
        active: number;
    };
    categories_count: number;
}

export interface OrderSummary {
    id: number;
    order_number: string;
    customer_name: string;
    total: number;
    payment_status: PaymentStatus;
    created_at: string;
}

// ============================
// Categories
// ============================
export interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image_url: string | null;
    sort_order: number;
    active: boolean;
    products_count?: number;
}

export interface CategoryFormData {
    name: string;
    slug?: string;
    description?: string;
    image_url?: string;
    sort_order?: number;
    active?: boolean;
}

// ============================
// Products
// ============================


export interface Product {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: number;
    compare_price: number | null;
    sku: string;
    stock: number;
    active: boolean;
    images: string[];
    weight?: number | null;
    length?: number | null;
    width?: number | null;
    height?: number | null;
    category_id: number | null;
    category?: Category | null;
    variations?: ProductVariation[];
    created_at?: string;
}

export interface ProductVariation {
    id?: number;
    name: string;
    price: number | null;
    stock: number;
    sku: string;
    image: string | null;
    weight?: number | null;
    length?: number | null;
    width?: number | null;
    height?: number | null;
}

export interface ProductFormData {
    name: string;
    slug?: string;
    description: string;
    price: number;
    compare_price: number | null;
    sku: string;
    stock: number;
    active: boolean;
    category_id: number | null;
    images: string[];
    weight?: number | null;
    length?: number | null;
    width?: number | null;
    height?: number | null;
    variations?: ProductVariation[];
}

// ============================
// Shipping
// ============================
export interface ShippingOption {
    id: number;
    name: string;
    city: string | null;
    state: string | null;
    price: number;
    delivery_days: number | null;
    active: boolean;
}

// ============================
// Orders
// ============================
export type PaymentStatus = 'pending' | 'awaiting_payment' | 'paid' | 'cancelled';
export type DeliveryStatus = 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';

export interface OrderItem {
    id: number;
    product_name: string;
    variation_name: string | null;
    quantity: number;
    unit_price: number;
    total: number;
}

export interface Order {
    id: number;
    order_number: string;
    customer_name: string;
    customer_email: string | null;
    customer_phone: string | null;
    subtotal: number;
    shipping_cost: number;
    total: number;
    payment_status: PaymentStatus;
    delivery_status: DeliveryStatus;
    payment_method: string;
    notes: string | null;
    items: OrderItem[];
    created_at: string;
    updated_at: string;
}

// ============================
// Payment Config
// ============================
export interface PaymentConfig {
    stripe?: {
        enabled: boolean;
        public_key?: string;
        secret_key?: string;
        webhook_secret?: string;
    };
    mercadopago?: {
        enabled: boolean;
        access_token?: string;
        public_key?: string;
    };
}

// ============================
// Platform Admin
// ============================
export interface PlatformStore {
    id: number;
    name: string;
    slug: string;
    email: string | null;
    status: 'active' | 'inactive' | 'suspended';
    theme: StoreTheme | null;
    owner: {
        id: number;
        name: string;
        email: string;
    } | null;
    products_count: number;
    orders_count: number;
    created_at: string;
}

export interface CreateStoreFormData {
    name: string;
    slug: string;
    email: string;
    owner_name: string;
    owner_email: string;
    owner_password: string;
}
