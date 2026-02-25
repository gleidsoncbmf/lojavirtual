'use client';

import { useEffect, useState } from 'react';
import {
    getShippingOptions,
    createShippingOption,
    updateShippingOption,
    deleteShippingOption,
    updateShippingZip,
} from '@/lib/api';
import type { ShippingOption } from '@/types';
import { Truck, Plus, Pencil, Trash2, Loader2, CheckCircle2, Save, MapPin } from 'lucide-react';

interface FormData {
    name: string;
    city: string;
    state: string;
    price: number;
    delivery_days: number | null;
    active: boolean;
}

const EMPTY_FORM: FormData = {
    name: '',
    city: '',
    state: '',
    price: 0,
    delivery_days: null,
    active: true,
};

const STATES = [
    '', 'AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT',
    'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO',
];

export default function ShippingPage() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [options, setOptions] = useState<ShippingOption[]>([]);
    const [shippingZip, setShippingZip] = useState('');
    const [success, setSuccess] = useState('');
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);
    const [form, setForm] = useState<FormData>(EMPTY_FORM);

    useEffect(() => {
        loadOptions();
    }, []);

    const loadOptions = async () => {
        try {
            const res = await getShippingOptions();
            setOptions(res.data);
            setShippingZip(res.shipping_zip || '');
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const showSuccessMsg = (msg: string) => {
        setSuccess(msg);
        setTimeout(() => setSuccess(''), 3000);
    };

    const handleSaveZip = async () => {
        setSaving(true);
        try {
            await updateShippingZip(shippingZip);
            showSuccessMsg('CEP de origem atualizado!');
        } catch { /* ignore */ } finally {
            setSaving(false);
        }
    };

    const handleOpenNew = () => {
        setForm(EMPTY_FORM);
        setEditingId(null);
        setShowForm(true);
    };

    const handleEdit = (option: ShippingOption) => {
        setForm({
            name: option.name,
            city: option.city || '',
            state: option.state || '',
            price: option.price,
            delivery_days: option.delivery_days,
            active: option.active,
        });
        setEditingId(option.id);
        setShowForm(true);
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Excluir esta opção de frete?')) return;
        try {
            await deleteShippingOption(id);
            setOptions((prev) => prev.filter((o) => o.id !== id));
            showSuccessMsg('Opção excluída.');
        } catch { /* ignore */ }
    };

    const handleSubmitOption = async () => {
        setSaving(true);
        try {
            if (editingId) {
                const updated = await updateShippingOption(editingId, form);
                setOptions((prev) => prev.map((o) => (o.id === editingId ? updated : o)));
                showSuccessMsg('Opção atualizada!');
            } else {
                const created = await createShippingOption(form as Omit<ShippingOption, 'id'>);
                setOptions((prev) => [...prev, created]);
                showSuccessMsg('Opção criada!');
            }
            setShowForm(false);
            setEditingId(null);
        } catch { /* ignore */ } finally {
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
            <div>
                <h1 className="text-2xl font-bold text-white flex items-center gap-3">
                    <Truck className="w-7 h-7 text-indigo-400" />
                    Envios e Frete
                </h1>
                <p className="text-gray-500 text-sm mt-1">Configure as opções de frete da sua loja</p>
            </div>

            {success && (
                <div className="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm rounded-xl px-4 py-3 scale-in">
                    <CheckCircle2 className="w-4 h-4" />
                    {success}
                </div>
            )}

            {/* CEP de Origem */}
            <div className="bg-[#0f111a] border border-white/5 rounded-2xl p-6">
                <h2 className="text-lg font-semibold text-white mb-1 flex items-center gap-2">
                    <MapPin className="w-5 h-5 text-indigo-400" />
                    CEP de Origem
                </h2>
                <p className="text-gray-500 text-xs mb-4">CEP da sua loja, usado para calcular frete ao comprador.</p>
                <div className="flex items-end gap-3">
                    <div className="flex-1 max-w-xs">
                        <label htmlFor="shipping_zip">CEP</label>
                        <input
                            id="shipping_zip"
                            type="text"
                            value={shippingZip}
                            onChange={(e) => setShippingZip(e.target.value)}
                            placeholder="69000-000"
                            maxLength={9}
                        />
                    </div>
                    <button
                        type="button"
                        onClick={handleSaveZip}
                        disabled={saving}
                        className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold hover:from-indigo-500 hover:to-purple-500 transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50"
                    >
                        {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                        Salvar
                    </button>
                </div>
            </div>

            {/* Opções de Frete Fixo */}
            <div className="bg-[#0f111a] border border-white/5 rounded-2xl p-6">
                <div className="flex items-center justify-between mb-4">
                    <div>
                        <h2 className="text-lg font-semibold text-white">Opções de Frete Fixo</h2>
                        <p className="text-gray-500 text-xs">Defina preços fixos de frete por cidade ou estado.</p>
                    </div>
                    <button
                        type="button"
                        onClick={handleOpenNew}
                        className="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-500/15 text-indigo-400 text-sm font-medium hover:bg-indigo-500/25 transition"
                    >
                        <Plus className="w-4 h-4" />
                        Nova Opção
                    </button>
                </div>

                {/* Option Form */}
                {showForm && (
                    <div className="bg-white/[0.02] border border-white/10 rounded-xl p-5 mb-4 space-y-4 animate-in">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="md:col-span-2">
                                <label>Nome *</label>
                                <input
                                    type="text"
                                    value={form.name}
                                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                                    placeholder="Ex: Entrega Local Manaus"
                                    required
                                />
                            </div>
                            <div>
                                <label>Cidade</label>
                                <input
                                    type="text"
                                    value={form.city}
                                    onChange={(e) => setForm({ ...form, city: e.target.value })}
                                    placeholder="Ex: Manaus (deixe vazio = todas)"
                                />
                            </div>
                            <div>
                                <label>Estado</label>
                                <select
                                    value={form.state}
                                    onChange={(e) => setForm({ ...form, state: e.target.value })}
                                >
                                    <option value="">Todos</option>
                                    {STATES.filter(Boolean).map((s) => (
                                        <option key={s} value={s}>{s}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label>Preço (R$) *</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={form.price}
                                    onChange={(e) => setForm({ ...form, price: Number(e.target.value) })}
                                    required
                                />
                            </div>
                            <div>
                                <label>Prazo (dias)</label>
                                <input
                                    type="number"
                                    min="0"
                                    value={form.delivery_days ?? ''}
                                    onChange={(e) => setForm({ ...form, delivery_days: e.target.value ? Number(e.target.value) : null })}
                                    placeholder="Ex: 3"
                                />
                            </div>
                        </div>
                        <div className="flex items-center gap-4">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={form.active}
                                    onChange={(e) => setForm({ ...form, active: e.target.checked })}
                                    className="w-4 h-4 rounded accent-indigo-500"
                                />
                                <span className="text-sm text-gray-400">Ativa</span>
                            </label>
                        </div>
                        <div className="flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={() => { setShowForm(false); setEditingId(null); }}
                                className="px-4 py-2 rounded-xl text-sm text-gray-400 hover:text-white hover:bg-white/5 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                onClick={handleSubmitOption}
                                disabled={saving || !form.name || form.price < 0}
                                className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold hover:from-indigo-500 hover:to-purple-500 transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50"
                            >
                                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                                {editingId ? 'Atualizar' : 'Criar'}
                            </button>
                        </div>
                    </div>
                )}

                {/* Options List */}
                {options.length === 0 ? (
                    <div className="text-center py-8">
                        <Truck className="w-10 h-10 text-gray-700 mx-auto mb-2" />
                        <p className="text-gray-500 text-sm">Nenhuma opção de frete fixo cadastrada.</p>
                        <p className="text-gray-600 text-xs mt-1">Clique em &quot;Nova Opção&quot; para adicionar.</p>
                    </div>
                ) : (
                    <div className="divide-y divide-white/5">
                        {options.map((option) => (
                            <div key={option.id} className="flex items-center justify-between py-3 px-1 group">
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2">
                                        <span className={`w-2 h-2 rounded-full ${option.active ? 'bg-emerald-400' : 'bg-gray-600'}`} />
                                        <span className="text-white font-medium text-sm">{option.name}</span>
                                        {(option.city || option.state) && (
                                            <span className="text-gray-500 text-xs">
                                                ({[option.city, option.state].filter(Boolean).join(' — ')})
                                            </span>
                                        )}
                                    </div>
                                    <div className="flex items-center gap-4 mt-0.5 ml-4">
                                        <span className="text-indigo-400 text-sm font-semibold">
                                            R$ {Number(option.price).toFixed(2)}
                                        </span>
                                        {option.delivery_days && (
                                            <span className="text-gray-500 text-xs">
                                                {option.delivery_days} {option.delivery_days === 1 ? 'dia' : 'dias'}
                                            </span>
                                        )}
                                    </div>
                                </div>
                                <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                    <button
                                        onClick={() => handleEdit(option)}
                                        className="p-2 rounded-lg hover:bg-white/5 text-gray-400 hover:text-indigo-400 transition"
                                        title="Editar"
                                    >
                                        <Pencil className="w-4 h-4" />
                                    </button>
                                    <button
                                        onClick={() => handleDelete(option.id)}
                                        className="p-2 rounded-lg hover:bg-white/5 text-gray-400 hover:text-red-400 transition"
                                        title="Excluir"
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Info */}
            <div className="bg-indigo-500/5 border border-indigo-500/10 rounded-xl p-4">
                <p className="text-indigo-300 text-sm font-medium mb-1">💡 Como funciona?</p>
                <p className="text-gray-400 text-xs leading-relaxed">
                    Ao finalizar a compra, o cliente digita seu CEP. O sistema mostra:
                </p>
                <ul className="text-gray-400 text-xs mt-1 space-y-0.5 ml-4 list-disc">
                    <li><b>Fretes fixos</b> — exibidos se a cidade/estado do comprador corresponder</li>
                    <li><b>Correios (PAC/Sedex)</b> — calculados automaticamente pelo CEP e dimensões dos produtos</li>
                </ul>
            </div>
        </div>
    );
}
