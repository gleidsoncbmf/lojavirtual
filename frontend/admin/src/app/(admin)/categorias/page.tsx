'use client';

import { useEffect, useState, useCallback } from 'react';
import { getCategories, createCategory, updateCategory, deleteCategory } from '@/lib/api';
import type { Category, CategoryFormData } from '@/types';
import { FolderOpen, Plus, Pencil, Trash2, X, Loader2, Save } from 'lucide-react';
import SingleImageUploader from '@/components/SingleImageUploader';

export default function CategoriesPage() {
    const [categories, setCategories] = useState<Category[]>([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing] = useState<Category | null>(null);
    const [saving, setSaving] = useState(false);
    const [deleting, setDeleting] = useState<number | null>(null);
    const [error, setError] = useState('');
    const [form, setForm] = useState<CategoryFormData>({
        name: '',
        description: '',
        image_url: '',
        sort_order: 0,
        active: true,
    });

    const fetchCategories = useCallback(async () => {
        try {
            const data = await getCategories();
            setCategories(data);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchCategories();
    }, [fetchCategories]);

    const resetForm = () => {
        setForm({ name: '', description: '', image_url: '', sort_order: 0, active: true });
        setEditing(null);
        setShowForm(false);
        setError('');
    };

    const openEdit = (cat: Category) => {
        setForm({
            name: cat.name,
            description: cat.description || '',
            image_url: cat.image_url || '',
            sort_order: cat.sort_order,
            active: cat.active,
        });
        setEditing(cat);
        setShowForm(true);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setSaving(true);
        try {
            if (editing) {
                await updateCategory(editing.id, form);
            } else {
                await createCategory(form);
            }
            resetForm();
            fetchCategories();
        } catch (err: unknown) {
            const message = (err as { response?: { data?: { message?: string } } })?.response?.data?.message;
            setError(message || 'Erro ao salvar categoria.');
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Tem certeza que deseja excluir esta categoria?')) return;
        setDeleting(id);
        try {
            await deleteCategory(id);
            fetchCategories();
        } catch (err) {
            console.error(err);
        } finally {
            setDeleting(null);
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value, type } = e.target;
        setForm((prev) => ({
            ...prev,
            [name]: type === 'number' ? Number(value) : value,
        }));
    };

    return (
        <div className="space-y-6 animate-in">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Categorias</h1>
                    <p className="text-gray-500 text-sm mt-1">Organize os produtos da sua loja</p>
                </div>
                <button
                    onClick={() => { resetForm(); setShowForm(true); }}
                    className="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-500 transition-all shadow-lg shadow-green-500/25"
                >
                    <Plus className="w-4 h-4" />
                    Nova Categoria
                </button>
            </div>

            {/* Form Modal */}
            {showForm && (
                <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl p-6 scale-in transition-colors duration-300">
                    <div className="flex items-center justify-between mb-5">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {editing ? 'Editar Categoria' : 'Nova Categoria'}
                        </h2>
                        <button onClick={resetForm} className="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <X className="w-5 h-5" />
                        </button>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        {error && (
                            <div className="bg-red-500/10 border border-red-500/20 text-red-500 dark:text-red-400 text-sm rounded-xl px-4 py-3">
                                {error}
                            </div>
                        )}

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="cat-name">Nome *</label>
                                <input id="cat-name" name="name" type="text" value={form.name} onChange={handleChange} required placeholder="Ex: Roupas" />
                            </div>
                            <div>
                                <label htmlFor="sort_order">Ordem</label>
                                <input id="sort_order" name="sort_order" type="number" min="0" value={form.sort_order || 0} onChange={handleChange} />
                            </div>
                            <div>
                                <SingleImageUploader
                                    value={form.image_url || ''}
                                    onChange={(url) => setForm((prev) => ({ ...prev, image_url: url }))}
                                    folder="categories"
                                    label="Imagem da Categoria"
                                />
                            </div>
                            <div>
                                <label htmlFor="cat-active">Status</label>
                                <select id="cat-active" name="active" value={form.active ? '1' : '0'} onChange={(e) => setForm((prev) => ({ ...prev, active: e.target.value === '1' }))}>
                                    <option value="1">Ativa</option>
                                    <option value="0">Inativa</option>
                                </select>
                            </div>
                            <div className="md:col-span-2">
                                <label htmlFor="cat-desc">Descrição</label>
                                <textarea id="cat-desc" name="description" rows={2} value={form.description || ''} onChange={handleChange} placeholder="Descrição da categoria..." />
                            </div>
                        </div>

                        <div className="flex justify-end gap-3 pt-2">
                            <button type="button" onClick={resetForm} className="px-4 py-2 rounded-xl text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition">
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                disabled={saving}
                                className="flex items-center gap-2 px-5 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-500 disabled:opacity-50 transition-all"
                            >
                                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                                {saving ? 'Salvando...' : editing ? 'Atualizar' : 'Criar'}
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* Table */}
            <div className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-2xl overflow-hidden transition-colors duration-300">
                {loading ? (
                    <div className="flex items-center justify-center py-20">
                        <Loader2 className="w-6 h-6 text-green-600 dark:text-green-500 animate-spin" />
                    </div>
                ) : categories.length === 0 ? (
                    <div className="text-center py-20 text-gray-500">
                        <FolderOpen className="w-10 h-10 mx-auto mb-3 opacity-30" />
                        <p>Nenhuma categoria encontrada.</p>
                    </div>
                ) : (
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-200 dark:border-white/5 text-gray-500 text-xs uppercase">
                                <th className="text-left px-5 py-3 font-medium">Nome</th>
                                <th className="text-left px-5 py-3 font-medium">Slug</th>
                                <th className="text-left px-5 py-3 font-medium">Ordem</th>
                                <th className="text-left px-5 py-3 font-medium">Produtos</th>
                                <th className="text-left px-5 py-3 font-medium">Status</th>
                                <th className="text-right px-5 py-3 font-medium">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            {categories.map((cat) => (
                                <tr key={cat.id} className="border-b border-gray-100 dark:border-white/[0.03] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                                    <td className="px-5 py-3 text-gray-900 dark:text-white font-medium">{cat.name}</td>
                                    <td className="px-5 py-3 text-gray-500">{cat.slug}</td>
                                    <td className="px-5 py-3 text-gray-600 dark:text-gray-400">{cat.sort_order}</td>
                                    <td className="px-5 py-3 text-gray-600 dark:text-gray-400">{cat.products_count ?? 0}</td>
                                    <td className="px-5 py-3">
                                        <span className={`px-2.5 py-1 rounded-lg text-xs font-medium ${cat.active ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-gray-500/20 text-gray-500 dark:text-gray-400'}`}>
                                            {cat.active ? 'Ativa' : 'Inativa'}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3">
                                        <div className="flex items-center justify-end gap-2">
                                            <button onClick={() => openEdit(cat)} className="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-green-600 dark:hover:text-green-400 transition">
                                                <Pencil className="w-4 h-4" />
                                            </button>
                                            <button
                                                onClick={() => handleDelete(cat.id)}
                                                disabled={deleting === cat.id}
                                                className="p-2 rounded-lg text-gray-400 hover:bg-red-500/10 hover:text-red-400 transition disabled:opacity-50"
                                            >
                                                {deleting === cat.id ? <Loader2 className="w-4 h-4 animate-spin" /> : <Trash2 className="w-4 h-4" />}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </div>
    );
}
