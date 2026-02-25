'use client';

import { useEffect, useState, useCallback } from 'react';
import { getProducts, deleteProduct } from '@/lib/api';
import { formatCurrency } from '@/lib/utils';
import type { Product, PaginatedResponse } from '@/types';
import { Package, Plus, Search, Trash2, Pencil, Loader2, AlertTriangle } from 'lucide-react';
import Link from 'next/link';

export default function ProductsPage() {
    const [data, setData] = useState<PaginatedResponse<Product> | null>(null);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [page, setPage] = useState(1);
    const [deleting, setDeleting] = useState<number | null>(null);

    const fetchProducts = useCallback(async () => {
        setLoading(true);
        try {
            const result = await getProducts({ page, per_page: 15, search: search || undefined });
            setData(result);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    }, [page, search]);

    useEffect(() => {
        fetchProducts();
    }, [fetchProducts]);

    const handleDelete = async (id: number) => {
        if (!confirm('Tem certeza que deseja excluir este produto?')) return;
        setDeleting(id);
        try {
            await deleteProduct(id);
            fetchProducts();
        } catch (err) {
            console.error(err);
        } finally {
            setDeleting(null);
        }
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        setPage(1);
        fetchProducts();
    };

    return (
        <div className="space-y-6 animate-in">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Produtos</h1>
                    <p className="text-gray-500 text-sm mt-1">Gerencie o catálogo da sua loja</p>
                </div>
                <Link
                    href="/produtos/novo"
                    className="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-500 transition-all shadow-lg shadow-green-500/25"
                >
                    <Plus className="w-4 h-4" />
                    Novo Produto
                </Link>
            </div>

            {/* Search */}
            <form onSubmit={handleSearch} className="flex gap-3">
                <div className="relative flex-1 max-w-md">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Buscar produtos..."
                        className="pl-10"
                    />
                </div>
            </form>

            {/* Table */}
            <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl overflow-hidden transition-colors duration-300">
                {loading ? (
                    <div className="flex items-center justify-center py-20">
                        <Loader2 className="w-6 h-6 text-green-600 dark:text-green-500 animate-spin" />
                    </div>
                ) : !data || data.data.length === 0 ? (
                    <div className="text-center py-20 text-gray-500">
                        <Package className="w-10 h-10 mx-auto mb-3 opacity-30" />
                        <p>Nenhum produto encontrado.</p>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-gray-200 dark:border-white/5 text-gray-500 text-xs uppercase">
                                        <th className="text-left px-5 py-3 font-medium">Produto</th>
                                        <th className="text-left px-5 py-3 font-medium">Preço</th>
                                        <th className="text-left px-5 py-3 font-medium">Estoque</th>
                                        <th className="text-left px-5 py-3 font-medium">Categoria</th>
                                        <th className="text-left px-5 py-3 font-medium">Status</th>
                                        <th className="text-right px-5 py-3 font-medium">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.data.map((product) => (
                                        <tr key={product.id} className="border-b border-gray-100 dark:border-white/[0.03] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                                            <td className="px-5 py-3">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-10 h-10 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center flex-shrink-0">
                                                        {product.images?.[0] ? (
                                                            <img src={product.images[0]} alt="" className="w-10 h-10 rounded-lg object-cover" />
                                                        ) : (
                                                            <Package className="w-5 h-5 text-gray-400 dark:text-gray-600" />
                                                        )}
                                                    </div>
                                                    <div>
                                                        <p className="text-gray-900 dark:text-white font-medium">{product.name}</p>
                                                        <p className="text-gray-400 dark:text-gray-600 text-xs">SKU: {product.sku || '—'}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-5 py-3 text-gray-900 dark:text-white font-medium">
                                                {formatCurrency(product.price)}
                                                {product.compare_price && (
                                                    <span className="block text-gray-400 dark:text-gray-600 text-xs line-through">
                                                        {formatCurrency(product.compare_price)}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-5 py-3">
                                                <span className={`font-medium ${product.stock <= 0 ? 'text-red-400' : product.stock <= 5 ? 'text-amber-400' : 'text-gray-700 dark:text-gray-300'}`}>
                                                    {product.stock}
                                                    {product.stock <= 5 && product.stock > 0 && (
                                                        <AlertTriangle className="inline w-3 h-3 ml-1" />
                                                    )}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3 text-gray-600 dark:text-gray-400">{product.category?.name || '—'}</td>
                                            <td className="px-5 py-3">
                                                <span className={`px-2.5 py-1 rounded-lg text-xs font-medium ${product.active ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-gray-500/20 text-gray-500 dark:text-gray-400'}`}>
                                                    {product.active ? 'Ativo' : 'Inativo'}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        href={`/produtos/${product.id}`}
                                                        className="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-green-600 dark:hover:text-green-400 transition"
                                                    >
                                                        <Pencil className="w-4 h-4" />
                                                    </Link>
                                                    <button
                                                        onClick={() => handleDelete(product.id)}
                                                        disabled={deleting === product.id}
                                                        className="p-2 rounded-lg text-gray-400 hover:bg-red-500/10 hover:text-red-400 transition disabled:opacity-50"
                                                    >
                                                        {deleting === product.id ? (
                                                            <Loader2 className="w-4 h-4 animate-spin" />
                                                        ) : (
                                                            <Trash2 className="w-4 h-4" />
                                                        )}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {data.meta.last_page > 1 && (
                            <div className="flex items-center justify-between px-5 py-4 border-t border-gray-200 dark:border-white/5">
                                <span className="text-gray-500 text-sm">
                                    {data.meta.total} produto(s) • Página {data.meta.current_page} de {data.meta.last_page}
                                </span>
                                <div className="flex gap-2">
                                    <button
                                        onClick={() => setPage(page - 1)}
                                        disabled={page <= 1}
                                        className="px-3 py-1.5 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 disabled:opacity-30 transition"
                                    >
                                        Anterior
                                    </button>
                                    <button
                                        onClick={() => setPage(page + 1)}
                                        disabled={page >= data.meta.last_page}
                                        className="px-3 py-1.5 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 disabled:opacity-30 transition"
                                    >
                                        Próxima
                                    </button>
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
}
