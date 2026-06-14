import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, ShieldAlert } from 'lucide-react';

export default function Forbidden() {
    const { auth } = usePage().props;
    const canViewDashboard =
        auth?.roles?.includes('Super Administrador') ||
        auth?.permissions?.includes('dashboard.ver');

    return (
        <>
            <Head title="Acceso no autorizado" />
            <main className="flex min-h-screen items-center justify-center bg-brand-bg px-4 py-10 text-text-main">
                <section className="w-full max-w-lg rounded-3xl border border-brand-border bg-brand-card p-6 text-center shadow-xl dark:shadow-black/20 sm:p-8">
                    <span className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-warning/10 text-brand-warning">
                        <ShieldAlert className="h-8 w-8" />
                    </span>
                    <p className="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-brand-secondary">
                        Acceso institucional
                    </p>
                    <h1 className="mt-2 text-2xl font-black text-text-main">
                        Módulo no autorizado
                    </h1>
                    <p className="mt-3 text-sm leading-6 text-text-muted">
                        No cuentas con permisos para acceder a este módulo. Consulta
                        con la administración del sistema si necesitas ampliar tu
                        acceso institucional.
                    </p>
                    <Link
                        href={canViewDashboard ? route('dashboard') : '/'}
                        className="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-brand-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-secondary/90"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        {canViewDashboard ? 'Volver al dashboard' : 'Volver al inicio'}
                    </Link>
                </section>
            </main>
        </>
    );
}
