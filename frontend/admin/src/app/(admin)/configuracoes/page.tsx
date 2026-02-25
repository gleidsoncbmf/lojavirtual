'use client';

import { useEffect, useState } from 'react';
import { getSettings, updateStoreInfo, updateTheme, updatePaymentConfig, updateWhatsApp, updateDomain } from '@/lib/api';
import type { StoreConfig, StoreTheme, PaymentConfig } from '@/types';
import { Store, Palette, CreditCard, MessageCircle, Globe, Loader2, Save, CheckCircle2 } from 'lucide-react';
import SingleImageUploader from '@/components/SingleImageUploader';

type Tab = 'store' | 'theme' | 'payments' | 'whatsapp' | 'domain';

export default function SettingsPage() {
    const [settings, setSettings] = useState<StoreConfig | null>(null);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [activeTab, setActiveTab] = useState<Tab>('store');
    const [success, setSuccess] = useState('');
    const [storeForm, setStoreForm] = useState({ name: '', email: '', logo_url: '', banner_url: '', banner_position: 'center' });
    const [themeForm, setThemeForm] = useState<Partial<StoreTheme>>({});
    const [paymentForm, setPaymentForm] = useState<PaymentConfig>({});
    const [whatsappForm, setWhatsappForm] = useState('');
    const [domainForm, setDomainForm] = useState('');

    useEffect(() => {
        getSettings().then((data) => {
            setSettings(data);
            setStoreForm({ name: data.name || '', email: data.email || '', logo_url: data.logo_url || '', banner_url: data.banner_url || '', banner_position: data.banner_position || 'center' });
            if (data.theme) setThemeForm({ primary_color: data.theme.primary_color, secondary_color: data.theme.secondary_color, button_color: data.theme.button_color, text_color: data.theme.text_color, background_color: data.theme.background_color, seo_title: data.theme.seo_title || '', seo_description: data.theme.seo_description || '' });
            setPaymentForm(data.payment_config || {});
            setWhatsappForm(data.whatsapp || '');
            if (data.domains?.[0]) setDomainForm(data.domains[0].domain);
        }).catch(console.error).finally(() => setLoading(false));
    }, []);

    const showSuccess = (msg: string) => { setSuccess(msg); setTimeout(() => setSuccess(''), 3000); };

    const handleSaveStore = async (e: React.FormEvent) => { e.preventDefault(); setSaving(true); try { const updated = await updateStoreInfo(storeForm); setSettings(updated); showSuccess('Dados da loja atualizados!'); } catch { /* ignore */ } finally { setSaving(false); } };
    const handleSaveTheme = async (e: React.FormEvent) => { e.preventDefault(); setSaving(true); try { await updateTheme(themeForm); showSuccess('Tema atualizado!'); } catch { /* ignore */ } finally { setSaving(false); } };
    const handleSavePayment = async (e: React.FormEvent) => { e.preventDefault(); setSaving(true); try { await updatePaymentConfig(paymentForm); showSuccess('Configuração de pagamento atualizada!'); } catch { /* ignore */ } finally { setSaving(false); } };
    const handleSaveWhatsApp = async (e: React.FormEvent) => { e.preventDefault(); setSaving(true); try { await updateWhatsApp(whatsappForm); showSuccess('WhatsApp atualizado!'); } catch { /* ignore */ } finally { setSaving(false); } };
    const handleSaveDomain = async (e: React.FormEvent) => { e.preventDefault(); setSaving(true); try { await updateDomain(domainForm); showSuccess('Domínio configurado!'); } catch { /* ignore */ } finally { setSaving(false); } };

    const tabs = [
        { id: 'store' as Tab, label: 'Loja', icon: Store },
        { id: 'theme' as Tab, label: 'Tema', icon: Palette },
        { id: 'payments' as Tab, label: 'Pagamentos', icon: CreditCard },
        { id: 'whatsapp' as Tab, label: 'WhatsApp', icon: MessageCircle },
        { id: 'domain' as Tab, label: 'Domínio', icon: Globe },
    ];

    if (loading) return (
        <div className="flex items-center justify-center py-32">
            <Loader2 className="w-8 h-8 text-green-600 dark:text-green-500 animate-spin" />
        </div>
    );

    return (
        <div className="space-y-6 animate-in">
            <div>
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Configurações</h1>
                <p className="text-gray-500 text-sm mt-1">Personalize a sua loja</p>
            </div>

            {success && (
                <div className="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm rounded-xl px-4 py-3 scale-in">
                    <CheckCircle2 className="w-4 h-4" /> {success}
                </div>
            )}

            {/* Tabs */}
            <div className="flex gap-1 bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-xl p-1 overflow-x-auto transition-colors duration-300">
                {tabs.map((tab) => {
                    const Icon = tab.icon;
                    return (
                        <button key={tab.id} onClick={() => setActiveTab(tab.id)}
                            className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap ${activeTab === tab.id
                                ? 'bg-green-500/15 text-green-600 dark:text-green-400'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5'
                                }`}>
                            <Icon className="w-4 h-4" /> {tab.label}
                        </button>
                    );
                })}
            </div>

            {/* Tab Content */}
            <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-6 transition-colors duration-300">
                {activeTab === 'store' && (
                    <form onSubmit={handleSaveStore} className="space-y-5 max-w-xl">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações da Loja</h2>
                        <div><label htmlFor="store-name">Nome da Loja</label><input id="store-name" type="text" value={storeForm.name} onChange={(e) => setStoreForm({ ...storeForm, name: e.target.value })} /></div>
                        <div><label htmlFor="store-email">E-mail</label><input id="store-email" type="email" value={storeForm.email} onChange={(e) => setStoreForm({ ...storeForm, email: e.target.value })} /></div>
                        <div><SingleImageUploader value={storeForm.logo_url} onChange={(url) => setStoreForm({ ...storeForm, logo_url: url })} folder="logos" label="Logo da Loja" /></div>
                        <div><SingleImageUploader value={storeForm.banner_url} onChange={(url) => setStoreForm({ ...storeForm, banner_url: url })} folder="banners" label="Banner da Loja" /></div>
                        <div>
                            <label className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                Posição do Banner <span className="text-xs text-gray-500 ml-2 font-normal">(Arraste a imagem para ajustar o foco)</span>
                            </label>
                            <BannerPositionEditor imageUrl={storeForm.banner_url} position={storeForm.banner_position || 'center'} onChange={(newPos) => setStoreForm({ ...storeForm, banner_position: newPos })} />
                        </div>
                        <SaveButton saving={saving} />
                    </form>
                )}

                {activeTab === 'theme' && (
                    <form onSubmit={handleSaveTheme} className="space-y-5 max-w-xl">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personalização do Tema</h2>
                        <div className="grid grid-cols-2 gap-4">
                            {[
                                { key: 'primary_color', label: 'Cor Primária' }, { key: 'secondary_color', label: 'Cor Secundária' },
                                { key: 'button_color', label: 'Cor dos Botões' }, { key: 'text_color', label: 'Cor do Texto' },
                                { key: 'background_color', label: 'Cor de Fundo' },
                            ].map(({ key, label }) => (
                                <div key={key}>
                                    <label htmlFor={key}>{label}</label>
                                    <div className="flex items-center gap-3">
                                        <input type="color" value={(themeForm as Record<string, string>)[key] || '#000000'} onChange={(e) => setThemeForm({ ...themeForm, [key]: e.target.value })}
                                            className="w-10 h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer bg-transparent p-0" />
                                        <input id={key} type="text" value={(themeForm as Record<string, string>)[key] || ''} onChange={(e) => setThemeForm({ ...themeForm, [key]: e.target.value })} className="flex-1" maxLength={7} />
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div><label htmlFor="seo_title">SEO — Título</label><input id="seo_title" type="text" value={themeForm.seo_title || ''} onChange={(e) => setThemeForm({ ...themeForm, seo_title: e.target.value })} placeholder="Título da loja nos buscadores" /></div>
                        <div><label htmlFor="seo_description">SEO — Descrição</label><textarea id="seo_description" rows={3} value={themeForm.seo_description || ''} onChange={(e) => setThemeForm({ ...themeForm, seo_description: e.target.value })} placeholder="Descrição para mecanismos de busca..." /></div>
                        <SaveButton saving={saving} />
                    </form>
                )}

                {activeTab === 'payments' && (
                    <form onSubmit={handleSavePayment} className="space-y-6 max-w-xl">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Gateways de Pagamento</h2>
                        <div className="bg-gray-50 dark:bg-white/[0.02] border border-gray-200 dark:border-white/5 rounded-xl p-5 space-y-4 transition-colors duration-300">
                            <div className="flex items-center justify-between">
                                <h3 className="text-gray-900 dark:text-white font-medium">Stripe</h3>
                                <label className="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" checked={paymentForm.stripe?.enabled || false}
                                        onChange={(e) => setPaymentForm({ ...paymentForm, stripe: { ...paymentForm.stripe, enabled: e.target.checked } as PaymentConfig['stripe'] })}
                                        className="w-4 h-4 rounded accent-green-600" />
                                    <span className="text-sm text-gray-600 dark:text-gray-400">Ativo</span>
                                </label>
                            </div>
                            <div><label>Public Key</label><input type="text" value={paymentForm.stripe?.public_key || ''} onChange={(e) => setPaymentForm({ ...paymentForm, stripe: { ...paymentForm.stripe, public_key: e.target.value, enabled: paymentForm.stripe?.enabled || false } })} placeholder="pk_..." /></div>
                            <div><label>Secret Key</label><input type="password" value={paymentForm.stripe?.secret_key || ''} onChange={(e) => setPaymentForm({ ...paymentForm, stripe: { ...paymentForm.stripe, secret_key: e.target.value, enabled: paymentForm.stripe?.enabled || false } })} placeholder="sk_..." /></div>
                            <div><label>Webhook Secret</label><input type="password" value={paymentForm.stripe?.webhook_secret || ''} onChange={(e) => setPaymentForm({ ...paymentForm, stripe: { ...paymentForm.stripe, webhook_secret: e.target.value, enabled: paymentForm.stripe?.enabled || false } })} placeholder="whsec_..." /></div>
                        </div>
                        <div className="bg-gray-50 dark:bg-white/[0.02] border border-gray-200 dark:border-white/5 rounded-xl p-5 space-y-4 transition-colors duration-300">
                            <div className="flex items-center justify-between">
                                <h3 className="text-gray-900 dark:text-white font-medium">Mercado Pago</h3>
                                <label className="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" checked={paymentForm.mercadopago?.enabled || false}
                                        onChange={(e) => setPaymentForm({ ...paymentForm, mercadopago: { ...paymentForm.mercadopago, enabled: e.target.checked } as PaymentConfig['mercadopago'] })}
                                        className="w-4 h-4 rounded accent-green-600" />
                                    <span className="text-sm text-gray-600 dark:text-gray-400">Ativo</span>
                                </label>
                            </div>
                            <div><label>Access Token</label><input type="password" value={paymentForm.mercadopago?.access_token || ''} onChange={(e) => setPaymentForm({ ...paymentForm, mercadopago: { ...paymentForm.mercadopago, access_token: e.target.value, enabled: paymentForm.mercadopago?.enabled || false } })} placeholder="APP_USR-..." /></div>
                            <div><label>Public Key</label><input type="text" value={paymentForm.mercadopago?.public_key || ''} onChange={(e) => setPaymentForm({ ...paymentForm, mercadopago: { ...paymentForm.mercadopago, public_key: e.target.value, enabled: paymentForm.mercadopago?.enabled || false } })} placeholder="APP_USR-..." /></div>
                        </div>
                        <SaveButton saving={saving} />
                    </form>
                )}

                {activeTab === 'whatsapp' && (
                    <form onSubmit={handleSaveWhatsApp} className="space-y-5 max-w-xl">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">WhatsApp</h2>
                        <div>
                            <label htmlFor="whatsapp">Número do WhatsApp</label>
                            <input id="whatsapp" type="tel" value={whatsappForm} onChange={(e) => setWhatsappForm(e.target.value)} placeholder="5511999999999" />
                            <p className="text-gray-400 dark:text-gray-600 text-xs mt-1.5">Formato: código do país + DDD + número (sem espaços ou hífens)</p>
                        </div>
                        <SaveButton saving={saving} />
                    </form>
                )}

                {activeTab === 'domain' && (
                    <form onSubmit={handleSaveDomain} className="space-y-5 max-w-xl">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Domínio Próprio</h2>
                        <div>
                            <label htmlFor="domain">Domínio</label>
                            <input id="domain" type="text" value={domainForm} onChange={(e) => setDomainForm(e.target.value)} placeholder="www.minhaloja.com.br" />
                            <p className="text-gray-400 dark:text-gray-600 text-xs mt-1.5">Após configurar, aponte o DNS do seu domínio para o IP da plataforma.</p>
                        </div>
                        {settings?.domains?.[0] && (
                            <div className="flex items-center gap-2 text-sm">
                                <span className={`w-2 h-2 rounded-full ${settings.domains[0].verified ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse'}`} />
                                <span className={settings.domains[0].verified ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'}>
                                    {settings.domains[0].verified ? 'Verificado' : 'Aguardando verificação'}
                                </span>
                            </div>
                        )}
                        <SaveButton saving={saving} />
                    </form>
                )}
            </div>
        </div>
    );
}

function SaveButton({ saving }: { saving: boolean }) {
    return (
        <div className="flex justify-end pt-2">
            <button type="submit" disabled={saving}
                className="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-500 transition-all shadow-lg shadow-green-500/25 disabled:opacity-50">
                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                {saving ? 'Salvando...' : 'Salvar'}
            </button>
        </div>
    );
}

function BannerPositionEditor({ imageUrl, position, onChange }: { imageUrl: string, position: string, onChange: (pos: string) => void }) {
    const [isDragging, setIsDragging] = useState(false);
    const [startPos, setStartPos] = useState({ x: 0, y: 0 });
    const [currentPos, setCurrentPos] = useState(() => {
        if (!position || ['top', 'center', 'bottom'].includes(position)) return { x: 50, y: 50 };
        const parts = position.split(' ');
        return { x: parseFloat(parts[0]) || 50, y: parseFloat(parts[1]) || 50 };
    });

    useEffect(() => {
        if (['top', 'center', 'bottom'].includes(position)) {
            if (position === 'top') setCurrentPos({ x: 50, y: 0 });
            else if (position === 'bottom') setCurrentPos({ x: 50, y: 100 });
            else setCurrentPos({ x: 50, y: 50 });
        }
    }, [position]);

    const handleMouseDown = (e: React.MouseEvent) => { if (!imageUrl) return; setIsDragging(true); setStartPos({ x: e.clientX, y: e.clientY }); };
    const handleMouseMove = (e: React.MouseEvent) => {
        if (!isDragging) return;
        const sensitivity = 0.5;
        let newX = currentPos.x + (startPos.x - e.clientX) * sensitivity;
        let newY = currentPos.y + (startPos.y - e.clientY) * sensitivity;
        newX = Math.max(0, Math.min(100, newX));
        newY = Math.max(0, Math.min(100, newY));
        setCurrentPos({ x: newX, y: newY });
        setStartPos({ x: e.clientX, y: e.clientY });
    };
    const handleMouseUp = () => { setIsDragging(false); onChange(`${currentPos.x.toFixed(1)}% ${currentPos.y.toFixed(1)}%`); };

    return (
        <div className={`w-full h-48 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden bg-gray-100 dark:bg-gray-900 relative ${imageUrl ? 'cursor-move' : 'cursor-default'} transition-colors duration-300`}
            onMouseDown={handleMouseDown} onMouseMove={handleMouseMove} onMouseUp={handleMouseUp} onMouseLeave={handleMouseUp}>
            {imageUrl ? (
                <div className="w-full h-full bg-cover bg-no-repeat transition-none select-none pointer-events-none"
                    style={{ backgroundImage: `url(${imageUrl})`, backgroundPosition: `${currentPos.x}% ${currentPos.y}%` }} />
            ) : (
                <div className="absolute inset-0 flex items-center justify-center text-gray-400 dark:text-gray-600 text-xs">Sem banner para ajustar</div>
            )}
            {imageUrl && (
                <div className="absolute bottom-2 right-2 bg-black/60 text-white text-[10px] px-2 py-1 rounded-md pointer-events-none backdrop-blur-sm">
                    {Math.round(currentPos.x)}% {Math.round(currentPos.y)}%
                </div>
            )}
        </div>
    );
}
