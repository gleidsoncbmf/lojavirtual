'use client';

import { LogOut, User as UserIcon } from 'lucide-react';
import { useAuth } from '@/context/AuthContext';

export default function AdminHeader() {
    const { user, logout } = useAuth();

    return (
        <header className="sticky top-0 z-30 h-16 bg-[#0f111a]/80 backdrop-blur-xl border-b border-white/5 flex items-center justify-between px-6 ml-64">
            <div>
                <h2 className="text-white/50 text-sm">
                    Bem-vindo,{' '}
                    <span className="text-white font-medium">{user?.name || 'Admin'}</span>
                </h2>
            </div>

            <div className="flex items-center gap-3">
                <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5">
                    <div className="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <UserIcon className="w-4 h-4 text-white" />
                    </div>
                    <span className="text-sm text-gray-300">{user?.email}</span>
                </div>

                <button
                    onClick={logout}
                    className="flex items-center gap-2 px-3 py-1.5 rounded-lg text-gray-400 hover:bg-red-500/10 hover:text-red-400 transition-all duration-200 text-sm"
                >
                    <LogOut className="w-4 h-4" />
                    Sair
                </button>
            </div>
        </header>
    );
}
