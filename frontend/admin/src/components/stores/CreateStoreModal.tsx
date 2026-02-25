'use client';

import { useState } from 'react';
import { X, Loader2, Store as StoreIcon } from 'lucide-react';
import type { CreateStoreFormData } from '@/types';

interface Props {
    open: boolean;
    onClose: () => void;
    onCreated: () => void;
}

function slugify(text: string): string {
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '');
}

export default function CreateStoreModal({ open, onClose, onCreated }: Props) {
    const [form, setForm] = useState<CreateStoreFormData>({
        name: '',
        slug: '',
        email: '',
        owner_name: '',
        owner_email: '',
        owner_password: '',
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [autoSlug, setAutoSlug] = useState(true);

    if (!open) return null;

    const handleNameChange = (name: string) => {
        setForm((prev) => ({
            ...prev,
            name,
            ...(autoSlug ? { slug: slugify(name) } : {}),
        }));
    };

    const handleSlugChange = (slug: string) => {
        setAutoSlug(false);
        setForm((prev) => ({ ...prev, slug }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const { createPlatformStore } = await import('@/lib/api');
            await createPlatformStore(form);
            onCreated();
            onClose();
            setForm({
                name: '',
                slug: '',
                email: '',
                owner_name: '',
                owner_email: '',
                owner_password: '',
            });
            setAutoSlug(true);
        } catch (err: unknown) {
            const axiosErr = err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
            if (axiosErr.response?.data?.errors) {
                const msgs = Object.values(axiosErr.response.data.errors).flat();
                setError(msgs.join(' '));
            } else {
                setError(axiosErr.response?.data?.message || 'Erro ao criar loja.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={onClose} />

            <div className="relative w-full max-w-lg mx-4 bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl animate-in transition-colors duration-300">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200 dark:border-white/5">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-green-600 flex items-center justify-center">
                            <StoreIcon className="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 className="text-lg font-bold text-gray-900 dark:text-white">Nova Loja</h2>
                            <p className="text-xs text-gray-500">Preencha os dados da loja e do dono</p>
                        </div>
                    </div>
                    <button
                        onClick={onClose}
                        className="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-white transition p-1"
                    >
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="p-6 space-y-5">
                    {error && (
                        <div className="bg-red-500/10 border border-red-500/20 text-red-500 dark:text-red-400 text-sm rounded-xl px-4 py-3">
                            {error}
                        </div>
                    )}

                    {/* Store Section */}
                    <div className="space-y-3">
                        <p className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dados da Loja</p>

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label htmlFor="store-name">Nome da Loja</label>
                                <input
                                    id="store-name"
                                    type="text"
                                    value={form.name}
                                    onChange={(e) => handleNameChange(e.target.value)}
                                    placeholder="Minha Loja"
                                    required
                                    autoFocus
                                />
                            </div>
                            <div>
                                <label htmlFor="store-slug">Slug (URL)</label>
                                <input
                                    id="store-slug"
                                    type="text"
                                    value={form.slug}
                                    onChange={(e) => handleSlugChange(e.target.value)}
                                    placeholder="minha-loja"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label htmlFor="store-email">E-mail da Loja</label>
                            <input
                                id="store-email"
                                type="email"
                                value={form.email}
                                onChange={(e) => setForm({ ...form, email: e.target.value })}
                                placeholder="contato@minhaloja.com"
                            />
                        </div>
                    </div>

                    {/* Owner Section */}
                    <div className="space-y-3 pt-2 border-t border-gray-200 dark:border-white/5">
                        <p className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider pt-2">Dono da Loja</p>

                        <div>
                            <label htmlFor="owner-name">Nome do Dono</label>
                            <input
                                id="owner-name"
                                type="text"
                                value={form.owner_name}
                                onChange={(e) => setForm({ ...form, owner_name: e.target.value })}
                                placeholder="João Silva"
                                required
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label htmlFor="owner-email">E-mail do Dono</label>
                                <input
                                    id="owner-email"
                                    type="email"
                                    value={form.owner_email}
                                    onChange={(e) => setForm({ ...form, owner_email: e.target.value })}
                                    placeholder="dono@email.com"
                                    required
                                />
                            </div>
                            <div>
                                <label htmlFor="owner-password">Senha</label>
                                <input
                                    id="owner-password"
                                    type="password"
                                    value={form.owner_password}
                                    onChange={(e) => setForm({ ...form, owner_password: e.target.value })}
                                    placeholder="••••••••"
                                    required
                                    minLength={6}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Submit */}
                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full py-2.5 rounded-xl bg-green-600 text-white font-semibold text-sm hover:bg-green-500 transition-all duration-200 shadow-lg shadow-green-500/25 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        {loading ? (
                            <>
                                <Loader2 className="w-4 h-4 animate-spin" />
                                Criando...
                            </>
                        ) : (
                            'Criar Loja'
                        )}
                    </button>
                </form>
            </div>
        </div>
    );
}
