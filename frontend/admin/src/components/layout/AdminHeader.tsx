'use client';

import { LogOut, User as UserIcon, Sun, Moon } from 'lucide-react';
import { useAuth } from '@/context/AuthContext';
import { useTheme } from '@/context/ThemeContext';

export default function AdminHeader() {
    const { user, logout } = useAuth();
    const { theme, toggleTheme } = useTheme();

    return (
        <header className="sticky top-0 z-30 h-16 bg-white/80 dark:bg-[#0f111a]/80 backdrop-blur-xl border-b border-gray-200 dark:border-white/5 flex items-center justify-between px-6 ml-64 transition-colors duration-300">
            <div>
                <h2 className="text-gray-500 dark:text-white/50 text-sm">
                    Bem-vindo,{' '}
                    <span className="text-gray-900 dark:text-white font-medium">{user?.name || 'Admin'}</span>
                </h2>
            </div>

            <div className="flex items-center gap-3">
                {/* Theme Toggle */}
                <button
                    onClick={toggleTheme}
                    className="flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 text-gray-600 dark:text-gray-400 transition-all duration-200"
                    title={theme === 'dark' ? 'Tema Claro' : 'Tema Escuro'}
                >
                    {theme === 'dark' ? (
                        <Sun className="w-4 h-4" />
                    ) : (
                        <Moon className="w-4 h-4" />
                    )}
                </button>

                <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-white/5">
                    <div className="w-7 h-7 rounded-lg bg-green-600 flex items-center justify-center">
                        <UserIcon className="w-4 h-4 text-white" />
                    </div>
                    <span className="text-sm text-gray-700 dark:text-gray-300">{user?.email}</span>
                </div>

                <button
                    onClick={logout}
                    className="flex items-center gap-2 px-3 py-1.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-red-500/10 hover:text-red-500 dark:hover:text-red-400 transition-all duration-200 text-sm"
                >
                    <LogOut className="w-4 h-4" />
                    Sair
                </button>
            </div>
        </header>
    );
}
