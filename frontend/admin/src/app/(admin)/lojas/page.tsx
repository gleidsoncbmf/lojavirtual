'use client';

import { useEffect, useState, useCallback } from 'react';
import {
    getPlatformStores,
    updatePlatformStore,
    deletePlatformStore,
} from '@/lib/api';
import { useAuth } from '@/context/AuthContext';
import { formatDate } from '@/lib/utils';
import type { PlatformStore } from '@/types';
import {
    Building2,
    Plus,
    Package,
    ShoppingCart,
    Trash2,
    ExternalLink,
    Loader2,
    AlertTriangle,
} from 'lucide-react';
import CreateStoreModal from '@/components/stores/CreateStoreModal';

const statusConfig: Record<string, { label: string; className: string }> = {
    active: { label: 'Ativa', className: 'bg-emerald-500/20 text-emerald-400' },
    inactive: { label: 'Inativa', className: 'bg-gray-500/20 text-gray-400' },
    suspended: { label: 'Suspensa', className: 'bg-red-500/20 text-red-400' },
};

export default function LojasPage() {
    const { user } = useAuth();
    const [stores, setStores] = useState<PlatformStore[]>([]);
    const [loading, setLoading] = useState(true);
    const [modalOpen, setModalOpen] = useState(false);
    const [deleteConfirm, setDeleteConfirm] = useState<number | null>(null);
    const [actionLoading, setActionLoading] = useState<number | null>(null);

    const fetchStores = useCallback(async () => {
        setLoading(true);
        try {
            const res = await getPlatformStores();
            setStores(res.data);
        } catch {
            // ignore
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (user?.role === 'platform_admin') {
            fetchStores();
        }
    }, [user, fetchStores]);

    const handleToggleStatus = async (store: PlatformStore) => {
        setActionLoading(store.id);
        try {
            const newStatus = store.status === 'active' ? 'inactive' : 'active';
            await updatePlatformStore(store.id, { status: newStatus });
            await fetchStores();
        } catch {
            // ignore
        } finally {
            setActionLoading(null);
        }
    };

    const handleDelete = async (id: number) => {
        setActionLoading(id);
        try {
            await deletePlatformStore(id);
            setDeleteConfirm(null);
            await fetchStores();
        } catch {
            // ignore
        } finally {
            setActionLoading(null);
        }
    };

    if (user?.role !== 'platform_admin') {
        return (
            <div className="text-center py-20 text-gray-500">
                <AlertTriangle className="w-10 h-10 mx-auto mb-3 opacity-30" />
                <p>Acesso restrito ao administrador da plataforma.</p>
            </div>
        );
    }

    if (loading) {
        return (
            <div className="flex items-center justify-center py-32">
                <Loader2 className="w-8 h-8 text-green-600 dark:text-green-500 animate-spin" />
            </div>
        );
    }

    return (
        <div className="space-y-6 animate-in">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Lojas</h1>
                    <p className="text-gray-500 text-sm mt-1">
                        Gerencie todas as lojas da plataforma
                    </p>
                </div>
                <button
                    onClick={() => setModalOpen(true)}
                    className="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-green-600 text-white font-semibold text-sm hover:bg-green-500 transition-all duration-200 shadow-lg shadow-green-500/25"
                >
                    <Plus className="w-4 h-4" />
                    Nova Loja
                </button>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-5 transition-colors duration-300">
                    <div className="flex items-center justify-between mb-2">
                        <span className="text-gray-500 text-xs font-medium uppercase tracking-wide">Total</span>
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-green-600 to-green-400 flex items-center justify-center">
                            <Building2 className="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stores.length}</p>
                </div>
                <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-5 transition-colors duration-300">
                    <div className="flex items-center justify-between mb-2">
                        <span className="text-gray-500 text-xs font-medium uppercase tracking-wide">Ativas</span>
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-600 to-emerald-400 flex items-center justify-center">
                            <Building2 className="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-gray-900 dark:text-white">
                        {stores.filter((s) => s.status === 'active').length}
                    </p>
                </div>
                <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-5 transition-colors duration-300">
                    <div className="flex items-center justify-between mb-2">
                        <span className="text-gray-500 text-xs font-medium uppercase tracking-wide">Produtos Total</span>
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-600 to-teal-400 flex items-center justify-center">
                            <Package className="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-gray-900 dark:text-white">
                        {stores.reduce((acc, s) => acc + (s.products_count || 0), 0)}
                    </p>
                </div>
            </div>

            {/* Table */}
            <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl overflow-hidden transition-colors duration-300">
                {stores.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-200 dark:border-white/5 text-gray-500 text-xs uppercase">
                                    <th className="text-left px-5 py-3 font-medium">Loja</th>
                                    <th className="text-left px-5 py-3 font-medium">Dono</th>
                                    <th className="text-left px-5 py-3 font-medium">Slug / URL</th>
                                    <th className="text-center px-5 py-3 font-medium">Produtos</th>
                                    <th className="text-center px-5 py-3 font-medium">Pedidos</th>
                                    <th className="text-left px-5 py-3 font-medium">Status</th>
                                    <th className="text-left px-5 py-3 font-medium">Criada em</th>
                                    <th className="text-right px-5 py-3 font-medium">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                {stores.map((store) => {
                                    const status = statusConfig[store.status] || statusConfig.inactive;
                                    return (
                                        <tr
                                            key={store.id}
                                            className="border-b border-gray-100 dark:border-white/[0.03] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition"
                                        >
                                            <td className="px-5 py-3">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-8 h-8 rounded-lg bg-green-500/10 dark:bg-green-500/20 border border-gray-200 dark:border-white/5 flex items-center justify-center flex-shrink-0">
                                                        <Building2 className="w-4 h-4 text-green-600 dark:text-green-400" />
                                                    </div>
                                                    <span className="text-gray-900 dark:text-white font-medium">{store.name}</span>
                                                </div>
                                            </td>
                                            <td className="px-5 py-3">
                                                {store.owner ? (
                                                    <div>
                                                        <p className="text-gray-700 dark:text-gray-300 text-sm">{store.owner.name}</p>
                                                        <p className="text-gray-400 dark:text-gray-500 text-xs">{store.owner.email}</p>
                                                    </div>
                                                ) : (
                                                    <span className="text-gray-400 dark:text-gray-600 text-xs">Sem dono</span>
                                                )}
                                            </td>
                                            <td className="px-5 py-3">
                                                <div className="flex items-center gap-2">
                                                    <code className="text-green-600 dark:text-green-400 text-xs bg-green-500/10 px-2 py-1 rounded-lg">
                                                        {store.slug}
                                                    </code>
                                                    <a
                                                        href={`http://localhost:3000?store=${store.slug}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="text-gray-500 hover:text-green-600 dark:hover:text-green-400 transition"
                                                        title="Abrir loja"
                                                    >
                                                        <ExternalLink className="w-3.5 h-3.5" />
                                                    </a>
                                                </div>
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <div className="flex items-center justify-center gap-1.5 text-gray-700 dark:text-gray-300">
                                                    <Package className="w-3.5 h-3.5 text-gray-500" />
                                                    {store.products_count || 0}
                                                </div>
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <div className="flex items-center justify-center gap-1.5 text-gray-700 dark:text-gray-300">
                                                    <ShoppingCart className="w-3.5 h-3.5 text-gray-500" />
                                                    {store.orders_count || 0}
                                                </div>
                                            </td>
                                            <td className="px-5 py-3">
                                                <button
                                                    onClick={() => handleToggleStatus(store)}
                                                    disabled={actionLoading === store.id}
                                                    className="cursor-pointer"
                                                >
                                                    <span className={`px-2.5 py-1 rounded-lg text-xs font-medium ${status.className} hover:opacity-80 transition`}>
                                                        {actionLoading === store.id ? '...' : status.label}
                                                    </span>
                                                </button>
                                            </td>
                                            <td className="px-5 py-3 text-gray-500 text-xs">
                                                {formatDate(store.created_at)}
                                            </td>
                                            <td className="px-5 py-3 text-right">
                                                {deleteConfirm === store.id ? (
                                                    <div className="flex items-center justify-end gap-2">
                                                        <button
                                                            onClick={() => handleDelete(store.id)}
                                                            disabled={actionLoading === store.id}
                                                            className="text-xs px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition font-medium disabled:opacity-50"
                                                        >
                                                            {actionLoading === store.id ? (
                                                                <Loader2 className="w-3 h-3 animate-spin" />
                                                            ) : (
                                                                'Confirmar'
                                                            )}
                                                        </button>
                                                        <button
                                                            onClick={() => setDeleteConfirm(null)}
                                                            className="text-xs px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10 transition"
                                                        >
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                ) : (
                                                    <button
                                                        onClick={() => setDeleteConfirm(store.id)}
                                                        className="text-gray-500 hover:text-red-400 transition p-1.5 rounded-lg hover:bg-red-500/10"
                                                        title="Excluir loja"
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="text-center py-16 text-gray-500">
                        <Building2 className="w-12 h-12 mx-auto mb-4 opacity-20" />
                        <p className="mb-1">Nenhuma loja cadastrada</p>
                        <p className="text-xs text-gray-400 dark:text-gray-600">
                            Clique em &quot;Nova Loja&quot; para criar a primeira.
                        </p>
                    </div>
                )}
            </div>

            <CreateStoreModal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                onCreated={fetchStores}
            />
        </div>
    );
}
