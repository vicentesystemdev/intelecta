import ApplicationLogo from '@/Components/ApplicationLogo';
import ThemeToggle from '@/Components/ThemeToggle';
import useTheme from '@/Hooks/useTheme';
import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft, LogOut, UserRound } from 'lucide-react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;
    useTheme();

    const initials = user.name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

    return (
        <div className="min-h-screen bg-brand-bg text-text-main transition-colors duration-300 dark:[&_.bg-white]:bg-brand-card dark:[&_.text-slate-900]:text-text-main dark:[&_.text-slate-800]:text-text-main dark:[&_.text-slate-700]:text-text-muted dark:[&_.text-slate-600]:text-text-muted dark:[&_.text-slate-500]:text-text-muted dark:[&_.border-slate-200]:border-brand-border dark:[&_.ring-slate-200]:ring-brand-border dark:[&_[class*='ring-slate-200']]:ring-brand-border">
            <header className="sticky top-0 z-50 border-b border-brand-border bg-brand-card/95 backdrop-blur">
                <div className="mx-auto flex h-18 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <Link
                        href="/"
                        className="flex min-w-0 items-center gap-3 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                    >
                        <ApplicationLogo className="h-10 w-10 shrink-0 text-brand-secondary shadow-sm" />
                        <span className="min-w-0">
                            <span className="block text-sm font-black tracking-[0.18em] text-brand-secondary">
                                INTELECTA
                            </span>
                            <span className="block truncate text-xs font-medium text-text-muted">
                                Portal del Postulante
                            </span>
                        </span>
                    </Link>

                    <div className="flex items-center gap-2">
                        <Link
                            href="/"
                            className="hidden h-9 items-center gap-2 rounded-xl border border-brand-border px-3 text-xs font-semibold text-text-main transition hover:bg-brand-border/30 hover:text-brand-secondary sm:inline-flex"
                        >
                            <ArrowLeft className="h-3.5 w-3.5" />
                            Sitio público
                        </Link>
                        <ThemeToggle />
                        <div className="hidden items-center gap-2 rounded-xl bg-brand-border/20 py-1.5 pl-1.5 pr-3 md:flex">
                            <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-secondary text-[10px] font-bold text-white">
                                {initials}
                            </span>
                            <span className="max-w-36 truncate text-xs font-semibold">
                                {user.name}
                            </span>
                        </div>
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            aria-label="Cerrar sesión"
                            className="inline-flex h-9 items-center gap-2 rounded-xl border border-brand-border px-3 text-xs font-semibold text-text-main transition hover:border-brand-border hover:bg-brand-border/30 hover:text-brand-secondary"
                        >
                            <LogOut className="h-3.5 w-3.5" />
                            <span className="hidden sm:inline">Cerrar sesión</span>
                        </Link>
                    </div>
                </div>
            </header>

            {header && (
                <div className="border-b border-brand-border bg-brand-card">
                    <div className="mx-auto flex max-w-7xl items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
                        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-secondary/10 text-brand-secondary">
                            <UserRound className="h-4 w-4" />
                        </span>
                        {header}
                    </div>
                </div>
            )}

            <main>{children}</main>
        </div>
    );
}
