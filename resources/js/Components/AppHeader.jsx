import ThemeToggle from '@/Components/ThemeToggle';
import { Button }  from '@/Components/ui/button';
import { Badge }   from '@/Components/ui/badge';
import { Link }    from '@inertiajs/react';
import { LogOut, Menu, Globe } from 'lucide-react';

export default function AppHeader({
    title,
    subtitle,
    user,
    role = 'Administrador institucional',
    isDark = false,
    onToggleTheme,
    onMenuClick,
}) {
    const initials =
        user?.name
            ?.split(' ')
            .map((part) => part[0])
            .slice(0, 2)
            .join('')
            .toUpperCase() || 'IN';

    return (
        <header className="
            sticky top-0 z-30
            border-b border-slate-200/80 bg-white/95 backdrop-blur-xl
            dark:border-slate-800 dark:bg-slate-950/95
        ">
            <div className="flex min-h-[72px] items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">

                {/* ── Left: hamburger + title ─────────────────────────── */}
                <div className="flex min-w-0 items-center gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        className="lg:hidden border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        onClick={onMenuClick}
                        aria-label="Abrir navegación"
                    >
                        <Menu className="h-5 w-5" />
                    </Button>
                    <div className="min-w-0">
                        <p className="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-400">
                            INTELECTA
                        </p>
                        <h1 className="text-lg font-bold text-slate-900 dark:text-slate-100 sm:text-xl leading-tight">
                            {title}
                        </h1>
                        <p className="mt-0.5 hidden max-w-2xl text-xs text-slate-500 dark:text-slate-400 md:block">
                            {subtitle}
                        </p>
                    </div>
                </div>

                {/* ── Right: actions + user ───────────────────────────── */}
                <div className="flex shrink-0 items-center gap-2">

                    {/* Theme toggle */}
                    <ThemeToggle isDark={isDark} onToggle={onToggleTheme} />

                    {/* Ver sitio público */}
                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        className="hidden md:inline-flex items-center gap-2
                            border-slate-200 text-slate-600 hover:text-slate-900
                            dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300
                            dark:hover:bg-slate-700 dark:hover:text-slate-100"
                    >
                        <Link href="/">
                            <Globe className="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                            Ver sitio público
                        </Link>
                    </Button>

                    {/* User name + role badge */}
                    <div className="hidden text-right sm:block">
                        <p className="max-w-48 truncate text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {user?.name || 'Usuario INTELECTA'}
                        </p>
                        <Badge
                            variant="outline"
                            className="mt-1 border-indigo-100 bg-indigo-50 text-[10px] text-indigo-700
                                dark:border-indigo-800 dark:bg-indigo-950 dark:text-indigo-300"
                        >
                            {role}
                        </Badge>
                    </div>

                    {/* Avatar */}
                    <div className="flex h-9 w-9 items-center justify-center rounded-full
                        bg-indigo-100 text-sm font-bold text-indigo-700 ring-4 ring-indigo-50
                        dark:bg-indigo-900 dark:text-indigo-300 dark:ring-indigo-950">
                        {initials}
                    </div>

                    {/* Logout */}
                    <Button
                        asChild
                        variant="ghost"
                        size="icon"
                        className="text-slate-500 hover:bg-red-50 hover:text-red-600
                            dark:text-slate-400 dark:hover:bg-red-950 dark:hover:text-red-400"
                    >
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            aria-label="Cerrar sesión"
                        >
                            <LogOut className="h-5 w-5" />
                        </Link>
                    </Button>
                </div>
            </div>
        </header>
    );
}
