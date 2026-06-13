import MetricCard from '@/Components/MetricCard';
import StatusBadge from '@/Components/StatusBadge';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Progress } from '@/Components/ui/progress';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    BarChart3,
    BookCopy,
    BookOpenCheck,
    BrainCircuit,
    CalendarDays,
    CalendarCheck2,
    FileQuestion,
    GraduationCap,
    Layers3,
    LibraryBig,
    ShieldAlert,
    ShieldCheck,
    Tags,
    Users,
    UserRoundCheck,
    UserRoundX,
    Waypoints,
    Link2,
    WalletCards,
    CircleDollarSign,
    BadgeCheck,
} from 'lucide-react';

const matterStyles = {
    MAT: {
        progress:
            '[&_[data-slot=progress-indicator]]:bg-brand-primary',
        icon: 'bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20 dark:text-slate-200',
    },
    FIS: {
        progress: '[&_[data-slot=progress-indicator]]:bg-brand-secondary',
        icon: 'bg-brand-secondary/10 text-brand-secondary dark:bg-brand-secondary/20 dark:text-brand-secondary',
    },
    QMC: {
        progress: '[&_[data-slot=progress-indicator]]:bg-brand-accent',
        icon: 'bg-brand-accent/10 text-brand-primary dark:bg-brand-accent/20 dark:text-brand-accent',
    },
    PAA: {
        progress:
            '[&_[data-slot=progress-indicator]]:bg-slate-600',
        icon: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    },
};

const coverageBadge = {
    Consolidada:
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300',
    'En ampliación':
        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300',
    'Base diagnóstica':
        'border-brand-secondary/30 bg-brand-secondary/5 text-brand-secondary dark:border-brand-secondary/40 dark:bg-brand-secondary/10 dark:text-brand-secondary',
};

const quickLinks = [
    { label: 'Banco de Preguntas', href: '/preguntas', icon: FileQuestion },
    { label: 'Plantillas', href: '/plantillas-evaluacion', icon: BookCopy },
    { label: 'Reportes Académicos', href: '/reportes-academicos', icon: BarChart3 },
    { label: 'Postulantes', href: '/postulantes', icon: GraduationCap },
    { label: 'Usuarios', href: '/admin/sistema/usuarios', icon: Users },
    {
        label: 'Roles y Permisos',
        href: '/admin/sistema/roles-permisos',
        icon: ShieldCheck,
    },
];

const shortMatterLabel = (name) => {
    if (name?.includes('Razonamiento Académico')) return 'PAA';
    return name || 'Sin materia';
};

function SectionHeading({ eyebrow, title, description }) {
    return (
        <div className="mb-4">
            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-brand-secondary dark:text-brand-secondary">
                {eyebrow}
            </p>
            <h2 className="mt-1 text-xl font-bold text-slate-900 dark:text-slate-100">
                {title}
            </h2>
            <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {description}
            </p>
        </div>
    );
}

export default function Dashboard({
    metricas = {},
    coberturaMaterias = [],
    preguntasSinMateria = 0,
    plantillasRecientes = [],
    postulantesPorCarrera = [],
    gestionInstitucional = {},
}) {
    const metrics = [
        {
            title: 'Postulantes registrados',
            value: String(metricas.postulantes ?? 0),
            detail: 'Perfiles preuniversitarios registrados',
            trend: 'neutral',
            icon: GraduationCap,
            accent: 'primary',
        },
        {
            title: 'Materias académicas',
            value: String(metricas.materias ?? 0),
            detail: 'Estructura curricular institucional',
            trend: 'neutral',
            icon: LibraryBig,
            accent: 'secondary',
        },
        {
            title: 'Banco de preguntas',
            value: String(metricas.preguntas ?? 0),
            detail: 'Reactivos disponibles para evaluación',
            trend: 'neutral',
            icon: FileQuestion,
            accent: 'accent',
        },
        {
            title: 'Plantillas activas',
            value: String(metricas.plantillasActivas ?? 0),
            detail: 'Instrumentos disponibles para aplicación',
            trend: 'neutral',
            icon: BookOpenCheck,
            accent: 'slate',
        },
        {
            title: 'Áreas curriculares',
            value: String(metricas.areas ?? 0),
            detail: 'Organización por campo de conocimiento',
            trend: 'neutral',
            icon: Layers3,
            accent: 'slate',
        },
        {
            title: 'Temas evaluables',
            value: String(metricas.temas ?? 0),
            detail: 'Contenidos clasificados en el banco',
            trend: 'neutral',
            icon: Tags,
            accent: 'amber',
        },
    ];

    const totalPostulantesCarrera = postulantesPorCarrera.reduce(
        (sum, item) => sum + Number(item.total || 0),
        0,
    );

    const institutionalMetrics = [
        {
            label: 'Programas activos',
            value: gestionInstitucional.programasActivos ?? 0,
            icon: BookOpenCheck,
        },
        {
            label: 'Grupos activos',
            value: gestionInstitucional.gruposActivos ?? 0,
            icon: Layers3,
        },
        {
            label: 'Postulantes inscritos',
            value: gestionInstitucional.postulantesInscritos ?? 0,
            icon: GraduationCap,
        },
        {
            label: 'Próximos simulacros',
            value: gestionInstitucional.proximosSimulacros ?? 0,
            icon: CalendarDays,
        },
        {
            label: 'Promedio institucional',
            value: Number(gestionInstitucional.promedioInstitucional ?? 0).toFixed(1),
            icon: BarChart3,
        },
        {
            label: 'Seguimiento prioritario',
            value: gestionInstitucional.seguimientoPrioritario ?? 0,
            icon: Users,
        },
        {
            label: 'Tutores activos',
            value: gestionInstitucional.tutoresActivos ?? 0,
            icon: UserRoundCheck,
        },
        {
            label: 'Grupos con tutor',
            value: gestionInstitucional.gruposConTutor ?? 0,
            icon: Waypoints,
        },
        {
            label: 'Asignaciones activas',
            value: gestionInstitucional.asignacionesActivas ?? 0,
            icon: Link2,
        },
        {
            label: 'Matrículas activas',
            value: gestionInstitucional.matriculasActivas ?? 0,
            icon: WalletCards,
        },
        {
            label: 'Cuotas pendientes',
            value: gestionInstitucional.cuotasPendientes ?? 0,
            icon: CircleDollarSign,
        },
        {
            label: 'Cuotas vencidas',
            value: gestionInstitucional.cuotasVencidas ?? 0,
            icon: ShieldAlert,
        },
        {
            label: 'Postulantes habilitados',
            value: gestionInstitucional.postulantesHabilitados ?? 0,
            icon: BadgeCheck,
        },
        {
            label: 'Postulantes restringidos',
            value: gestionInstitucional.postulantesRestringidos ?? 0,
            icon: ShieldAlert,
        },
        {
            label: 'Asistencia promedio',
            value: `${Number(gestionInstitucional.asistenciaPromedio ?? 0).toFixed(1)}%`,
            icon: CalendarCheck2,
        },
        {
            label: 'Presentes del periodo',
            value: gestionInstitucional.presentesPeriodo ?? 0,
            icon: UserRoundCheck,
        },
        {
            label: 'Ausencias registradas',
            value: gestionInstitucional.ausenciasPeriodo ?? 0,
            icon: UserRoundX,
        },
        {
            label: 'Grupos con asistencia',
            value: gestionInstitucional.gruposConAsistencia ?? 0,
            icon: Layers3,
        },
    ];

    return (
        <AdminLayout
            title="Panel Académico Institucional"
            subtitle="Cobertura curricular y preparación preuniversitaria en ciencias exactas y razonamiento académico."
        >
            <Head title="Panel Académico Institucional" />

            <section className="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-[#16213E] via-[#0F2A3A] to-[#0F766E]/80 p-6 text-white shadow-lg shadow-brand-primary/20 dark:shadow-brand-primary/40 sm:p-8">
                <div className="absolute -right-14 -top-20 h-64 w-64 rounded-full border-[42px] border-white/5" />
                <div className="relative max-w-3xl">
                    <div className="mb-3 flex items-center gap-2 text-sm font-medium text-brand-accent">
                        <BrainCircuit className="h-4 w-4" />
                        Lectura institucional de la estructura académica
                    </div>
                    <h2 className="text-2xl font-bold sm:text-3xl">
                        Cobertura para materias de Ingeniería
                    </h2>
                    <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-200 sm:text-base">
                        INTELECTA consolida Matemática, Física, Química y
                        Razonamiento Académico mediante áreas, temas, preguntas y
                        plantillas diagnósticas.
                    </p>
                </div>
            </section>

            <section>
                <SectionHeading
                    eyebrow="Resumen institucional"
                    title="Indicadores académicos"
                    description="Conteos obtenidos directamente de la estructura vigente del sistema."
                />
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {metrics.map((metric) => (
                        <MetricCard key={metric.title} {...metric} />
                    ))}
                </div>
            </section>

            <section className="mt-8">
                <SectionHeading
                    eyebrow="Gestión institucional"
                    title="Operación académica"
                    description="Programas, grupos, inscripciones, simulacros y seguimiento institucional."
                />
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {institutionalMetrics.map(({ label, value, icon: Icon }) => (
                        <Card
                            key={label}
                            className="rounded-2xl border border-brand-border bg-brand-card shadow-sm dark:shadow-black/20"
                        >
                            <CardContent className="p-5">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-secondary/10 text-brand-secondary">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <p className="mt-4 text-2xl font-black text-text-main">
                                    {value}
                                </p>
                                <p className="mt-1 text-xs font-semibold leading-5 text-text-muted">
                                    {label}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </section>

            <section className="mt-8">
                <SectionHeading
                    eyebrow="Cobertura curricular"
                    title="Materias académicas"
                    description="Participación del banco y disponibilidad de estructura evaluativa por materia."
                />
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {coberturaMaterias.map((materia) => {
                        const styles =
                            matterStyles[materia.codigo_mat] || matterStyles.MAT;

                        return (
                            <Card
                                key={materia.id_mat}
                                className="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                            >
                                <CardContent className="p-5">
                                    <div className="flex items-start justify-between gap-3">
                                        <span
                                            className={`flex h-10 w-10 items-center justify-center rounded-xl ${styles.icon}`}
                                        >
                                            <LibraryBig className="h-5 w-5" />
                                        </span>
                                        <Badge
                                            variant="outline"
                                            className={
                                                coverageBadge[
                                                    materia.estado_cobertura
                                                ]
                                            }
                                        >
                                            {materia.estado_cobertura}
                                        </Badge>
                                    </div>
                                    <p className="mt-4 text-xs font-bold uppercase tracking-wider text-slate-400">
                                        {materia.codigo_mat}
                                    </p>
                                    <h3 className="mt-1 min-h-12 text-base font-bold leading-5 text-slate-900 dark:text-slate-100">
                                        {materia.nombre_mat}
                                    </h3>
                                    <div className="mt-4 grid grid-cols-2 gap-2 text-center">
                                        {[
                                            ['Áreas', materia.areas],
                                            ['Temas', materia.temas],
                                            ['Preguntas', materia.preguntas],
                                            ['Plantillas', materia.plantillas],
                                        ].map(([label, value]) => (
                                            <div
                                                key={label}
                                                className="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60"
                                            >
                                                <p className="text-lg font-black text-slate-900 dark:text-slate-100">
                                                    {value}
                                                </p>
                                                <p className="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                                    {label}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="mt-4">
                                        <div className="mb-1.5 flex justify-between text-xs font-semibold text-slate-500 dark:text-slate-400">
                                            <span>Participación en el banco</span>
                                            <span>
                                                {materia.participacion_banco}%
                                            </span>
                                        </div>
                                        <Progress
                                            value={materia.participacion_banco}
                                            className={`h-2 ${styles.progress}`}
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
                {preguntasSinMateria > 0 && (
                    <p className="mt-3 text-xs font-medium text-amber-700 dark:text-amber-300">
                        {preguntasSinMateria} preguntas requieren asignación
                        curricular para completar la lectura por materia.
                    </p>
                )}
            </section>

            <section className="mt-8">
                <SectionHeading
                    eyebrow="Accesos rápidos"
                    title="Gestión institucional"
                    description="Operaciones frecuentes para mantener la información académica."
                />
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {quickLinks.map(({ label, href, icon: Icon }) => (
                        <Link
                            key={label}
                            href={href}
                            className="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-secondary/30 hover:bg-brand-secondary/5 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-secondary/40 dark:hover:bg-brand-secondary/10"
                        >
                            <span className="flex items-center gap-3">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 group-hover:bg-brand-secondary/10 group-hover:text-brand-secondary dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-brand-secondary/20 dark:group-hover:text-brand-secondary">
                                    <Icon className="h-5 w-5" />
                                </span>
                                <span className="text-sm font-bold text-slate-800 dark:text-slate-100">
                                    {label}
                                </span>
                            </span>
                            <ArrowRight className="h-4 w-4 text-slate-400 transition group-hover:translate-x-1 group-hover:text-brand-secondary" />
                        </Link>
                    ))}
                </div>
            </section>

            <section className="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,1fr)]">
                <Card className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                    <CardHeader className="mb-4 border-0 p-0">
                        <CardTitle className="text-lg font-semibold leading-snug text-slate-900 dark:text-slate-100">
                            Plantillas académicas recientes
                        </CardTitle>
                        <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Instrumentos recientes y materias que integran su composición.
                        </p>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="hidden md:block">
                            <Table className="w-full table-fixed">
                                <TableHeader>
                                    <TableRow className="bg-slate-50/80 dark:bg-slate-800/40">
                                        <TableHead className="w-[45%] px-4 py-3">
                                            Plantilla
                                        </TableHead>
                                        <TableHead className="w-[30%] px-4 py-3">
                                            Materias
                                        </TableHead>
                                        <TableHead className="w-[12%] px-4 py-3 text-center">
                                            Preguntas
                                        </TableHead>
                                        <TableHead className="w-[13%] px-4 py-3 text-right">
                                            Estado
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {plantillasRecientes.map((plantilla) => (
                                        <TableRow key={plantilla.id_plan}>
                                            <TableCell className="px-4 py-4 align-top">
                                                <p className="whitespace-normal break-words text-sm font-semibold leading-snug text-slate-900 dark:text-slate-100">
                                                    {plantilla.nombre_plan}
                                                </p>
                                                <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                    {plantilla.duracion_minutos_plan || 0} min
                                                </p>
                                            </TableCell>
                                            <TableCell className="px-4 py-4 align-top">
                                                <div className="flex max-w-full flex-wrap gap-1.5">
                                                    {plantilla.materias.length >
                                                    0 ? (
                                                        plantilla.materias.map(
                                                            (materia) => (
                                                                <Badge
                                                                    key={materia}
                                                                    variant="outline"
                                                                    className="inline-flex max-w-full shrink-0 rounded-md px-2 py-1 text-[11px] leading-none"
                                                                >
                                                                    {shortMatterLabel(materia)}
                                                                </Badge>
                                                            ),
                                                        )
                                                    ) : (
                                                        <span className="text-xs text-slate-400">
                                                            Sin materia
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="whitespace-nowrap px-4 py-4 text-center align-top font-bold text-slate-700 dark:text-slate-300">
                                                {plantilla.preguntas_count}
                                            </TableCell>
                                            <TableCell className="whitespace-nowrap px-4 py-4 text-right align-top">
                                                <StatusBadge
                                                    status={
                                                        plantilla.estado_plan ===
                                                        'activa'
                                                            ? 'Activa'
                                                            : 'Inactiva'
                                                    }
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {plantillasRecientes.length === 0 && (
                                        <TableRow>
                                            <TableCell
                                                colSpan={4}
                                                className="py-10 text-center text-sm text-slate-400"
                                            >
                                                No hay plantillas registradas.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        <div className="space-y-3 md:hidden">
                            {plantillasRecientes.map((plantilla) => (
                                <div
                                    key={plantilla.id_plan}
                                    className="rounded-xl border border-slate-200 p-4 dark:border-slate-800"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="whitespace-normal break-words text-sm font-semibold leading-snug text-slate-900 dark:text-slate-100">
                                                {plantilla.nombre_plan}
                                            </p>
                                            <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                {plantilla.preguntas_count} preguntas ·{' '}
                                                {plantilla.duracion_minutos_plan || 0} min
                                            </p>
                                        </div>
                                        <StatusBadge
                                            status={
                                                plantilla.estado_plan === 'activa'
                                                    ? 'Activa'
                                                    : 'Inactiva'
                                            }
                                        />
                                    </div>
                                    <div className="mt-3 flex max-w-full flex-wrap gap-1.5">
                                        {plantilla.materias.length > 0 ? (
                                            plantilla.materias.map((materia) => (
                                                <Badge
                                                    key={materia}
                                                    variant="outline"
                                                    className="inline-flex shrink-0 rounded-md px-2 py-1 text-[11px]"
                                                >
                                                    {shortMatterLabel(materia)}
                                                </Badge>
                                            ))
                                        ) : (
                                            <span className="text-xs text-slate-400">
                                                Sin materia
                                            </span>
                                        )}
                                    </div>
                                </div>
                            ))}
                            {plantillasRecientes.length === 0 && (
                                <p className="py-8 text-center text-sm text-slate-400">
                                    No hay plantillas registradas.
                                </p>
                            )}
                        </div>
                    </CardContent>
                </Card>

                <Card className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                    <h3 className="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Postulantes por carrera
                    </h3>
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Distribución registrada entre carreras postuladas.
                    </p>
                    <div className="mt-5 space-y-4">
                        {postulantesPorCarrera.map((carrera) => {
                            const porcentaje =
                                totalPostulantesCarrera > 0
                                    ? (Number(carrera.total) /
                                          totalPostulantesCarrera) *
                                      100
                                    : 0;

                            return (
                                <div
                                    key={carrera.nombre_car}
                                    className="space-y-2"
                                >
                                    <div className="flex items-center justify-between gap-3 text-sm">
                                        <span className="font-medium text-slate-700 dark:text-slate-300">
                                            {carrera.nombre_car}
                                        </span>
                                        <span className="shrink-0 text-xs font-bold text-slate-500 dark:text-slate-400">
                                            {carrera.total}
                                        </span>
                                    </div>
                                    <Progress
                                        value={porcentaje}
                                        className="h-2 bg-brand-primary/10 dark:bg-brand-primary/20 [&_[data-slot=progress-indicator]]:bg-brand-primary"
                                    />
                                </div>
                            );
                        })}
                        {postulantesPorCarrera.length === 0 && (
                            <p className="py-8 text-center text-sm text-slate-400">
                                No hay postulantes asociados a carreras.
                            </p>
                        )}
                    </div>
                </Card>
            </section>

            <section className="mt-8">
                <Card className="rounded-2xl border border-brand-primary/20 bg-brand-primary/5 p-5 shadow-sm dark:border-brand-primary/30 dark:bg-brand-primary/10 sm:p-6">
                    <div className="flex gap-4">
                        <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20 dark:text-slate-200">
                            <BrainCircuit className="h-5 w-5" />
                        </span>
                        <div>
                            <h3 className="font-bold text-brand-primary dark:text-slate-100">
                                Base para Learning Analytics
                            </h3>
                            <p className="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                El banco clasifica reactivos por materia, área,
                                tema, dificultad y habilidad evaluada. Esta
                                estructura permite construir futuras lecturas por
                                desempeño cuando existan evaluaciones aplicadas,
                                sin emitir conclusiones predictivas.
                            </p>
                        </div>
                    </div>
                </Card>
            </section>
        </AdminLayout>
    );
}
