'use client';

import { useState, useRef } from 'react';
import { Upload, X, Loader2 } from 'lucide-react';
import { uploadProductImages } from '@/lib/api';

interface SingleImageUploaderProps {
    value: string;
    onChange: (url: string) => void;
    folder?: string;
    label?: string;
}

export default function SingleImageUploader({
    value,
    onChange,
    folder = 'categories',
    label = 'Imagem',
}: SingleImageUploaderProps) {
    const [uploading, setUploading] = useState(false);
    const [dragOver, setDragOver] = useState(false);
    const [error, setError] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    const handleFile = async (file: File) => {
        if (!['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type)) {
            setError('Formato inválido. Use JPG, PNG ou WebP.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            setError('Imagem excede o limite de 5MB.');
            return;
        }

        setError('');
        setUploading(true);
        try {
            const urls = await uploadProductImages([file], folder);
            onChange(urls[0]);
        } catch {
            setError('Erro ao fazer upload. Tente novamente.');
        } finally {
            setUploading(false);
        }
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setDragOver(false);
        if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
    };

    return (
        <div className="space-y-2">
            <label className="text-sm font-medium text-gray-300">{label}</label>

            {value ? (
                <div className="relative group w-fit">
                    <img
                        src={value}
                        alt="Preview"
                        className="w-32 h-32 object-cover rounded-xl border border-white/10"
                    />
                    <button
                        type="button"
                        onClick={() => onChange('')}
                        className="absolute -top-2 -right-2 p-1 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                    >
                        <X className="w-3.5 h-3.5" />
                    </button>
                    <button
                        type="button"
                        onClick={() => inputRef.current?.click()}
                        className="absolute inset-0 flex items-center justify-center bg-black/50 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity text-white text-xs font-medium"
                    >
                        Trocar
                    </button>
                </div>
            ) : (
                <div
                    onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                    onDragLeave={() => setDragOver(false)}
                    onDrop={handleDrop}
                    onClick={() => inputRef.current?.click()}
                    className={`
                        flex flex-col items-center justify-center gap-2 p-6
                        w-32 h-32 rounded-xl border-2 border-dashed cursor-pointer transition-all
                        ${dragOver
                            ? 'border-indigo-500 bg-indigo-500/10'
                            : 'border-white/10 bg-white/[0.02] hover:border-white/20 hover:bg-white/[0.04]'
                        }
                    `}
                >
                    {uploading ? (
                        <Loader2 className="w-6 h-6 text-indigo-400 animate-spin" />
                    ) : (
                        <>
                            <Upload className="w-5 h-5 text-gray-500" />
                            <span className="text-[10px] text-gray-500 text-center">Enviar imagem</span>
                        </>
                    )}
                </div>
            )}

            <input
                ref={inputRef}
                type="file"
                accept="image/jpeg,image/jpg,image/png,image/webp"
                className="hidden"
                onChange={(e) => {
                    if (e.target.files?.[0]) {
                        handleFile(e.target.files[0]);
                        e.target.value = '';
                    }
                }}
            />

            {error && <p className="text-red-400 text-xs">{error}</p>}
        </div>
    );
}
