import AppHeader from '@/Components/AppHeader';
import AppSidebar from '@/Components/AppSidebar';
import { usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AdminLayout({ title, subtitle, children }) {
    const { auth } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const role = auth?.user?.email?.startsWith('superadmin')
        ? 'Superadministrador'
        : 'Administrador institucional';

    return (
        <div className="min-h-screen bg-slate-50 text-slate-950">
            <AppSidebar
                open={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />

            <div className="min-h-screen lg:pl-72">
                <AppHeader
                    title={title}
                    subtitle={subtitle}
                    user={auth?.user}
                    role={role}
                    onMenuClick={() => setSidebarOpen(true)}
                />
                <main className="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">{children}</main>
            </div>
        </div>
    );
}
