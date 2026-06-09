import AppHeader from '@/Components/AppHeader';
import AppSidebar from '@/Components/AppSidebar';
import useTheme from '@/Hooks/useTheme';
import { usePage } from '@inertiajs/react';
import { useState } from 'react';

const inheritedDarkStyles =
    'dark:[&_.bg-white]:bg-slate-900 dark:[&_.bg-slate-50]:bg-slate-900 dark:[&_.bg-slate-100]:bg-slate-800 ' +
    'dark:[&_.text-slate-950]:text-slate-50 dark:[&_.text-slate-900]:text-slate-100 dark:[&_.text-slate-800]:text-slate-200 ' +
    'dark:[&_.text-slate-700]:text-slate-300 dark:[&_.text-slate-600]:text-slate-400 dark:[&_.text-slate-500]:text-slate-400 ' +
    "dark:[&_.border-slate-200]:border-slate-700 dark:[&_.border-slate-100]:border-slate-800 dark:[&_.ring-slate-200]:ring-slate-800 dark:[&_[class*='border-slate-200']]:border-slate-700 dark:[&_[class*='border-slate-100']]:border-slate-800 dark:[&_[class*='ring-slate-200']]:ring-slate-800 dark:[&_[class*='bg-slate-50']]:bg-slate-800 " +
    'dark:[&_input]:border-slate-700 dark:[&_input]:bg-slate-900 dark:[&_input]:text-slate-100 ' +
    'dark:[&_select]:border-slate-700 dark:[&_select]:bg-slate-900 dark:[&_select]:text-slate-100 ' +
    'dark:[&_textarea]:border-slate-700 dark:[&_textarea]:bg-slate-900 dark:[&_textarea]:text-slate-100 ' +
    'dark:[&_a]:hover:bg-slate-800 dark:[&_tr]:border-slate-800 dark:[&_tr]:hover:bg-slate-800/50 dark:[&_th]:text-slate-400';

export default function AdminLayout({ title, subtitle, children, wide = false }) {
    const { auth } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { isDark, toggleTheme } = useTheme();

    const role = auth?.user?.email?.startsWith('superadmin')
        ? 'Superadministrador'
        : 'Administrador institucional';

    const containerClass = wide
        ? 'mx-auto max-w-[1500px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8 xl:px-10'
        : 'mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8 xl:px-10';

    return (
        <div className="flex h-screen overflow-hidden bg-slate-50 text-slate-950 transition-colors dark:bg-slate-950 dark:text-slate-100">
            <AppSidebar
                open={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />

            <div className="flex h-screen flex-1 flex-col overflow-hidden lg:pl-72">
                <AppHeader
                    title={title}
                    subtitle={subtitle}
                    user={auth?.user}
                    role={role}
                    isDark={isDark}
                    onToggleTheme={toggleTheme}
                    onMenuClick={() => setSidebarOpen(true)}
                />
                <main
                    className={`custom-scrollbar flex-1 overflow-y-auto ${inheritedDarkStyles}`}
                >
                    <div className={containerClass}>{children}</div>
                </main>
            </div>
        </div>
    );
}
