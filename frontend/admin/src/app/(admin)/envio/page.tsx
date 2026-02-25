'use client';

import { useEffect, useState } from 'react';
import {
    getShippingOptions, createShippingOption, updateShippingOption,
    deleteShippingOption, updateShippingZip, updateCorreiosCredentials,
} from '@/lib/api';
import type { ShippingOption } from '@/types';
import {
    Truck, Plus, Pencil, Trash2, Loader2, CheckCircle2, Save,
    MapPin, Key, Eye, EyeOff, ExternalLink, Shield, AlertTriangle,
} from 'lucide-react';

interface FormData {
    name: string; city: string; state: string;
    price: number; delivery_days: number | null; active: boolean;
}

const EMPTY_FORM: FormData = { name: '', city: '', state: '', price: 0, delivery_days: null, active: true };

const STATES = [
    '', 'AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT',
    'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO',
];

export default function ShippingPage() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [savingCorreios, setSavingCorreios] = useState(false);
    const [options, setOptions] = useState<ShippingOption[]>([]);
    const [shippingZip, setShippingZip] = useState('');
    const [success, setSuccess] = useState('');
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);
    const [form, setForm] = useState<FormData>(EMPTY_FORM);
    const [correiosUser, setCorreiosUser] = useState('');
    const [correiosPassword, setCorreiosPassword] = useState('');
    const [correiosCartao, setCorreiosCartao] = useState('');
    const [hasCorreiosCredentials, setHasCorreiosCredentials] = useState(false);
    const [showPassword, setShowPassword] = useState(false);

    useEffect(() => { loadOptions(); }, []);

    const loadOptions = async () => {
        try {
            const res = await getShippingOptions();
            setOptions(res.data); setShippingZip(res.shipping_zip || '');
            setHasCorreiosCredentials(res.has_correios_credentials);
            setCorreiosUser(res.correios_user || '');
            setCorreiosCartao(res.correios_cartao_postagem || '');
        } catch (err) { console.error(err); } finally { setLoading(false); }
    };

    const showSuccessMsg = (msg: string) => { setSuccess(msg); setTimeout(() => setSuccess(''), 3000); };

    const handleSaveZip = async () => {
        setSaving(true);
        try { await updateShippingZip(shippingZip); showSuccessMsg('CEP de origem atualizado!'); }
        catch { /* ignore */ } finally { setSaving(false); }
    };

    const handleSaveCorreios = async () => {
        setSavingCorreios(true);
        try {
            const result = await updateCorreiosCredentials({
                correios_user: correiosUser || null, correios_password: correiosPassword || null,
                correios_cartao_postagem: correiosCartao || null,
            });
            setHasCorreiosCredentials(result.has_correios_credentials);
            setCorreiosPassword(''); showSuccessMsg('Credenciais dos Correios atualizadas!');
        } catch { /* ignore */ } finally { setSavingCorreios(false); }
    };

    const handleOpenNew = () => { setForm(EMPTY_FORM); setEditingId(null); setShowForm(true); };

    const handleEdit = (option: ShippingOption) => {
        setForm({ name: option.name, city: option.city || '', state: option.state || '', price: option.price, delivery_days: option.delivery_days, active: option.active });
        setEditingId(option.id); setShowForm(true);
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Excluir esta opção de frete?')) return;
        try { await deleteShippingOption(id); setOptions((prev) => prev.filter((o) => o.id !== id)); showSuccessMsg('Opção excluída.'); }
        catch { /* ignore */ }
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
                setOptions((prev) => [...prev, created]); showSuccessMsg('Opção criada!');
            }
            setShowForm(false); setEditingId(null);
        } catch { /* ignore */ } finally { setSaving(false); }
    };

    if (loading) return (
        <div className="flex items-center justify-center py-32">
            <Loader2 className="w-8 h-8 text-green-600 dark:text-green-500 animate-spin" />
        </div>
    );

    return (
        <div className="space-y-6 animate-in max-w-3xl">
            <div>
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <Truck className="w-7 h-7 text-green-600 dark:text-green-400" /> Envios e Frete
                </h1>
                <p className="text-gray-500 text-sm mt-1">Configure as opções de frete da sua loja</p>
            </div>

            {success && (
                <div className="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm rounded-xl px-4 py-3 scale-in">
                    <CheckCircle2 className="w-4 h-4" /> {success}
                </div>
            )}

            {/* CEP de Origem */}
            <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-6 transition-colors duration-300">
                <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                    <MapPin className="w-5 h-5 text-green-600 dark:text-green-400" /> CEP de Origem
                </h2>
                <p className="text-gray-500 text-xs mb-4">CEP da sua loja, usado para calcular frete ao comprador.</p>
                <div className="flex items-end gap-3">
                    <div className="flex-1 max-w-xs">
                        <label htmlFor="shipping_zip">CEP</label>
                        <input id="shipping_zip" type="text" value={shippingZip} onChange={(e) => setShippingZip(e.target.value)} placeholder="69000-000" maxLength={9} />
                    </div>
                    <button type="button" onClick={handleSaveZip} disabled={saving}
                        className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-500 transition-all shadow-lg shadow-green-500/25 disabled:opacity-50">
                        {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />} Salvar
                    </button>
                </div>
            </div>

            {/* Credenciais Correios */}
            <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-6 transition-colors duration-300">
                <div className="flex items-center justify-between mb-1">
                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <Key className="w-5 h-5 text-amber-500 dark:text-amber-400" /> API Oficial dos Correios
                    </h2>
                    {hasCorreiosCredentials ? (
                        <span className="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 text-xs font-medium bg-emerald-500/10 px-3 py-1 rounded-full">
                            <Shield className="w-3.5 h-3.5" /> Configurado
                        </span>
                    ) : (
                        <span className="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 text-xs font-medium bg-amber-500/10 px-3 py-1 rounded-full">
                            <AlertTriangle className="w-3.5 h-3.5" /> Não configurado
                        </span>
                    )}
                </div>
                <p className="text-gray-500 text-xs mb-4">Conecte sua conta empresarial dos Correios para cálculo de frete em tempo real (SEDEX e PAC). Sem as credenciais, o sistema usará uma estimativa automática.</p>
                <div className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label htmlFor="correios_user">Usuário (Meu Correios)</label>
                            <input id="correios_user" type="text" value={correiosUser} onChange={(e) => setCorreiosUser(e.target.value)} placeholder="seu.usuario@email.com" />
                        </div>
                        <div>
                            <label htmlFor="correios_password">Código de Acesso (CWS)</label>
                            <div className="relative">
                                <input id="correios_password" type={showPassword ? 'text' : 'password'} value={correiosPassword}
                                    onChange={(e) => setCorreiosPassword(e.target.value)} placeholder={hasCorreiosCredentials ? '••••••••' : 'Senha gerada no CWS'} />
                                <button type="button" onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition">
                                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div className="max-w-xs">
                        <label htmlFor="correios_cartao">Cartão de Postagem</label>
                        <input id="correios_cartao" type="text" value={correiosCartao} onChange={(e) => setCorreiosCartao(e.target.value)} placeholder="0076543210" />
                    </div>
                    <div className="flex items-center justify-between">
                        <a href="https://cws.correios.com.br" target="_blank" rel="noopener noreferrer"
                            className="flex items-center gap-1.5 text-green-600 dark:text-green-400 text-xs hover:text-green-500 dark:hover:text-green-300 transition">
                            <ExternalLink className="w-3.5 h-3.5" /> Acessar CWS dos Correios
                        </a>
                        <button type="button" onClick={handleSaveCorreios} disabled={savingCorreios}
                            className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-semibold hover:bg-amber-500 transition-all shadow-lg shadow-amber-500/25 disabled:opacity-50">
                            {savingCorreios ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />} Salvar Credenciais
                        </button>
                    </div>
                </div>
                <div className="mt-4 bg-amber-500/5 border border-amber-500/10 rounded-xl p-3">
                    <p className="text-amber-700 dark:text-amber-300/80 text-xs leading-relaxed">
                        <strong>Como obter:</strong> Acesse <a href="https://cws.correios.com.br" target="_blank" rel="noopener noreferrer" className="underline">cws.correios.com.br</a> com seu login do &quot;Meu Correios&quot;. Vá em &quot;Credenciais&quot; para gerar o código de acesso. O número do cartão de postagem está vinculado ao seu contrato comercial.
                    </p>
                </div>
            </div>

            {/* Opções de Frete Fixo */}
            <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-6 transition-colors duration-300">
                <div className="flex items-center justify-between mb-4">
                    <div>
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white">Opções de Frete Fixo</h2>
                        <p className="text-gray-500 text-xs">Defina preços fixos de frete por cidade ou estado.</p>
                    </div>
                    <button type="button" onClick={handleOpenNew}
                        className="flex items-center gap-2 px-4 py-2 rounded-xl bg-green-500/15 text-green-600 dark:text-green-400 text-sm font-medium hover:bg-green-500/25 transition">
                        <Plus className="w-4 h-4" /> Nova Opção
                    </button>
                </div>

                {showForm && (
                    <div className="bg-gray-50 dark:bg-white/[0.02] border border-gray-200 dark:border-white/10 rounded-xl p-5 mb-4 space-y-4 animate-in transition-colors duration-300">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="md:col-span-2"><label>Nome *</label><input type="text" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} placeholder="Ex: Entrega Local Manaus" required /></div>
                            <div><label>Cidade</label><input type="text" value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} placeholder="Ex: Manaus (deixe vazio = todas)" /></div>
                            <div><label>Estado</label><select value={form.state} onChange={(e) => setForm({ ...form, state: e.target.value })}><option value="">Todos</option>{STATES.filter(Boolean).map((s) => (<option key={s} value={s}>{s}</option>))}</select></div>
                            <div><label>Preço (R$) *</label><input type="number" step="0.01" min="0" value={form.price} onChange={(e) => setForm({ ...form, price: Number(e.target.value) })} required /></div>
                            <div><label>Prazo (dias)</label><input type="number" min="0" value={form.delivery_days ?? ''} onChange={(e) => setForm({ ...form, delivery_days: e.target.value ? Number(e.target.value) : null })} placeholder="Ex: 3" /></div>
                        </div>
                        <div className="flex items-center gap-4">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={form.active} onChange={(e) => setForm({ ...form, active: e.target.checked })} className="w-4 h-4 rounded accent-green-600" />
                                <span className="text-sm text-gray-600 dark:text-gray-400">Ativa</span>
                            </label>
                        </div>
                        <div className="flex justify-end gap-3">
                            <button type="button" onClick={() => { setShowForm(false); setEditingId(null); }} className="px-4 py-2 rounded-xl text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition">Cancelar</button>
                            <button type="button" onClick={handleSubmitOption} disabled={saving || !form.name || form.price < 0}
                                className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-500 transition-all shadow-lg shadow-green-500/25 disabled:opacity-50">
                                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />} {editingId ? 'Atualizar' : 'Criar'}
                            </button>
                        </div>
                    </div>
                )}

                {options.length === 0 ? (
                    <div className="text-center py-8">
                        <Truck className="w-10 h-10 text-gray-300 dark:text-gray-700 mx-auto mb-2" />
                        <p className="text-gray-500 text-sm">Nenhuma opção de frete fixo cadastrada.</p>
                        <p className="text-gray-400 dark:text-gray-600 text-xs mt-1">Clique em &quot;Nova Opção&quot; para adicionar.</p>
                    </div>
                ) : (
                    <div className="divide-y divide-gray-100 dark:divide-white/5">
                        {options.map((option) => (
                            <div key={option.id} className="flex items-center justify-between py-3 px-1 group">
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2">
                                        <span className={`w-2 h-2 rounded-full ${option.active ? 'bg-emerald-400' : 'bg-gray-400 dark:bg-gray-600'}`} />
                                        <span className="text-gray-900 dark:text-white font-medium text-sm">{option.name}</span>
                                        {(option.city || option.state) && <span className="text-gray-500 text-xs">({[option.city, option.state].filter(Boolean).join(' — ')})</span>}
                                    </div>
                                    <div className="flex items-center gap-4 mt-0.5 ml-4">
                                        <span className="text-green-600 dark:text-green-400 text-sm font-semibold">R$ {Number(option.price).toFixed(2)}</span>
                                        {option.delivery_days && <span className="text-gray-500 text-xs">{option.delivery_days} {option.delivery_days === 1 ? 'dia' : 'dias'}</span>}
                                    </div>
                                </div>
                                <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                    <button onClick={() => handleEdit(option)} className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition" title="Editar"><Pencil className="w-4 h-4" /></button>
                                    <button onClick={() => handleDelete(option.id)} className="p-2 rounded-lg hover:bg-red-500/10 text-gray-400 hover:text-red-400 transition" title="Excluir"><Trash2 className="w-4 h-4" /></button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            <div className="bg-green-500/5 border border-green-500/10 rounded-xl p-4">
                <p className="text-green-700 dark:text-green-300 text-sm font-medium mb-1">💡 Como funciona?</p>
                <p className="text-gray-600 dark:text-gray-400 text-xs leading-relaxed">Ao finalizar a compra, o cliente digita seu CEP. O sistema mostra:</p>
                <ul className="text-gray-600 dark:text-gray-400 text-xs mt-1 space-y-0.5 ml-4 list-disc">
                    <li><b>Fretes fixos</b> — exibidos se a cidade/estado do comprador corresponder</li>
                    <li><b>Correios (PAC/Sedex)</b> — calculados via API oficial (se configurado) ou por estimativa automática</li>
                </ul>
            </div>
        </div>
    );
}
