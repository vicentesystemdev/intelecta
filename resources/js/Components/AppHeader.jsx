import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Link } from '@inertiajs/react';
import { LogOut, Menu, Globe } from 'lucide-react';

export default function AppHeader({
    title,
    subtitle,
    user,
    role = 'Administrador institucional',
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
        <header className="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl">
            <div className="flex min-h-24 items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div className="flex min-w-0 items-center gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        className="lg:hidden"
                        onClick={onMenuClick}
                        aria-label="Abrir navegación"
                    >
                        <Menu className="h-5 w-5" />
                    </Button>
                    <div className="min-w-0">
                        <p className="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">
                            INTELECTA
                        </p>
                        <h1 className="text-lg font-bold text-slate-900 sm:text-2xl">
                            {title}
                        </h1>
                        <p className="mt-0.5 hidden max-w-2xl text-sm text-slate-500 md:block">
                            {subtitle}
                        </p>
                    </div>
                </div>

                <div className="flex shrink-0 items-center gap-3">
                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        className="hidden md:inline-flex items-center gap-2 border-slate-200 text-slate-600 hover:text-slate-900"
                    >
                        <Link href="/">
                            <Globe className="h-4 w-4 text-indigo-500" />
                            Ver sitio público
                        </Link>
                    </Button>

                    <div className="hidden text-right sm:block">
                        <p className="max-w-48 truncate text-sm font-semibold text-slate-900">
                            {user?.name || 'Usuario INTELECTA'}
                        </p>
                        <Badge
                            variant="outline"
                            className="mt-1 border-indigo-100 bg-indigo-50 text-[10px] text-indigo-700"
                        >
                            {role}
                        </Badge>
                    </div>

                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 ring-4 ring-indigo-50">
                        {initials}
                    </div>

                    <Button
                        asChild
                        variant="ghost"
                        size="icon"
                        className="text-slate-500 hover:bg-red-50 hover:text-red-600"
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
