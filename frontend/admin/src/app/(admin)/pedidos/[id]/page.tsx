'use client';

import { useEffect, useState, use } from 'react';
import { getOrder, updatePaymentStatus, updateDeliveryStatus, markOrderAsPaid } from '@/lib/api';
import { formatCurrency, formatDate, paymentStatusLabels, paymentStatusColors, deliveryStatusLabels, deliveryStatusColors } from '@/lib/utils';
import type { Order, PaymentStatus, DeliveryStatus } from '@/types';
import { ArrowLeft, Loader2, CheckCircle2, CreditCard, Truck } from 'lucide-react';
import Link from 'next/link';

export default function OrderDetailPage({ params }: { params: Promise<{ id: string }> }) {
    const { id } = use(params);
    const [order, setOrder] = useState<Order | null>(null);
    const [loading, setLoading] = useState(true);
    const [updating, setUpdating] = useState(false);

    useEffect(() => {
        getOrder(Number(id))
            .then(setOrder)
            .catch(console.error)
            .finally(() => setLoading(false));
    }, [id]);

    const handlePaymentStatus = async (status: PaymentStatus) => {
        if (!order) return;
        setUpdating(true);
        try {
            const updated = await updatePaymentStatus(order.id, status);
            setOrder(updated);
        } catch (err) {
            console.error(err);
        } finally {
            setUpdating(false);
        }
    };

    const handleDeliveryStatus = async (status: DeliveryStatus) => {
        if (!order) return;
        setUpdating(true);
        try {
            const updated = await updateDeliveryStatus(order.id, status);
            setOrder(updated);
        } catch (err) {
            console.error(err);
        } finally {
            setUpdating(false);
        }
    };

    const handleMarkPaid = async () => {
        if (!order) return;
        setUpdating(true);
        try {
            const updated = await markOrderAsPaid(order.id);
            setOrder(updated);
        } catch (err) {
            console.error(err);
        } finally {
            setUpdating(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center py-32">
                <Loader2 className="w-8 h-8 text-green-600 dark:text-green-500 animate-spin" />
            </div>
        );
    }

    if (!order) {
        return (
            <div className="text-center py-20 text-gray-500">Pedido não encontrado.</div>
        );
    }

    return (
        <div className="space-y-6 animate-in max-w-4xl">
            <div className="flex items-center gap-4">
                <Link href="/pedidos" className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 text-gray-500 dark:text-gray-400 transition">
                    <ArrowLeft className="w-5 h-5" />
                </Link>
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Pedido #{order.order_number}</h1>
                    <p className="text-gray-500 text-sm mt-1">{formatDate(order.created_at)}</p>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Order Info */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Customer */}
                    <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-5 transition-colors duration-300">
                        <h3 className="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">Cliente</h3>
                        <p className="text-gray-900 dark:text-white font-medium">{order.customer_name}</p>
                        {order.customer_email && <p className="text-gray-500 text-sm">{order.customer_email}</p>}
                        {order.customer_phone && <p className="text-gray-500 text-sm">{order.customer_phone}</p>}
                    </div>

                    {/* Items */}
                    <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl overflow-hidden transition-colors duration-300">
                        <div className="p-5 border-b border-gray-200 dark:border-white/5">
                            <h3 className="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Itens do Pedido</h3>
                        </div>
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-200 dark:border-white/5 text-gray-500 text-xs uppercase">
                                    <th className="text-left px-5 py-3 font-medium">Produto</th>
                                    <th className="text-center px-5 py-3 font-medium">Qtd</th>
                                    <th className="text-right px-5 py-3 font-medium">Unitário</th>
                                    <th className="text-right px-5 py-3 font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {order.items.map((item) => (
                                    <tr key={item.id} className="border-b border-gray-100 dark:border-white/[0.03]">
                                        <td className="px-5 py-3">
                                            <p className="text-gray-900 dark:text-white">{item.product_name}</p>
                                            {item.variation_name && (
                                                <p className="text-gray-400 dark:text-gray-600 text-xs">{item.variation_name}</p>
                                            )}
                                        </td>
                                        <td className="px-5 py-3 text-center text-gray-700 dark:text-gray-300">{item.quantity}</td>
                                        <td className="px-5 py-3 text-right text-gray-600 dark:text-gray-400">{formatCurrency(item.unit_price)}</td>
                                        <td className="px-5 py-3 text-right text-gray-900 dark:text-white font-medium">{formatCurrency(item.total)}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot>
                                <tr className="border-t border-gray-200 dark:border-white/5">
                                    <td colSpan={3} className="px-5 py-2 text-right text-gray-500">Subtotal</td>
                                    <td className="px-5 py-2 text-right text-gray-700 dark:text-gray-300">{formatCurrency(order.subtotal)}</td>
                                </tr>
                                <tr>
                                    <td colSpan={3} className="px-5 py-2 text-right text-gray-500">Frete</td>
                                    <td className="px-5 py-2 text-right text-gray-700 dark:text-gray-300">{formatCurrency(order.shipping_cost)}</td>
                                </tr>
                                <tr className="border-t border-gray-200 dark:border-white/5">
                                    <td colSpan={3} className="px-5 py-3 text-right text-gray-900 dark:text-white font-semibold">Total</td>
                                    <td className="px-5 py-3 text-right text-gray-900 dark:text-white font-bold text-lg">{formatCurrency(order.total)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {order.notes && (
                        <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-5 transition-colors duration-300">
                            <h3 className="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Observações</h3>
                            <p className="text-gray-700 dark:text-gray-300 text-sm">{order.notes}</p>
                        </div>
                    )}
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    {/* Payment Status */}
                    <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-5 transition-colors duration-300">
                        <div className="flex items-center gap-2 mb-4">
                            <CreditCard className="w-4 h-4 text-green-600 dark:text-green-400" />
                            <h3 className="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Pagamento</h3>
                        </div>
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-gray-500 text-sm">Status:</span>
                                <span className={`px-2.5 py-1 rounded-lg text-xs font-medium ${paymentStatusColors[order.payment_status]}`}>
                                    {paymentStatusLabels[order.payment_status]}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-gray-500 text-sm">Método:</span>
                                <span className="text-gray-700 dark:text-gray-300 text-sm capitalize">{order.payment_method}</span>
                            </div>

                            {order.payment_status !== 'paid' && (
                                <button
                                    onClick={handleMarkPaid}
                                    disabled={updating}
                                    className="w-full mt-2 flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 text-sm font-medium hover:bg-emerald-600/30 transition disabled:opacity-50"
                                >
                                    {updating ? <Loader2 className="w-4 h-4 animate-spin" /> : <CheckCircle2 className="w-4 h-4" />}
                                    Marcar como Pago
                                </button>
                            )}

                            <div className="pt-2">
                                <label className="text-xs text-gray-500 dark:text-gray-600 mb-1.5 block">Alterar Status:</label>
                                <select
                                    value={order.payment_status}
                                    onChange={(e) => handlePaymentStatus(e.target.value as PaymentStatus)}
                                    disabled={updating}
                                >
                                    <option value="pending">Pendente</option>
                                    <option value="awaiting_payment">Aguardando</option>
                                    <option value="paid">Pago</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Delivery Status */}
                    <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-5 transition-colors duration-300">
                        <div className="flex items-center gap-2 mb-4">
                            <Truck className="w-4 h-4 text-green-600 dark:text-green-400" />
                            <h3 className="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Entrega</h3>
                        </div>
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-gray-500 text-sm">Status:</span>
                                <span className={`px-2.5 py-1 rounded-lg text-xs font-medium ${deliveryStatusColors[order.delivery_status]}`}>
                                    {deliveryStatusLabels[order.delivery_status]}
                                </span>
                            </div>

                            <div className="pt-2">
                                <label className="text-xs text-gray-500 dark:text-gray-600 mb-1.5 block">Alterar Status:</label>
                                <select
                                    value={order.delivery_status}
                                    onChange={(e) => handleDeliveryStatus(e.target.value as DeliveryStatus)}
                                    disabled={updating}
                                >
                                    <option value="pending">Pendente</option>
                                    <option value="processing">Processando</option>
                                    <option value="shipped">Enviado</option>
                                    <option value="delivered">Entregue</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
