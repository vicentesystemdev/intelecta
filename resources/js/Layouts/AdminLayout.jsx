import AppHeader   from '@/Components/AppHeader';
import AppSidebar  from '@/Components/AppSidebar';
import { usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

const THEME_KEY = 'intelecta-theme';

export default function AdminLayout({ title, subtitle, children, wide = false }) {
    const { auth } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [isDark, setIsDark] = useState(false);

    const role = auth?.user?.email?.startsWith('superadmin')
        ? 'Superadministrador'
        : 'Administrador institucional';

    // ─── Read saved preference on mount ──────────────────────────────────
    useEffect(() => {
        const saved = localStorage.getItem(THEME_KEY);
        const dark  = saved === 'dark';
        setIsDark(dark);
        document.documentElement.classList.toggle('dark', dark);
    }, []);

    // ─── Toggle and persist ───────────────────────────────────────────────
    const toggleTheme = () => {
        setIsDark(prev => {
            const next = !prev;
            document.documentElement.classList.toggle('dark', next);
            localStorage.setItem(THEME_KEY, next ? 'dark' : 'light');
            return next;
        });
    };

    // Max-width: use 1500px for wide pages (e.g. Reportes), 7xl for the rest.
    const containerClass = wide
        ? 'max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 py-6 lg:py-8'
        : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 py-6 lg:py-8';

    return (
        <div className="flex h-screen overflow-hidden bg-slate-50 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
            <AppSidebar
                open={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />

            <div className="flex-1 flex flex-col h-screen overflow-hidden lg:pl-72">
                <AppHeader
                    title={title}
                    subtitle={subtitle}
                    user={auth?.user}
                    role={role}
                    isDark={isDark}
                    onToggleTheme={toggleTheme}
                    onMenuClick={() => setSidebarOpen(true)}
                />
                <main className="flex-1 overflow-y-auto custom-scrollbar">
                    <div className={containerClass}>
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
