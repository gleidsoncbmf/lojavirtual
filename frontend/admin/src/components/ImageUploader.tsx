'use client';

import { useState, useRef, useCallback } from 'react';
import { Upload, X, GripVertical, Loader2, ImagePlus } from 'lucide-react';
import { uploadProductImages } from '@/lib/api';

interface ImageUploaderProps {
    images: string[];
    onChange: (images: string[]) => void;
    maxFiles?: number;
}


export default function ImageUploader({ images, onChange, maxFiles }: ImageUploaderProps) {
    const [uploading, setUploading] = useState(false);
    const [dragOver, setDragOver] = useState(false);
    const [dragIndex, setDragIndex] = useState<number | null>(null);
    const [dragOverIndex, setDragOverIndex] = useState<number | null>(null);
    const [error, setError] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    const handleFiles = useCallback(async (files: FileList | File[]) => {
        let fileArray = Array.from(files).filter((f) =>
            ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(f.type)
        );

        if (fileArray.length === 0) {
            setError('Selecione imagens válidas (JPG, PNG ou WebP).');
            return;
        }

        if (maxFiles && (images.length + fileArray.length > maxFiles)) {
            setError(`Você só pode enviar no máximo ${maxFiles} imagens.`);
            return;
        }

        const oversized = fileArray.filter((f) => f.size > 5 * 1024 * 1024);
        if (oversized.length > 0) {
            setError(`${oversized.length} imagem(ns) excede(m) o limite de 5MB.`);
            return;
        }

        setError('');
        setUploading(true);
        try {
            const urls = await uploadProductImages(fileArray);
            onChange([...images, ...urls]);
        } catch {
            setError('Erro ao fazer upload das imagens. Tente novamente.');
        } finally {
            setUploading(false);
        }
    }, [images, onChange]);

    const handleDrop = useCallback(
        (e: React.DragEvent) => {
            e.preventDefault();
            setDragOver(false);
            if (e.dataTransfer.files.length > 0) {
                handleFiles(e.dataTransfer.files);
            }
        },
        [handleFiles]
    );

    const removeImage = (index: number) => {
        onChange(images.filter((_, i) => i !== index));
    };

    // Drag-to-reorder handlers
    const handleReorderDragStart = (index: number) => {
        setDragIndex(index);
    };

    const handleReorderDragOver = (e: React.DragEvent, index: number) => {
        e.preventDefault();
        setDragOverIndex(index);
    };

    const handleReorderDrop = (e: React.DragEvent, dropIndex: number) => {
        e.preventDefault();
        if (dragIndex === null || dragIndex === dropIndex) {
            setDragIndex(null);
            setDragOverIndex(null);
            return;
        }
        const reordered = [...images];
        const [moved] = reordered.splice(dragIndex, 1);
        reordered.splice(dropIndex, 0, moved);
        onChange(reordered);
        setDragIndex(null);
        setDragOverIndex(null);
    };

    const handleReorderDragEnd = () => {
        setDragIndex(null);
        setDragOverIndex(null);
    };

    return (
        <div className="space-y-3">
            <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                Imagens do Produto
            </label>

            {/* Drop zone */}
            <div
                onDragOver={(e) => {
                    e.preventDefault();
                    setDragOver(true);
                }}
                onDragLeave={() => setDragOver(false)}
                onDrop={handleDrop}
                onClick={() => inputRef.current?.click()}
                className={`
                    relative flex flex-col items-center justify-center gap-3
                    p-8 rounded-xl border-2 border-dashed cursor-pointer
                    transition-all duration-200
                    ${dragOver
                        ? 'border-green-500 bg-green-500/10'
                        : 'border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/[0.02] hover:border-gray-400 dark:hover:border-white/20 hover:bg-gray-100 dark:hover:bg-white/[0.04]'
                    }
                `}
            >
                {uploading ? (
                    <>
                        <Loader2 className="w-8 h-8 text-green-500 dark:text-green-400 animate-spin" />
                        <span className="text-sm text-gray-500 dark:text-gray-400">Enviando imagens...</span>
                    </>
                ) : (
                    <>
                        <div className="w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center">
                            <Upload className="w-6 h-6 text-green-500 dark:text-green-400" />
                        </div>
                        <div className="text-center">
                            <p className="text-sm text-gray-600 dark:text-gray-300">
                                Arraste imagens aqui ou{' '}
                                <span className="text-green-600 dark:text-green-400 font-semibold">
                                    clique para selecionar
                                </span>
                            </p>
                            <p className="text-xs text-gray-500 mt-1">
                                JPG, PNG ou WebP · Até 5MB cada
                            </p>
                        </div>
                    </>
                )}

                <input
                    ref={inputRef}
                    type="file"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    multiple
                    className="hidden"
                    onChange={(e) => {
                        if (e.target.files && e.target.files.length > 0) {
                            handleFiles(e.target.files);
                            e.target.value = '';
                        }
                    }}
                />
            </div>

            {error && (
                <p className="text-red-400 text-xs">{error}</p>
            )}

            {/* Image previews */}
            {images.length > 0 && (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    {images.map((url, index) => (
                        <div
                            key={url + index}
                            draggable
                            onDragStart={() => handleReorderDragStart(index)}
                            onDragOver={(e) => handleReorderDragOver(e, index)}
                            onDrop={(e) => handleReorderDrop(e, index)}
                            onDragEnd={handleReorderDragEnd}
                            className={`
                                group relative aspect-square rounded-xl overflow-hidden
                                border transition-all duration-200
                                ${dragOverIndex === index
                                    ? 'border-green-500 scale-105'
                                    : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20'
                                }
                                ${dragIndex === index ? 'opacity-40' : 'opacity-100'}
                            `}
                        >
                            <img
                                src={url}
                                alt={`Produto ${index + 1}`}
                                className="w-full h-full object-cover"
                            />

                            {/* Badge for first image */}
                            {index === 0 && (
                                <span className="absolute top-1.5 left-1.5 px-2 py-0.5 bg-green-600 text-white text-[10px] font-bold rounded-full uppercase tracking-wider">
                                    Principal
                                </span>
                            )}

                            {/* Overlay on hover */}
                            <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    className="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition cursor-grab"
                                    title="Arrastar para reordenar"
                                >
                                    <GripVertical className="w-4 h-4 text-white" />
                                </button>
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        removeImage(index);
                                    }}
                                    className="p-1.5 rounded-lg bg-red-500/80 hover:bg-red-500 transition"
                                    title="Remover imagem"
                                >
                                    <X className="w-4 h-4 text-white" />
                                </button>
                            </div>
                        </div>
                    ))}

                    {/* Add more button */}
                    {(!maxFiles || images.length < maxFiles) && (
                        <button
                            type="button"
                            onClick={() => inputRef.current?.click()}
                            className="aspect-square rounded-xl border-2 border-dashed border-gray-300 dark:border-white/10 hover:border-gray-400 dark:hover:border-white/20 bg-gray-50 dark:bg-white/[0.02] hover:bg-gray-100 dark:hover:bg-white/[0.04] flex flex-col items-center justify-center gap-1 transition-all"
                        >
                            <ImagePlus className="w-6 h-6 text-gray-500" />
                            <span className="text-xs text-gray-500">Adicionar</span>
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
