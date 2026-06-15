import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    BookCopy,
    BookOpenCheck,
    BriefcaseBusiness,
    Building2,
    CalendarCheck2,
    CalendarDays,
    ChartNoAxesCombined,
    ChevronDown,
    ClipboardList,
    FileChartColumn,
    FileQuestion,
    GraduationCap,
    LayoutDashboard,
    Layers3,
    Link2,
    ListChecks,
    Settings,
    ShieldCheck,
    SlidersHorizontal,
    Tags,
    Trophy,
    Users,
    UserRoundCheck,
    WalletCards,
    X,
} from 'lucide-react';
import { useState } from 'react';

const navigationGroups = [
    {
        label: 'INICIO',
        items: [
            { label: 'Dashboard', icon: LayoutDashboard, href: '/dashboard', routeName: 'dashboard', permission: 'dashboard.ver' },
        ],
    },
    {
        label: 'GESTIÓN INSTITUCIONAL',
        items: [
            { label: 'Programas Académicos', icon: BookOpenCheck, href: '/admin/institucional/programas', routeName: 'admin.institucional.programas.*', permission: 'programas.ver' },
            { label: 'Grupos y Paralelos', icon: Layers3, href: '/admin/institucional/grupos', routeName: 'admin.institucional.grupos.*', permission: 'grupos.ver' },
            { label: 'Tutores Académicos', icon: UserRoundCheck, href: '/admin/institucional/tutores', routeName: 'admin.institucional.tutores.*', permission: 'tutores.ver' },
            { label: 'Asignación de Tutores', icon: Link2, href: '/admin/institucional/asignacion-tutores', routeName: 'admin.institucional.asignacion-tutores.*', permission: 'asignaciones-tutores.ver' },
            { label: 'Inscripciones Académicas', icon: ClipboardList, href: '/admin/institucional/inscripciones', routeName: 'admin.institucional.inscripciones.*', permission: 'inscripciones.ver' },
            { label: 'Matrículas y Cuotas', icon: WalletCards, href: '/admin/institucional/matriculas-cuotas', routeName: 'admin.institucional.matriculas-cuotas.*', permission: 'matriculas-cuotas.ver' },
            { label: 'Habilitación Académica', icon: ShieldCheck, href: '/admin/institucional/habilitacion-academica', routeName: 'admin.institucional.habilitacion.*', permission: 'habilitacion-academica.ver' },
            { label: 'Control de Asistencia', icon: CalendarCheck2, href: '/admin/institucional/asistencia', routeName: 'admin.institucional.asistencia.*', permission: 'asistencia.ver' },
            { label: 'Calendario de Simulacros', icon: CalendarDays, href: '/admin/institucional/simulacros', routeName: 'admin.institucional.simulacros.*', permission: 'simulacros.ver' },
        ],
    },
    {
        label: 'GESTIÓN DE POSTULANTES',
        items: [
            { label: 'Postulantes', icon: GraduationCap, href: '/postulantes', routeName: 'postulantes.*', permission: 'postulantes.ver' },
            { label: 'Ficha Académica', icon: FileChartColumn, href: '/admin/institucional/ficha-academica', routeName: 'admin.institucional.ficha.*', permission: 'ficha-academica.ver' },
            { label: 'Ranking Académico', icon: Trophy, href: '/admin/institucional/ranking', routeName: 'admin.institucional.ranking.*', permission: 'ranking.ver' },
        ],
    },
    {
        label: 'GESTIÓN EVALUATIVA',
        items: [
            { label: 'Materias', icon: BookOpenCheck, href: '/admin/evaluaciones/materias', routeName: 'admin.evaluaciones.materias', permission: 'materias.ver' },
            { label: 'Áreas de Conocimiento', icon: Layers3, href: '/areas-conocimiento', routeName: 'areas-conocimiento.*', permission: 'areas.ver' },
            { label: 'Temas Académicos', icon: Tags, href: '/temas', routeName: 'temas.*', permission: 'temas.ver' },
            { label: 'Banco de Preguntas', icon: FileQuestion, href: '/preguntas', routeName: 'preguntas.*', permission: 'preguntas.ver' },
            { label: 'Plantillas de Evaluación', icon: BookCopy, href: '/plantillas-evaluacion', routeName: 'plantillas-evaluacion.*', permission: 'plantillas.ver' },
            { label: 'Resultados Académicos', icon: ListChecks, href: '/admin/evaluaciones/resultados', routeName: 'admin.evaluaciones.resultados', permission: 'resultados.ver' },
        ],
    },
    {
        label: 'REPORTES Y ANÁLISIS',
        items: [
            { label: 'Reportes Académicos', icon: BarChart3, href: '/reportes-academicos', routeName: 'reportes-academicos.index', permission: 'reportes.ver' },
            { label: 'Indicadores de Desempeño', icon: FileChartColumn, href: '/admin/analisis/riesgo-academico', routeName: 'admin.analisis.riesgo-academico', permission: 'indicadores.ver' },
            { label: 'Learning Analytics', icon: ChartNoAxesCombined, href: '/admin/analisis/learning-analytics', routeName: 'admin.analisis.learning-analytics', permission: 'learning_analytics.ver' },
        ],
    },
    {
        label: 'ADMINISTRACIÓN DEL SISTEMA',
        items: [
            { label: 'Usuarios', icon: Users, href: '/admin/sistema/usuarios', routeName: 'admin.sistema.usuarios', permission: 'usuarios.ver' },
            { label: 'Roles y Permisos', icon: ShieldCheck, href: '/admin/sistema/roles-permisos', routeName: 'admin.sistema.roles-permisos', permission: 'roles-permisos.ver' },
            { label: 'Configuración', icon: Settings, href: '/admin/sistema/configuracion', routeName: 'admin.sistema.configuracion', permission: 'configuracion.ver' },
        ],
    },
];

const groupIsActive = (group) =>
    group.items.some(({ routeName }) => routeName && route().current(routeName));

export default function AppSidebar({ open = false, onClose = () => {} }) {
    const auth = usePage().props.auth || {};
    const permissions = auth.permissions || [];
    const isSuperAdministrator =
        auth.isSuperAdministrator ||
        (auth.roles || []).includes('Super Administrador');
    const visibleGroups = navigationGroups
        .map((group) => ({
            ...group,
            items: group.items.filter(
                ({ permission }) =>
                    isSuperAdministrator ||
                    !permission ||
                    permissions.includes(permission),
            ),
        }))
        .filter((group) => group.items.length > 0);

    const [expanded, setExpanded] = useState(() =>
        Object.fromEntries(
            visibleGroups.map((group) => [
                group.label,
                group.label === 'INICIO' || groupIsActive(group),
            ]),
        ),
    );

    const toggleGroup = (label) => {
        setExpanded((current) => ({ ...current, [label]: !current[label] }));
    };

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
                            <span className="block text-lg font-bold tracking-[0.18em] text-white">INTELECTA</span>
                            <span className="block text-xs text-slate-400">Gestión académica</span>
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

                <nav className="flex-1 space-y-2 overflow-y-auto px-4 py-5 sidebar-scrollbar">
                    {visibleGroups.map((group) => {
                        const active = groupIsActive(group);
                        const isOpen = expanded[group.label] || active;

                        return (
                            <section key={group.label} className="rounded-xl">
                                <button
                                    type="button"
                                    aria-expanded={isOpen}
                                    onClick={() => toggleGroup(group.label)}
                                    className={`flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-[0.16em] transition ${
                                        active ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white'
                                    }`}
                                >
                                    <span>{group.label}</span>
                                    <ChevronDown className={`h-4 w-4 transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`} />
                                </button>
                                <div
                                    className={`grid transition-[grid-template-rows,opacity] duration-200 ${
                                        isOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'
                                    }`}
                                >
                                    <div className="overflow-hidden">
                                        <div className="space-y-1 pb-2 pt-1">
                                            {group.items.map(({ label, icon: Icon, href, routeName }) => {
                                                const itemActive = routeName ? route().current(routeName) : false;
                                                return (
                                                    <Link
                                                        key={label}
                                                        href={href}
                                                        onClick={onClose}
                                                        className={`group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition ${
                                                            itemActive
                                                                ? 'bg-brand-secondary font-semibold text-white shadow-md'
                                                                : 'text-slate-400 hover:bg-white/5 hover:text-white'
                                                        }`}
                                                    >
                                                        <Icon className={`h-4.5 w-4.5 shrink-0 ${itemActive ? 'text-white' : 'text-slate-400 group-hover:text-white'}`} />
                                                        <span className="leading-5">{label}</span>
                                                    </Link>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </div>
                            </section>
                        );
                    })}
                </nav>

                <div className="m-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div className="flex items-center gap-2">
                        <SlidersHorizontal className="h-4 w-4 text-brand-accent" />
                        <p className="text-xs font-semibold uppercase tracking-wider text-brand-accent">Panel académico</p>
                    </div>
                    <p className="mt-2 text-xs leading-5 text-slate-400">
                        Información consolidada para el seguimiento institucional.
                    </p>
                </div>
            </aside>
        </>
    );
}
