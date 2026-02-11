import axios from 'axios';
import type {
    AuthResponse,
    LoginCredentials,
    User,
    DashboardStats,
    Product,
    ProductFormData,
    Category,
    CategoryFormData,
    Order,
    StoreConfig,
    StoreTheme,
    PaymentConfig,
    PaginatedResponse,
    ApiResponse,
    PaymentStatus,
    DeliveryStatus,
    PlatformStore,
    CreateStoreFormData,
} from '@/types';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

const api = axios.create({
    baseURL: API_BASE,
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});

// Interceptor: attach Bearer token
api.interceptors.request.use((config) => {
    if (typeof window !== 'undefined') {
        const token = localStorage.getItem('admin_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
    }
    return config;
});

// Interceptor: handle 401 → redirect to login
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401 && typeof window !== 'undefined') {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

// ============================
// Auth
// ============================
export async function login(credentials: LoginCredentials): Promise<AuthResponse> {
    const { data } = await api.post<AuthResponse>('/auth/login', credentials);
    return data;
}

export async function logout(): Promise<void> {
    await api.post('/auth/logout');
}

export async function getMe(): Promise<User> {
    const { data } = await api.get<{ user: User }>('/auth/me');
    return data.user;
}

// ============================
// Dashboard
// ============================
export async function getDashboard(): Promise<DashboardStats> {
    const { data } = await api.get<ApiResponse<DashboardStats>>('/admin/dashboard');
    return data.data;
}

// ============================
// Products
// ============================
export async function getProducts(params?: {
    page?: number;
    per_page?: number;
    category_id?: number;
    search?: string;
    active?: boolean;
    sort_by?: string;
    sort_dir?: string;
}): Promise<PaginatedResponse<Product>> {
    const { data } = await api.get<PaginatedResponse<Product>>('/admin/products', { params });
    return data;
}

export async function getProduct(id: number): Promise<Product> {
    const { data } = await api.get<ApiResponse<Product>>(`/admin/products/${id}`);
    return data.data;
}

export async function createProduct(productData: ProductFormData): Promise<Product> {
    const { data } = await api.post<ApiResponse<Product>>('/admin/products', productData);
    return data.data;
}

export async function updateProduct(id: number, productData: Partial<ProductFormData>): Promise<Product> {
    const { data } = await api.put<ApiResponse<Product>>(`/admin/products/${id}`, productData);
    return data.data;
}

export async function deleteProduct(id: number): Promise<void> {
    await api.delete(`/admin/products/${id}`);
}

// ============================
// Categories
// ============================
export async function getCategories(): Promise<Category[]> {
    const { data } = await api.get<{ data: Category[] }>('/admin/categories');
    return data.data;
}

export async function createCategory(categoryData: CategoryFormData): Promise<Category> {
    const { data } = await api.post<ApiResponse<Category>>('/admin/categories', categoryData);
    return data.data;
}

export async function updateCategory(id: number, categoryData: Partial<CategoryFormData>): Promise<Category> {
    const { data } = await api.put<ApiResponse<Category>>(`/admin/categories/${id}`, categoryData);
    return data.data;
}

export async function deleteCategory(id: number): Promise<void> {
    await api.delete(`/admin/categories/${id}`);
}

// ============================
// Orders
// ============================
export async function getOrders(params?: {
    page?: number;
    per_page?: number;
    payment_status?: string;
    delivery_status?: string;
    search?: string;
}): Promise<PaginatedResponse<Order>> {
    const { data } = await api.get<PaginatedResponse<Order>>('/admin/orders', { params });
    return data;
}

export async function getOrder(id: number): Promise<Order> {
    const { data } = await api.get<ApiResponse<Order>>(`/admin/orders/${id}`);
    return data.data;
}

export async function updatePaymentStatus(id: number, status: PaymentStatus): Promise<Order> {
    const { data } = await api.patch<ApiResponse<Order>>(
        `/admin/orders/${id}/payment-status`,
        { payment_status: status }
    );
    return data.data;
}

export async function updateDeliveryStatus(id: number, status: DeliveryStatus): Promise<Order> {
    const { data } = await api.patch<ApiResponse<Order>>(
        `/admin/orders/${id}/delivery-status`,
        { delivery_status: status }
    );
    return data.data;
}

export async function markOrderAsPaid(id: number): Promise<Order> {
    const { data } = await api.patch<ApiResponse<Order>>(`/admin/orders/${id}/mark-paid`);
    return data.data;
}

// ============================
// Settings
// ============================
export async function getSettings(): Promise<StoreConfig> {
    const { data } = await api.get<ApiResponse<StoreConfig>>('/admin/settings');
    return data.data;
}

export async function updateStoreInfo(info: { name?: string; email?: string; logo_url?: string }): Promise<StoreConfig> {
    const { data } = await api.put<ApiResponse<StoreConfig>>('/admin/settings/store', info);
    return data.data;
}

export async function updateTheme(theme: Partial<StoreTheme>): Promise<StoreTheme> {
    const { data } = await api.put<ApiResponse<StoreTheme>>('/admin/settings/theme', theme);
    return data.data;
}

export async function updatePaymentConfig(config: PaymentConfig): Promise<void> {
    await api.put('/admin/settings/payment', config);
}

export async function updateWhatsApp(whatsapp: string): Promise<void> {
    await api.put('/admin/settings/whatsapp', { whatsapp });
}

export async function updateDomain(domain: string): Promise<void> {
    await api.put('/admin/settings/domain', { domain });
}

// ============================
// Platform Admin — Stores
// ============================
export async function getPlatformStores(page = 1): Promise<PaginatedResponse<PlatformStore>> {
    const { data } = await api.get<PaginatedResponse<PlatformStore>>('/platform/stores', {
        params: { page },
    });
    return data;
}

export async function createPlatformStore(storeData: CreateStoreFormData): Promise<PlatformStore> {
    const { data } = await api.post<{ data: PlatformStore }>('/platform/stores', storeData);
    return data.data;
}

export async function updatePlatformStore(
    id: number,
    updates: { name?: string; status?: string }
): Promise<PlatformStore> {
    const { data } = await api.patch<{ data: PlatformStore }>(`/platform/stores/${id}`, updates);
    return data.data;
}

export async function deletePlatformStore(id: number): Promise<void> {
    await api.delete(`/platform/stores/${id}`);
}

export default api;
