'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import {
    LayoutDashboard,
    Package,
    FolderOpen,
    ShoppingCart,
    Settings,
    Store,
    Building2,
    Truck,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { useAuth } from '@/context/AuthContext';

const navItems = [
    { href: '/dashboard', icon: LayoutDashboard, label: 'Dashboard' },
    { href: '/produtos', icon: Package, label: 'Produtos' },
    { href: '/categorias', icon: FolderOpen, label: 'Categorias' },
    { href: '/pedidos', icon: ShoppingCart, label: 'Pedidos' },
    { href: '/envio', icon: Truck, label: 'Envios' },
    { href: '/configuracoes', icon: Settings, label: 'Configurações' },
];

const platformItems = [
    { href: '/lojas', icon: Building2, label: 'Lojas' },
];

export default function Sidebar() {
    const pathname = usePathname();
    const { user } = useAuth();

    const allItems = [
        ...(user?.role === 'platform_admin' ? platformItems : []),
        ...navItems,
    ];

    return (
        <aside className="fixed left-0 top-0 z-40 h-screen w-64 bg-[#0f111a] border-r border-white/5 flex flex-col">
            {/* Logo */}
            <div className="flex items-center gap-3 px-6 py-5 border-b border-white/5">
                <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <Store className="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 className="text-white font-bold text-lg leading-tight">Admin</h1>
                    <p className="text-gray-500 text-[11px]">Painel de Gestão</p>
                </div>
            </div>

            {/* Navigation */}
            <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                {allItems.map((item) => {
                    const isActive = pathname === item.href ||
                        pathname.startsWith(item.href + '/');
                    const Icon = item.icon;

                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={cn(
                                'flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
                                isActive
                                    ? 'bg-indigo-500/15 text-indigo-400 shadow-sm shadow-indigo-500/10'
                                    : 'text-gray-400 hover:bg-white/5 hover:text-gray-200'
                            )}
                        >
                            <Icon className={cn('w-5 h-5', isActive && 'text-indigo-400')} />
                            {item.label}
                            {isActive && (
                                <span className="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse" />
                            )}
                        </Link>
                    );
                })}
            </nav>

            {/* Bottom */}
            <div className="px-4 py-4 border-t border-white/5">
                <p className="text-[11px] text-gray-600 text-center">
                    © {new Date().getFullYear()} Loja Virtual
                </p>
            </div>
        </aside>
    );
}

