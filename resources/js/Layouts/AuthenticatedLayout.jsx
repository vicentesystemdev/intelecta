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
        <div className="min-h-screen bg-slate-50 text-slate-900 transition-colors dark:bg-slate-950 dark:text-slate-100 dark:[&_.bg-white]:bg-slate-900 dark:[&_.text-slate-900]:text-slate-100 dark:[&_.text-slate-800]:text-slate-200 dark:[&_.text-slate-700]:text-slate-300 dark:[&_.text-slate-600]:text-slate-400 dark:[&_.text-slate-500]:text-slate-400 dark:[&_.border-slate-200]:border-slate-700 dark:[&_.ring-slate-200]:ring-slate-800 dark:[&_[class*='ring-slate-200']]:ring-slate-800">
            <header className="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95">
                <div className="mx-auto flex h-18 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <Link
                        href="/"
                        className="flex min-w-0 items-center gap-3 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                    >
                        <ApplicationLogo className="h-10 w-10 shrink-0 text-indigo-700 shadow-sm dark:text-indigo-500" />
                        <span className="min-w-0">
                            <span className="block text-sm font-black tracking-[0.18em] text-indigo-800 dark:text-indigo-300">
                                INTELECTA
                            </span>
                            <span className="block truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                                Portal del Postulante
                            </span>
                        </span>
                    </Link>

                    <div className="flex items-center gap-2">
                        <Link
                            href="/"
                            className="hidden h-9 items-center gap-2 rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-indigo-700 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 sm:inline-flex"
                        >
                            <ArrowLeft className="h-3.5 w-3.5" />
                            Sitio público
                        </Link>
                        <ThemeToggle />
                        <div className="hidden items-center gap-2 rounded-xl bg-slate-100 py-1.5 pl-1.5 pr-3 dark:bg-slate-800 md:flex">
                            <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-700 text-[10px] font-bold text-white">
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
                            className="inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 dark:border-slate-700 dark:text-slate-300 dark:hover:border-rose-900 dark:hover:bg-rose-950/50 dark:hover:text-rose-300"
                        >
                            <LogOut className="h-3.5 w-3.5" />
                            <span className="hidden sm:inline">Cerrar sesión</span>
                        </Link>
                    </div>
                </div>
            </header>

            {header && (
                <div className="border-b border-slate-200/70 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div className="mx-auto flex max-w-7xl items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
                        <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
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
