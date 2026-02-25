'use client';

import { useState } from 'react';
import { ProductVariation } from '@/types';
import ImageUploader from './ImageUploader';
import { Trash2, Plus, ChevronDown, ChevronUp } from 'lucide-react';

interface ProductVariationsProps {
    variations: ProductVariation[];
    onChange: (variations: ProductVariation[]) => void;
}

type VariationType = 'adultos' | 'criancas' | 'calcados';

const VARIATION_OPTIONS: Record<VariationType, string[]> = {
    adultos: ['PP', 'P', 'M', 'G', 'GG', 'XG'],
    criancas: ['2', '4', '6', '8', '10', '12', '14'],
    calcados: Array.from({ length: 12 }, (_, i) => String(34 + i)), // 34 to 45
};

const VARIATION_LABELS: Record<VariationType, string> = {
    adultos: 'Adulto',
    criancas: 'Infantil',
    calcados: 'Calçados',
};

export default function ProductVariations({ variations, onChange }: ProductVariationsProps) {
    const [selectedType, setSelectedType] = useState<VariationType>('adultos');
    const [expanded, setExpanded] = useState<Set<string>>(new Set());

    const handleTypeChange = (type: VariationType) => {
        setSelectedType(type);
    };

    const toggleVariation = (name: string) => {
        const exists = variations.some((v) => v.name === name);
        if (exists) {
            onChange(variations.filter((v) => v.name !== name));
            const newExpanded = new Set(expanded);
            newExpanded.delete(name);
            setExpanded(newExpanded);
        } else {
            onChange([
                ...variations,
                {
                    name,
                    price: null,
                    stock: 0,
                    sku: '',
                    image: null,
                },
            ]);
            // Auto expand when adding
            const newExpanded = new Set(expanded);
            newExpanded.add(name);
            setExpanded(newExpanded);
        }
    };

    const updateVariation = (name: string, field: keyof ProductVariation, value: any) => {
        onChange(
            variations.map((v) => {
                if (v.name === name) {
                    return { ...v, [field]: value };
                }
                return v;
            })
        );
    };

    const toggleExpand = (name: string) => {
        const newExpanded = new Set(expanded);
        if (newExpanded.has(name)) {
            newExpanded.delete(name);
        } else {
            newExpanded.add(name);
        }
        setExpanded(newExpanded);
    };

    return (
        <div className="space-y-4">
            <h3 className="text-lg font-medium text-gray-900 dark:text-white">Variações</h3>

            {/* Type Selector */}
            <div className="flex flex-wrap gap-2">
                {(Object.keys(VARIATION_OPTIONS) as VariationType[]).map((type) => (
                    <button
                        key={type}
                        type="button"
                        onClick={() => handleTypeChange(type)}
                        className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${selectedType === type
                            ? 'bg-green-600 text-white'
                            : 'bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10 hover:text-gray-700 dark:hover:text-white'
                            }`}
                    >
                        {VARIATION_LABELS[type]}
                    </button>
                ))}
            </div>

            {/* Options Grid */}
            <div className="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2 bg-gray-50 dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-xl p-4 transition-colors duration-300">
                {VARIATION_OPTIONS[selectedType].map((size) => {
                    const isSelected = variations.some((v) => v.name === size);
                    return (
                        <button
                            key={size}
                            type="button"
                            onClick={() => toggleVariation(size)}
                            className={`flex items-center justify-center p-2 rounded-lg text-sm font-medium border transition-all ${isSelected
                                ? 'bg-green-600/20 border-green-500/50 text-green-600 dark:text-green-400'
                                : 'bg-gray-100 dark:bg-white/5 border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/10'
                                }`}
                        >
                            {size}
                        </button>
                    );
                })}
            </div>

            {/* Selected Variations Details */}
            <div className="space-y-3">
                {variations.map((variation) => (
                    <div
                        key={variation.name}
                        className="bg-white dark:bg-[#0f111a] border border-gray-200 dark:border-white/5 rounded-xl overflow-hidden transition-colors duration-300"
                    >
                        <div
                            className="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            onClick={() => toggleExpand(variation.name)}
                        >
                            <div className="flex items-center gap-3">
                                <span className="flex items-center justify-center w-8 h-8 rounded-lg bg-green-500/10 text-green-600 dark:text-green-400 font-bold text-sm">
                                    {variation.name}
                                </span>
                                <div className="text-sm text-gray-500 dark:text-gray-400">
                                    {variation.stock} un. • {variation.price ? `R$ ${variation.price}` : 'Preço padrão'}
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        toggleVariation(variation.name);
                                    }}
                                    className="p-1.5 text-gray-500 hover:text-red-400 transition-colors"
                                >
                                    <Trash2 className="w-4 h-4" />
                                </button>
                                {expanded.has(variation.name) ? (
                                    <ChevronUp className="w-4 h-4 text-gray-500" />
                                ) : (
                                    <ChevronDown className="w-4 h-4 text-gray-500" />
                                )}
                            </div>
                        </div>

                        {expanded.has(variation.name) && (
                            <div className="p-4 border-t border-gray-200 dark:border-white/5 bg-gray-50 dark:bg-black/20 space-y-4 transition-colors duration-300">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Preço (Opcional)
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            value={variation.price || ''}
                                            onChange={(e) =>
                                                updateVariation(
                                                    variation.name,
                                                    'price',
                                                    e.target.value ? parseFloat(e.target.value) : null
                                                )
                                            }
                                            placeholder="Use preço do produto"
                                            className="w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-green-500/50 transition-colors"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-400 mb-1">
                                            Estoque
                                        </label>
                                        <input
                                            type="number"
                                            value={variation.stock}
                                            onChange={(e) =>
                                                updateVariation(variation.name, 'stock', parseInt(e.target.value) || 0)
                                            }
                                            className="w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-green-500/50 transition-colors"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block text-xs font-medium text-gray-400 mb-1">SKU</label>
                                        <input
                                            type="text"
                                            value={variation.sku || ''}
                                            onChange={(e) => updateVariation(variation.name, 'sku', e.target.value)}
                                            className="w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-green-500/50 transition-colors"
                                        />
                                    </div>
                                </div>

                                {/* Weight & Dimensions per variation */}
                                <div className="mt-3">
                                    <p className="text-xs font-medium text-gray-500 mb-2">📦 Peso e Dimensões</p>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <div>
                                            <label className="block text-xs text-gray-500 mb-1">Peso (g)</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={variation.weight || ''}
                                                onChange={(e) => updateVariation(variation.name, 'weight', e.target.value ? parseFloat(e.target.value) : null)}
                                                placeholder="300"
                                                className="w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-green-500/50 transition-colors"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs text-gray-500 mb-1">Comp. (cm)</label>
                                            <input
                                                type="number"
                                                step="0.1"
                                                min="0"
                                                value={variation.length || ''}
                                                onChange={(e) => updateVariation(variation.name, 'length', e.target.value ? parseFloat(e.target.value) : null)}
                                                placeholder="20"
                                                className="w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-green-500/50 transition-colors"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs text-gray-500 mb-1">Larg. (cm)</label>
                                            <input
                                                type="number"
                                                step="0.1"
                                                min="0"
                                                value={variation.width || ''}
                                                onChange={(e) => updateVariation(variation.name, 'width', e.target.value ? parseFloat(e.target.value) : null)}
                                                placeholder="15"
                                                className="w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-green-500/50 transition-colors"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs text-gray-500 mb-1">Alt. (cm)</label>
                                            <input
                                                type="number"
                                                step="0.1"
                                                min="0"
                                                value={variation.height || ''}
                                                onChange={(e) => updateVariation(variation.name, 'height', e.target.value ? parseFloat(e.target.value) : null)}
                                                placeholder="5"
                                                className="w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-green-500/50 transition-colors"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                                        Imagem da Variação
                                    </label>
                                    <ImageUploader
                                        images={variation.image ? [variation.image] : []}
                                        onChange={(imgs) =>
                                            updateVariation(variation.name, 'image', imgs.length > 0 ? imgs[0] : null)
                                        }
                                        maxFiles={1}
                                    />
                                </div>
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
