'use client';

import { useEffect, useState, use } from 'react';
import { useRouter } from 'next/navigation';
import { getProduct, updateProduct, getCategories } from '@/lib/api';
import type { Category, ProductFormData } from '@/types';
import { ArrowLeft, Loader2, Save } from 'lucide-react';
import Link from 'next/link';

export default function EditProductPage({ params }: { params: Promise<{ id: string }> }) {
    const { id } = use(params);
    const router = useRouter();
    const [categories, setCategories] = useState<Category[]>([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');
    const [form, setForm] = useState<ProductFormData>({
        name: '',
        description: '',
        price: 0,
        compare_price: null,
        sku: '',
        stock: 0,
        active: true,
        category_id: null,
        images: [],
    });

    useEffect(() => {
        Promise.all([getProduct(Number(id)), getCategories()])
            .then(([product, cats]) => {
                setForm({
                    name: product.name,
                    slug: product.slug,
                    description: product.description || '',
                    price: product.price,
                    compare_price: product.compare_price,
                    sku: product.sku || '',
                    stock: product.stock,
                    active: product.active,
                    category_id: product.category_id,
                    images: product.images || [],
                });
                setCategories(cats);
            })
            .catch(console.error)
            .finally(() => setLoading(false));
    }, [id]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value, type } = e.target;
        setForm((prev) => ({
            ...prev,
            [name]: type === 'number' ? Number(value) : value,
        }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setSaving(true);
        try {
            await updateProduct(Number(id), form);
            router.push('/produtos');
        } catch (err: unknown) {
            const message = (err as { response?: { data?: { message?: string } } })?.response?.data?.message;
            setError(message || 'Erro ao atualizar produto.');
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center py-32">
                <Loader2 className="w-8 h-8 text-indigo-500 animate-spin" />
            </div>
        );
    }

    return (
        <div className="space-y-6 animate-in max-w-3xl">
            <div className="flex items-center gap-4">
                <Link href="/produtos" className="p-2 rounded-lg hover:bg-white/5 text-gray-400 transition">
                    <ArrowLeft className="w-5 h-5" />
                </Link>
                <div>
                    <h1 className="text-2xl font-bold text-white">Editar Produto</h1>
                    <p className="text-gray-500 text-sm mt-1">Atualize as informações do produto</p>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="bg-[#0f111a] border border-white/5 rounded-2xl p-6 space-y-5">
                {error && (
                    <div className="bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-xl px-4 py-3">
                        {error}
                    </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div className="md:col-span-2">
                        <label htmlFor="name">Nome do Produto *</label>
                        <input id="name" name="name" type="text" value={form.name} onChange={handleChange} required />
                    </div>

                    <div>
                        <label htmlFor="price">Preço *</label>
                        <input id="price" name="price" type="number" step="0.01" min="0" value={form.price} onChange={handleChange} required />
                    </div>

                    <div>
                        <label htmlFor="compare_price">Preço Comparativo</label>
                        <input id="compare_price" name="compare_price" type="number" step="0.01" min="0" value={form.compare_price || ''} onChange={handleChange} />
                    </div>

                    <div>
                        <label htmlFor="sku">SKU</label>
                        <input id="sku" name="sku" type="text" value={form.sku || ''} onChange={handleChange} />
                    </div>

                    <div>
                        <label htmlFor="stock">Estoque</label>
                        <input id="stock" name="stock" type="number" min="0" value={form.stock || 0} onChange={handleChange} />
                    </div>

                    <div>
                        <label htmlFor="category_id">Categoria</label>
                        <select id="category_id" name="category_id" value={form.category_id || ''} onChange={handleChange}>
                            <option value="">Sem categoria</option>
                            {categories.map((cat) => (
                                <option key={cat.id} value={cat.id}>{cat.name}</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label htmlFor="active">Status</label>
                        <select id="active" name="active" value={form.active ? '1' : '0'} onChange={(e) => setForm((prev) => ({ ...prev, active: e.target.value === '1' }))}>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>

                    <div className="md:col-span-2">
                        <label htmlFor="description">Descrição</label>
                        <textarea id="description" name="description" rows={4} value={form.description || ''} onChange={handleChange} />
                    </div>
                </div>

                <div className="flex justify-end pt-2">
                    <button
                        type="submit"
                        disabled={saving}
                        className="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold hover:from-indigo-500 hover:to-purple-500 transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50"
                    >
                        {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                        {saving ? 'Salvando...' : 'Salvar Alterações'}
                    </button>
                </div>
            </form>
        </div>
    );
}
