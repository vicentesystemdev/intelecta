import MetricCard from '@/Components/MetricCard';
import StatusBadge from '@/Components/StatusBadge';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
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
    AlertTriangle,
    BrainCircuit,
    Building2,
    CheckCircle2,
    ClipboardList,
    GraduationCap,
    Lightbulb,
    Sigma,
    Sparkles,
    Target,
    TrendingUp,
    Users,
    ChevronRight,
    Settings,
    ShieldCheck,
    Layers,
} from 'lucide-react';

const metrics = [
    {
        title: 'Postulantes registrados',
        value: '1.248',
        detail: '8,4% más que el periodo anterior',
        trend: 'up',
        icon: GraduationCap,
        accent: 'indigo',
    },
    {
        title: 'Docentes activos',
        value: '86',
        detail: '4 incorporaciones en el periodo',
        trend: 'up',
        icon: Users,
        accent: 'blue',
    },
    {
        title: 'Evaluaciones creadas',
        value: '32',
        detail: '6 programadas para esta semana',
        trend: 'up',
        icon: ClipboardList,
        accent: 'cyan',
    },
    {
        title: 'Evaluaciones cerradas',
        value: '18',
        detail: '56% del ciclo de evaluación',
        trend: 'neutral',
        icon: CheckCircle2,
        accent: 'sky',
    },
    {
        title: 'Promedio lógico-matemático',
        value: '72,6',
        detail: '2,1 puntos sobre el último corte',
        trend: 'up',
        icon: Sigma,
        accent: 'violet',
    },
    {
        title: 'Riesgo académico alto',
        value: '94',
        detail: '7,5% requiere atención prioritaria',
        trend: 'down',
        icon: AlertTriangle,
        accent: 'rose',
    },
];

const evaluations = [
    {
        name: 'Diagnóstico de razonamiento lógico',
        audience: 'Ingeniería de Sistemas',
        date: '08 Jun 2026',
        participants: 184,
        status: 'Activa',
    },
    {
        name: 'Competencias lógico-matemáticas I',
        audience: 'Administración',
        date: '06 Jun 2026',
        participants: 126,
        status: 'Cerrada',
    },
    {
        name: 'Fundamentos matemáticos',
        audience: 'Ciencias Económicas',
        date: '04 Jun 2026',
        participants: 211,
        status: 'Cerrada',
    },
    {
        name: 'Pensamiento lógico y análisis',
        audience: 'Área transversal',
        date: '02 Jun 2026',
        participants: 98,
        status: 'Borrador',
    },
];

const priorityApplicants = [
    { name: 'María Fernanda López', program: 'Ing. de Sistemas', score: 42 },
    { name: 'Carlos Andrés Rojas', program: 'Administración', score: 47 },
    { name: 'Valentina Cruz', program: 'Contaduría Pública', score: 51 },
];

const careerDistribution = [
    { label: 'Ingeniería de Sistemas', value: 31, applicants: 387 },
    { label: 'Administración', value: 25, applicants: 312 },
    { label: 'Contaduría Pública', value: 19, applicants: 237 },
    { label: 'Ingeniería Industrial', value: 15, applicants: 187 },
    { label: 'Otras carreras', value: 10, applicants: 125 },
];

const analyticsIndicators = [
    { label: 'Razonamiento lógico', value: 73 },
    { label: 'Álgebra básica', value: 64 },
    { label: 'Resolución de problemas', value: 76 },
    { label: 'Interpretación cuantitativa', value: 68 },
];

const recommendations = [
    'Reforzar álgebra básica en grupos con desempeño menor al nivel esperado.',
    'Priorizar seguimiento a postulantes con bajo rendimiento en razonamiento lógico.',
    'Planificar evaluaciones diagnósticas por carrera postulada.',
];

function SectionHeading({ eyebrow, title, description }) {
    return (
        <div className="mb-4">
            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">
                {eyebrow}
            </p>
            <h2 className="mt-1 text-xl font-bold text-slate-900">{title}</h2>
            {description && (
                <p className="mt-1 text-sm text-slate-500">{description}</p>
            )}
        </div>
    );
}

export default function Dashboard() {
    return (
        <AdminLayout
            title="Panel Académico Institucional"
            subtitle="Seguimiento del desempeño lógico-matemático de postulantes preuniversitarios."
        >
            <Head title="Panel Académico Institucional" />

            <section className="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-800 via-blue-700 to-cyan-600 p-6 text-white shadow-lg shadow-indigo-200/60 sm:p-8">
                <div className="absolute -right-14 -top-20 h-64 w-64 rounded-full border-[42px] border-white/5" />
                <div className="relative max-w-3xl">
                    <div className="mb-3 flex items-center gap-2 text-sm font-medium text-cyan-100">
                        <Building2 className="h-4 w-4" />
                        Información consolidada del proceso de admisión
                    </div>
                    <h2 className="text-2xl font-bold sm:text-3xl">
                        Estado general de postulantes y evaluaciones
                    </h2>
                    <p className="mt-3 max-w-2xl text-sm leading-6 text-indigo-100 sm:text-base">
                        La vista ejecutiva integra seguimiento académico, control de
                        evaluaciones y alertas de desempeño para respaldar decisiones
                        institucionales.
                    </p>
                    <div className="mt-5 flex flex-wrap gap-3 text-xs font-medium">
                        <span className="rounded-full bg-white/10 px-3 py-1.5 ring-1 ring-white/15">
                            Periodo académico 2026-I
                        </span>
                        <span className="rounded-full bg-white/10 px-3 py-1.5 ring-1 ring-white/15">
                            Corte actualizado: 08 Jun 2026
                        </span>
                    </div>
                </div>
            </section>

            <section>
                <SectionHeading
                    eyebrow="Resumen institucional"
                    title="Métricas académicas"
                    description="Indicadores generales para el seguimiento del proceso preuniversitario."
                />
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {metrics.map((metric) => (
                        <MetricCard key={metric.title} {...metric} />
                    ))}
                </div>
            </section>

            <section className="mt-8">
                <SectionHeading
                    eyebrow="Navegación Modular"
                    title="Módulos y Herramientas Administrativas"
                    description="Consola de accesos directos para la gestión del instituto preuniversitario."
                />
                
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Tarjeta A: Gestión Académica */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 hover:ring-indigo-300 transition duration-300">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <div className="flex items-center gap-2">
                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                    <GraduationCap className="h-4.5 w-4.5" />
                                </span>
                                <CardTitle className="text-sm font-bold text-slate-800">
                                    Gestión Académica
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-2">
                            <Link href="/postulantes" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                <span>Postulantes</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/gestion-academica/docentes" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                <span>Docentes</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/gestion-academica/carreras" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                <span>Carreras</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/gestion-academica/colegios" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                <span>Colegios</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                        </CardContent>
                    </Card>

                    {/* Tarjeta B: Evaluaciones */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 hover:ring-cyan-300 transition duration-300">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <div className="flex items-center gap-2">
                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600">
                                    <ClipboardList className="h-4.5 w-4.5" />
                                </span>
                                <CardTitle className="text-sm font-bold text-slate-800">
                                    Evaluaciones
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-2">
                            <Link href="/plantillas-evaluacion" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-cyan-50 hover:text-cyan-700 transition">
                                <span>Plantillas</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/preguntas" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-cyan-50 hover:text-cyan-700 transition">
                                <span>Banco de Preguntas</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/evaluaciones" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-cyan-50 hover:text-cyan-700 transition">
                                <span>Evaluaciones</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/evaluaciones/resultados" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-cyan-50 hover:text-cyan-700 transition">
                                <span>Resultados</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                        </CardContent>
                    </Card>

                    {/* Tarjeta C: Análisis Académico */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 hover:ring-violet-300 transition duration-300">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <div className="flex items-center gap-2">
                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                    <TrendingUp className="h-4.5 w-4.5" />
                                </span>
                                <CardTitle className="text-sm font-bold text-slate-800">
                                    Análisis Académico
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-2">
                            <Link href="/reportes-academicos" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-violet-50 hover:text-violet-700 transition">
                                <span>Reportes Académicos</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/analisis/learning-analytics" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-violet-50 hover:text-violet-750 transition">
                                <span>Learning Analytics</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/analisis/riesgo-academico" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-violet-50 hover:text-violet-750 transition">
                                <span>Riesgo Académico</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                        </CardContent>
                    </Card>

                    {/* Tarjeta D: Sistema */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 hover:ring-slate-400 transition duration-300">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <div className="flex items-center gap-2">
                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                    <Settings className="h-4.5 w-4.5" />
                                </span>
                                <CardTitle className="text-sm font-bold text-slate-800">
                                    Sistema
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-2">
                            <Link href="/admin/sistema/usuarios" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 hover:text-slate-950 transition">
                                <span>Usuarios</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/sistema/roles-permisos" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 hover:text-slate-950 transition">
                                <span>Roles y Permisos</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                            <Link href="/admin/sistema/configuracion" className="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 hover:text-slate-950 transition">
                                <span>Configuración</span>
                                <ChevronRight className="h-3.5 w-3.5" />
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <section className="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,1fr)]">
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader className="border-b border-slate-100">
                        <CardTitle className="text-lg text-slate-900">
                            Evaluaciones recientes
                        </CardTitle>
                        <CardDescription>
                            Organización y estado del ciclo lógico-matemático
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="px-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-slate-50/80">
                                    <TableHead className="pl-5">Evaluación</TableHead>
                                    <TableHead>Carrera o área</TableHead>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead className="text-center">
                                        Postulantes
                                    </TableHead>
                                    <TableHead className="pr-5">Estado</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {evaluations.map((evaluation) => (
                                    <TableRow key={evaluation.name}>
                                        <TableCell className="pl-5 font-medium text-slate-900">
                                            {evaluation.name}
                                        </TableCell>
                                        <TableCell className="text-slate-600">
                                            {evaluation.audience}
                                        </TableCell>
                                        <TableCell className="text-slate-500">
                                            {evaluation.date}
                                        </TableCell>
                                        <TableCell className="text-center font-medium">
                                            {evaluation.participants}
                                        </TableCell>
                                        <TableCell className="pr-5">
                                            <StatusBadge status={evaluation.status} />
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader className="border-b border-slate-100">
                        <CardTitle className="flex items-center gap-2 text-lg text-slate-900">
                            <AlertTriangle className="h-5 w-5 text-rose-500" />
                            Postulantes con atención prioritaria
                        </CardTitle>
                        <CardDescription>
                            Seguimiento por desempeño lógico-matemático
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-5">
                        {priorityApplicants.map((applicant) => (
                            <div key={applicant.name}>
                                <div className="mb-2 flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-sm font-semibold text-slate-900">
                                            {applicant.name}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            {applicant.program}
                                        </p>
                                    </div>
                                    <StatusBadge status="Prioritario" />
                                </div>
                                <div className="flex items-center gap-3">
                                    <Progress
                                        value={applicant.score}
                                        className="h-2 bg-rose-100 [&_[data-slot=progress-indicator]]:bg-rose-500"
                                    />
                                    <span className="w-8 text-right text-xs font-bold text-rose-600">
                                        {applicant.score}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </section>

            <section className="mt-8 grid gap-6 xl:grid-cols-2">
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg text-slate-900">
                            <GraduationCap className="h-5 w-5 text-blue-600" />
                            Distribución por carrera postulada
                        </CardTitle>
                        <CardDescription>
                            Participación registrada en el periodo académico
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {careerDistribution.map((career) => (
                            <div key={career.label}>
                                <div className="mb-2 flex items-center justify-between gap-4 text-sm">
                                    <span className="font-medium text-slate-700">
                                        {career.label}
                                    </span>
                                    <span className="text-xs text-slate-500">
                                        {career.applicants} postulantes · {career.value}%
                                    </span>
                                </div>
                                <Progress
                                    value={career.value}
                                    className="h-2 bg-blue-100 [&_[data-slot=progress-indicator]]:bg-blue-600"
                                />
                            </div>
                        ))}
                    </CardContent>
                </Card>

                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg text-slate-900">
                            <BrainCircuit className="h-5 w-5 text-indigo-600" />
                            Indicadores de Learning Analytics
                        </CardTitle>
                        <CardDescription>
                            Análisis preliminar de competencias evaluadas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                            {analyticsIndicators.map((indicator) => (
                                <div key={indicator.label}>
                                    <div className="mb-2 flex justify-between text-sm">
                                        <span className="font-medium text-slate-700">
                                            {indicator.label}
                                        </span>
                                        <span className="font-bold text-indigo-700">
                                            {indicator.value}%
                                        </span>
                                    </div>
                                    <Progress
                                        value={indicator.value}
                                        className="h-2 bg-indigo-100 [&_[data-slot=progress-indicator]]:bg-gradient-to-r [&_[data-slot=progress-indicator]]:from-indigo-600 [&_[data-slot=progress-indicator]]:to-cyan-500"
                                    />
                                </div>
                            ))}
                        </div>
                        <Alert className="mt-6 border-indigo-100 bg-indigo-50/70 p-4 text-indigo-950">
                            <TrendingUp className="h-5 w-5 text-indigo-600" />
                            <AlertTitle>Lectura institucional</AlertTitle>
                            <AlertDescription className="mt-1 text-indigo-700">
                                La información académica está preparada para ampliar el
                                análisis hacia patrones de desempeño y modelos predictivos.
                            </AlertDescription>
                        </Alert>
                    </CardContent>
                </Card>
            </section>

            <section className="mt-8">
                <Card className="border-0 bg-gradient-to-br from-white to-sky-50/70 shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg text-slate-900">
                            <Lightbulb className="h-5 w-5 text-amber-500" />
                            Recomendaciones académicas preliminares
                        </CardTitle>
                        <CardDescription>
                            Orientaciones derivadas de los indicadores institucionales
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-3 lg:grid-cols-3">
                        {recommendations.map((recommendation, index) => (
                            <div
                                key={recommendation}
                                className="flex gap-3 rounded-xl border border-slate-200 bg-white p-4"
                            >
                                <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                    {index === 0 ? (
                                        <Sigma className="h-4 w-4" />
                                    ) : index === 1 ? (
                                        <Target className="h-4 w-4" />
                                    ) : (
                                        <Sparkles className="h-4 w-4" />
                                    )}
                                </span>
                                <p className="text-sm leading-6 text-slate-700">
                                    {recommendation}
                                </p>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </section>
        </AdminLayout>
    );
}
