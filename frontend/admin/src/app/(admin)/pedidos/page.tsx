'use client';

import { useEffect, useState, useCallback } from 'react';
import { getOrders } from '@/lib/api';
import { formatCurrency, formatDate, paymentStatusLabels, paymentStatusColors, deliveryStatusLabels, deliveryStatusColors } from '@/lib/utils';
import type { Order, PaginatedResponse } from '@/types';
import { ShoppingCart, Loader2, Search } from 'lucide-react';
import Link from 'next/link';

export default function OrdersPage() {
    const [data, setData] = useState<PaginatedResponse<Order> | null>(null);
    const [loading, setLoading] = useState(true);
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState('');
    const [paymentFilter, setPaymentFilter] = useState('');
    const [deliveryFilter, setDeliveryFilter] = useState('');

    const fetchOrders = useCallback(async () => {
        setLoading(true);
        try {
            const result = await getOrders({
                page,
                per_page: 15,
                search: search || undefined,
                payment_status: paymentFilter || undefined,
                delivery_status: deliveryFilter || undefined,
            });
            setData(result);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    }, [page, search, paymentFilter, deliveryFilter]);

    useEffect(() => {
        fetchOrders();
    }, [fetchOrders]);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        setPage(1);
        fetchOrders();
    };

    return (
        <div className="space-y-6 animate-in">
            <div>
                <h1 className="text-2xl font-bold text-white">Pedidos</h1>
                <p className="text-gray-500 text-sm mt-1">Acompanhe e gerencie os pedidos da sua loja</p>
            </div>

            {/* Filters */}
            <div className="flex flex-wrap gap-3">
                <form onSubmit={handleSearch} className="relative flex-1 min-w-[200px] max-w-md">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Buscar por cliente ou nº do pedido..."
                        className="pl-10"
                    />
                </form>
                <select
                    value={paymentFilter}
                    onChange={(e) => { setPaymentFilter(e.target.value); setPage(1); }}
                    className="max-w-[180px]"
                >
                    <option value="">Pagamento: Todos</option>
                    <option value="pending">Pendente</option>
                    <option value="awaiting_payment">Aguardando</option>
                    <option value="paid">Pago</option>
                    <option value="cancelled">Cancelado</option>
                </select>
                <select
                    value={deliveryFilter}
                    onChange={(e) => { setDeliveryFilter(e.target.value); setPage(1); }}
                    className="max-w-[180px]"
                >
                    <option value="">Entrega: Todos</option>
                    <option value="pending">Pendente</option>
                    <option value="processing">Processando</option>
                    <option value="shipped">Enviado</option>
                    <option value="delivered">Entregue</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>

            {/* Table */}
            <div className="bg-[#0f111a] border border-white/5 rounded-2xl overflow-hidden">
                {loading ? (
                    <div className="flex items-center justify-center py-20">
                        <Loader2 className="w-6 h-6 text-indigo-500 animate-spin" />
                    </div>
                ) : !data || data.data.length === 0 ? (
                    <div className="text-center py-20 text-gray-500">
                        <ShoppingCart className="w-10 h-10 mx-auto mb-3 opacity-30" />
                        <p>Nenhum pedido encontrado.</p>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-white/5 text-gray-500 text-xs uppercase">
                                        <th className="text-left px-5 py-3 font-medium">Pedido</th>
                                        <th className="text-left px-5 py-3 font-medium">Cliente</th>
                                        <th className="text-left px-5 py-3 font-medium">Total</th>
                                        <th className="text-left px-5 py-3 font-medium">Pagamento</th>
                                        <th className="text-left px-5 py-3 font-medium">Entrega</th>
                                        <th className="text-left px-5 py-3 font-medium">Método</th>
                                        <th className="text-left px-5 py-3 font-medium">Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.data.map((order) => (
                                        <tr key={order.id} className="border-b border-white/[0.03] hover:bg-white/[0.02] transition">
                                            <td className="px-5 py-3">
                                                <Link href={`/pedidos/${order.id}`} className="text-indigo-400 hover:underline font-medium">
                                                    #{order.order_number}
                                                </Link>
                                            </td>
                                            <td className="px-5 py-3">
                                                <div>
                                                    <p className="text-white">{order.customer_name}</p>
                                                    {order.customer_email && (
                                                        <p className="text-gray-600 text-xs">{order.customer_email}</p>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-5 py-3 text-white font-medium">{formatCurrency(order.total)}</td>
                                            <td className="px-5 py-3">
                                                <span className={`px-2.5 py-1 rounded-lg text-xs font-medium ${paymentStatusColors[order.payment_status] || 'bg-gray-500/20 text-gray-400'}`}>
                                                    {paymentStatusLabels[order.payment_status] || order.payment_status}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3">
                                                <span className={`px-2.5 py-1 rounded-lg text-xs font-medium ${deliveryStatusColors[order.delivery_status] || 'bg-gray-500/20 text-gray-400'}`}>
                                                    {deliveryStatusLabels[order.delivery_status] || order.delivery_status}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3 text-gray-400 capitalize">{order.payment_method}</td>
                                            <td className="px-5 py-3 text-gray-500 text-xs">{formatDate(order.created_at)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {data.meta.last_page > 1 && (
                            <div className="flex items-center justify-between px-5 py-4 border-t border-white/5">
                                <span className="text-gray-500 text-sm">
                                    {data.meta.total} pedido(s) • Página {data.meta.current_page} de {data.meta.last_page}
                                </span>
                                <div className="flex gap-2">
                                    <button
                                        onClick={() => setPage(page - 1)}
                                        disabled={page <= 1}
                                        className="px-3 py-1.5 rounded-lg text-sm text-gray-400 hover:bg-white/5 disabled:opacity-30 transition"
                                    >
                                        Anterior
                                    </button>
                                    <button
                                        onClick={() => setPage(page + 1)}
                                        disabled={page >= data.meta.last_page}
                                        className="px-3 py-1.5 rounded-lg text-sm text-gray-400 hover:bg-white/5 disabled:opacity-30 transition"
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
