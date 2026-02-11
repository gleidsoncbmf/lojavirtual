'use client';

import { useEffect, useState } from 'react';
import { getDashboard } from '@/lib/api';
import { formatCurrency, formatDate, paymentStatusLabels, paymentStatusColors } from '@/lib/utils';
import type { DashboardStats } from '@/types';
import {
    ShoppingCart,
    DollarSign,
    Package,
    Clock,
    CheckCircle2,
    XCircle,
    FolderOpen,
    TrendingUp,
    Loader2,
} from 'lucide-react';
import Link from 'next/link';

export default function DashboardPage() {
    const [stats, setStats] = useState<DashboardStats | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        getDashboard()
            .then(setStats)
            .catch(console.error)
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return (
            <div className="flex items-center justify-center py-32">
                <Loader2 className="w-8 h-8 text-indigo-500 animate-spin" />
            </div>
        );
    }

    if (!stats) {
        return (
            <div className="text-center py-20 text-gray-500">
                Não foi possível carregar o dashboard.
            </div>
        );
    }

    const cards = [
        {
            label: 'Total de Pedidos',
            value: stats.orders.total,
            icon: ShoppingCart,
            gradient: 'from-indigo-600 to-indigo-400',
            glow: 'shadow-indigo-500/20',
        },
        {
            label: 'Receita Total',
            value: formatCurrency(stats.orders.total_revenue || 0),
            icon: DollarSign,
            gradient: 'from-emerald-600 to-emerald-400',
            glow: 'shadow-emerald-500/20',
        },
        {
            label: 'Pedidos Pagos',
            value: stats.orders.paid,
            icon: CheckCircle2,
            gradient: 'from-green-600 to-green-400',
            glow: 'shadow-green-500/20',
        },
        {
            label: 'Pedidos Pendentes',
            value: stats.orders.pending,
            icon: Clock,
            gradient: 'from-amber-600 to-amber-400',
            glow: 'shadow-amber-500/20',
        },
        {
            label: 'Pedidos Cancelados',
            value: stats.orders.cancelled,
            icon: XCircle,
            gradient: 'from-red-600 to-red-400',
            glow: 'shadow-red-500/20',
        },
        {
            label: 'Produtos Ativos',
            value: `${stats.products.active} / ${stats.products.total}`,
            icon: Package,
            gradient: 'from-purple-600 to-purple-400',
            glow: 'shadow-purple-500/20',
        },
        {
            label: 'Categorias',
            value: stats.categories_count,
            icon: FolderOpen,
            gradient: 'from-sky-600 to-sky-400',
            glow: 'shadow-sky-500/20',
        },
        {
            label: 'Taxa de Conversão',
            value: stats.orders.total > 0
                ? `${Math.round((stats.orders.paid / stats.orders.total) * 100)}%`
                : '0%',
            icon: TrendingUp,
            gradient: 'from-teal-600 to-teal-400',
            glow: 'shadow-teal-500/20',
        },
    ];

    return (
        <div className="space-y-8 animate-in">
            <div>
                <h1 className="text-2xl font-bold text-white">Dashboard</h1>
                <p className="text-gray-500 text-sm mt-1">Visão geral da sua loja</p>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {cards.map((card) => {
                    const Icon = card.icon;
                    return (
                        <div
                            key={card.label}
                            className={`bg-[#0f111a] border border-white/5 rounded-2xl p-5 hover:border-white/10 transition-all duration-200 shadow-lg ${card.glow}`}
                        >
                            <div className="flex items-center justify-between mb-3">
                                <span className="text-gray-500 text-xs font-medium uppercase tracking-wide">
                                    {card.label}
                                </span>
                                <div className={`w-9 h-9 rounded-xl bg-gradient-to-br ${card.gradient} flex items-center justify-center`}>
                                    <Icon className="w-4 h-4 text-white" />
                                </div>
                            </div>
                            <p className="text-2xl font-bold text-white">{card.value}</p>
                        </div>
                    );
                })}
            </div>

            {/* Recent Orders */}
            <div className="bg-[#0f111a] border border-white/5 rounded-2xl overflow-hidden">
                <div className="flex items-center justify-between p-5 border-b border-white/5">
                    <h2 className="text-lg font-semibold text-white">Pedidos Recentes</h2>
                    <Link
                        href="/pedidos"
                        className="text-indigo-400 hover:text-indigo-300 text-sm font-medium transition"
                    >
                        Ver todos →
                    </Link>
                </div>

                {stats.orders.recent && stats.orders.recent.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-white/5 text-gray-500 text-xs uppercase">
                                    <th className="text-left px-5 py-3 font-medium">Pedido</th>
                                    <th className="text-left px-5 py-3 font-medium">Cliente</th>
                                    <th className="text-left px-5 py-3 font-medium">Total</th>
                                    <th className="text-left px-5 py-3 font-medium">Status</th>
                                    <th className="text-left px-5 py-3 font-medium">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                {stats.orders.recent.map((order) => (
                                    <tr
                                        key={order.id}
                                        className="border-b border-white/[0.03] hover:bg-white/[0.02] transition"
                                    >
                                        <td className="px-5 py-3">
                                            <Link href={`/pedidos/${order.id}`} className="text-indigo-400 hover:underline font-medium">
                                                #{order.order_number}
                                            </Link>
                                        </td>
                                        <td className="px-5 py-3 text-gray-300">{order.customer_name}</td>
                                        <td className="px-5 py-3 text-white font-medium">
                                            {formatCurrency(order.total)}
                                        </td>
                                        <td className="px-5 py-3">
                                            <span className={`px-2.5 py-1 rounded-lg text-xs font-medium ${paymentStatusColors[order.payment_status] || 'bg-gray-500/20 text-gray-400'}`}>
                                                {paymentStatusLabels[order.payment_status] || order.payment_status}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3 text-gray-500 text-xs">
                                            {formatDate(order.created_at)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="text-center py-12 text-gray-500">
                        <ShoppingCart className="w-10 h-10 mx-auto mb-3 opacity-30" />
                        <p>Nenhum pedido recente.</p>
                    </div>
                )}
            </div>
        </div>
    );
}
