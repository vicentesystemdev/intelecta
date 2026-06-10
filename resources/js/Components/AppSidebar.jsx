import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    BookCopy,
    BookOpenCheck,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    ChartNoAxesCombined,
    ClipboardCheck,
    ClipboardList,
    FileChartColumn,
    FileQuestion,
    Globe,
    GraduationCap,
    LayoutDashboard,
    Layers3,
    Settings,
    ShieldAlert,
    ShieldCheck,
    SlidersHorizontal,
    Trophy,
    Users,
    UserRoundCheck,
    X,
} from 'lucide-react';

const navigationGroups = [
    {
        label: 'General',
        items: [
            {
                label: 'Dashboard',
                icon: LayoutDashboard,
                href: '/dashboard',
                routeName: 'dashboard',
            },
        ],
    },
    {
        label: 'Gestión Académica',
        items: [
            {
                label: 'Postulantes',
                icon: GraduationCap,
                href: '/postulantes',
                routeName: 'postulantes.*',
            },
            {
                label: 'Tutores Académicos',
                icon: UserRoundCheck,
                href: '/admin/gestion-academica/docentes',
                routeName: 'admin.docentes',
            },
            {
                label: 'Carreras',
                icon: BriefcaseBusiness,
                href: '/admin/gestion-academica/carreras',
                routeName: 'admin.carreras',
            },
            {
                label: 'Colegios',
                icon: Building2,
                href: '/admin/gestion-academica/colegios',
                routeName: 'admin.colegios',
            },
        ],
    },
    {
        label: 'Gestión Institucional',
        items: [
            {
                label: 'Programas Académicos',
                icon: BookOpenCheck,
                href: '/admin/institucional/programas',
                routeName: 'admin.institucional.programas.*',
            },
            {
                label: 'Grupos y Paralelos',
                icon: Layers3,
                href: '/admin/institucional/grupos',
                routeName: 'admin.institucional.grupos.*',
            },
            {
                label: 'Inscripciones',
                icon: ClipboardList,
                href: '/admin/institucional/inscripciones',
                routeName: 'admin.institucional.inscripciones.*',
            },
            {
                label: 'Calendario de Simulacros',
                icon: CalendarDays,
                href: '/admin/institucional/simulacros',
                routeName: 'admin.institucional.simulacros.*',
            },
            {
                label: 'Ranking Académico',
                icon: Trophy,
                href: '/admin/institucional/ranking',
                routeName: 'admin.institucional.ranking.*',
            },
        ],
    },
    {
        label: 'Evaluaciones',
        items: [
            {
                label: 'Plantillas',
                icon: BookCopy,
                href: '/plantillas-evaluacion',
                routeName: 'plantillas-evaluacion.*',
            },
            {
                label: 'Banco de Preguntas',
                icon: FileQuestion,
                href: '/preguntas',
                routeName: 'preguntas.*',
            },
            {
                label: 'Centro de Evaluaciones',
                icon: ClipboardCheck,
                href: '/admin/evaluaciones',
                routeName: 'admin.evaluaciones.index',
            },
            {
                label: 'Resultados y Seguimiento',
                icon: FileChartColumn,
                href: '/admin/evaluaciones/resultados',
                routeName: 'admin.evaluaciones.resultados',
            },
        ],
    },
    {
        label: 'Análisis',
        items: [
            {
                label: 'Reportes Académicos',
                icon: BarChart3,
                href: '/reportes-academicos',
                routeName: 'reportes-academicos.index',
            },
            {
                label: 'Learning Analytics',
                icon: ChartNoAxesCombined,
                href: '/admin/analisis/learning-analytics',
                routeName: 'admin.analisis.learning-analytics',
            },
            {
                label: 'Riesgo Académico',
                icon: ShieldAlert,
                href: '/admin/analisis/riesgo-academico',
                routeName: 'admin.analisis.riesgo-academico',
            },
        ],
    },
    {
        label: 'Sistema',
        items: [
            {
                label: 'Usuarios',
                icon: Users,
                href: '/admin/sistema/usuarios',
                routeName: 'admin.sistema.usuarios',
                permission: 'usuarios.ver',
            },
            {
                label: 'Roles y Permisos',
                icon: ShieldCheck,
                href: '/admin/sistema/roles-permisos',
                routeName: 'admin.sistema.roles-permisos',
                permission: 'roles.ver',
            },
            {
                label: 'Configuración',
                icon: Settings,
                href: '/admin/sistema/configuracion',
                routeName: 'admin.sistema.configuracion',
            },
        ],
    },
];

export default function AppSidebar({ open = false, onClose = () => {} }) {
    const permissions = usePage().props.auth.permissions || [];

    return (
        <>
            {open && (
                <button
                    type="button"
                    aria-label="Cerrar navegación"
                    className="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden dark:bg-slate-950/70"
                    onClick={onClose}
                />
            )}

            <aside
                className={`fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-brand-primary text-slate-300 shadow-2xl transition-transform duration-300 lg:translate-x-0 ${
                    open ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="flex h-20 items-center justify-between border-b border-white/10 px-6">
                    <Link href="/" className="flex items-center gap-3">
                        <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-secondary text-white shadow-lg">
                            <GraduationCap className="h-6 w-6" />
                        </span>
                        <span>
                            <span className="block text-lg font-bold tracking-[0.18em] text-white">
                                INTELECTA
                            </span>
                            <span className="block text-xs text-slate-400">
                                Gestión académica
                            </span>
                        </span>
                    </Link>

                    <button
                        type="button"
                        aria-label="Cerrar menú"
                        className="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden"
                        onClick={onClose}
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <nav className="flex-1 space-y-5 overflow-y-auto px-4 py-5 sidebar-scrollbar">
                    {navigationGroups.map((group) => (
                        <div key={group.label}>
                            <p className="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                                {group.label}
                            </p>
                            <div className="space-y-1">
                                {group.items
                                    .filter(
                                        ({ permission }) =>
                                            !permission ||
                                            permissions.includes(permission),
                                    )
                                    .map(
                                        ({
                                            label,
                                            icon: Icon,
                                            href,
                                            routeName,
                                        }) => {
                                            const active = routeName
                                                ? route().current(routeName)
                                                : false;

                                            return (
                                                <Link
                                                    key={label}
                                                    href={href}
                                                    onClick={onClose}
                                                    className={`group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition ${
                                                        active
                                                            ? 'bg-brand-secondary text-white font-semibold shadow-md'
                                                            : 'text-slate-400 hover:bg-white/5 hover:text-white'
                                                    }`}
                                                >
                                                    <Icon
                                                        className={`h-4.5 w-4.5 transition-colors ${
                                                            active
                                                                ? 'text-white'
                                                                : 'text-slate-400 group-hover:text-white'
                                                        }`}
                                                    />
                                                    {label}
                                                </Link>
                                            );
                                        },
                                    )}
                            </div>
                        </div>
                    ))}
                </nav>

                <div className="m-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div className="flex items-center gap-2">
                        <SlidersHorizontal className="h-4 w-4 text-brand-accent" />
                        <p className="text-xs font-semibold uppercase tracking-wider text-brand-accent">
                            Panel académico
                        </p>
                    </div>
                    <p className="mt-2 text-xs leading-5 text-slate-400">
                        Información consolidada para el seguimiento institucional.
                    </p>
                </div>
            </aside>
        </>
    );
}
