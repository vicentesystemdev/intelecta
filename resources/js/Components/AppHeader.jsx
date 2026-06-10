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
                        <p className="text-xs font-semibold uppercase tracking-[0.16em] text-brand-secondary">
                            INTELECTA
                        </p>
                        <h1 className="text-lg font-bold text-text-main sm:text-xl leading-tight">
                            {title}
                        </h1>
                        <p className="mt-0.5 hidden max-w-2xl text-xs text-text-muted md:block">
                            {subtitle}
                        </p>
                    </div>
                </div>

                {/* ── Right: actions + user ───────────────────────────── */}
                <div className="flex shrink-0 items-center gap-2">

                    {/* Ver sitio público */}
                    <Button
                        asChild
                        variant="ghost"
                        className="inline-flex h-9 w-9 p-0 items-center justify-center rounded-xl border border-brand-border bg-brand-card text-text-muted transition duration-200 hover:bg-brand-border/30 hover:text-brand-secondary"
                        title="Ver sitio público"
                        aria-label="Ver sitio público"
                    >
                        <Link href="/">
                            <Globe className="h-4 w-4" aria-hidden="true" />
                        </Link>
                    </Button>

                    {/* Theme toggle */}
                    <ThemeToggle isDark={isDark} onToggle={onToggleTheme} />

                    {/* User name + role badge */}
                    <div className="hidden text-right sm:block">
                        <p className="max-w-48 truncate text-sm font-semibold text-text-main">
                            {user?.name || 'Usuario INTELECTA'}
                        </p>
                        <Badge
                            variant="outline"
                            className="mt-1 border-brand-secondary/20 bg-brand-secondary/10 text-[10px] text-brand-secondary"
                        >
                            {role}
                        </Badge>
                    </div>

                    {/* Avatar */}
                    <div className="flex h-9 w-9 items-center justify-center rounded-full
                        bg-brand-secondary/10 text-sm font-bold text-brand-secondary ring-4 ring-brand-secondary/5">
                        {initials}
                    </div>

                    {/* Logout */}
                    <Button
                        asChild
                        variant="ghost"
                        size="icon"
                        className="text-text-muted hover:bg-brand-border/30 hover:text-brand-secondary transition-colors duration-200"
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
