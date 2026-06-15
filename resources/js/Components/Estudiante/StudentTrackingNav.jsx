import { Link } from '@inertiajs/react';
import { BookOpenCheck, FileChartColumn, Trophy } from 'lucide-react';

const items = [
    {
        label: 'Mis Evaluaciones',
        description: 'Instrumentos y resultados',
        icon: BookOpenCheck,
        route: 'estudiante.evaluaciones',
    },
    {
        label: 'Mi Ficha Académica',
        description: 'Seguimiento consolidado',
        icon: FileChartColumn,
        route: 'estudiante.ficha',
    },
    {
        label: 'Ranking Académico',
        description: 'Posición referencial',
        icon: Trophy,
        route: 'estudiante.ranking',
    },
];

export default function StudentTrackingNav() {
    return (
        <section className="rounded-2xl border border-brand-border bg-brand-card p-5 shadow-sm sm:p-6">
            <div className="mb-4">
                <p className="text-xs font-black uppercase tracking-[0.16em] text-brand-secondary">
                    Mi seguimiento académico
                </p>
                <p className="mt-1 text-sm text-text-muted">
                    Accede a tus evaluaciones, ficha consolidada y posición académica referencial.
                </p>
            </div>
            <div className="grid gap-3 md:grid-cols-3">
                {items.map(({ label, description, icon: Icon, route: routeName }) => {
                    const active = route().current(routeName);

                    return (
                        <Link
                            key={routeName}
                            href={route(routeName)}
                            className={`flex items-center gap-3 rounded-xl border p-4 transition ${
                                active
                                    ? 'border-brand-secondary bg-brand-secondary text-white shadow-sm'
                                    : 'border-brand-border bg-brand-bg text-text-main hover:border-brand-secondary/40 hover:bg-brand-secondary/5'
                            }`}
                        >
                            <span
                                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${
                                    active
                                        ? 'bg-white/15 text-white'
                                        : 'bg-brand-secondary/10 text-brand-secondary'
                                }`}
                            >
                                <Icon className="h-5 w-5" />
                            </span>
                            <span className="min-w-0">
                                <span className="block text-sm font-black">{label}</span>
                                <span
                                    className={`mt-0.5 block text-xs ${
                                        active ? 'text-white/75' : 'text-text-muted'
                                    }`}
                                >
                                    {description}
                                </span>
                            </span>
                        </Link>
                    );
                })}
            </div>
        </section>
    );
}
