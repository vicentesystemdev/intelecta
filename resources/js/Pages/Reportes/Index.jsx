import StatusBadge from '@/Components/StatusBadge';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import useTheme from '@/Hooks/useTheme';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import {
    Activity,
    BarChart3,
    BookCopy,
    BrainCircuit,
    Building2,
    ChevronDown,
    ChevronUp,
    FileQuestion,
    GraduationCap,
    Layers3,
    LibraryBig,
    Search,
    Sparkles,
    Tags,
} from 'lucide-react';
import { Fragment, useMemo, useState } from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

const chartColors = [
    '#16213E',
    '#0F766E',
    '#E0B84C',
    '#0F4B70',
    '#1A5C5C',
    '#10b981',
    '#f59e0b',
];

const difficultyColors = {
    basica: '#10b981',
    media: '#f59e0b',
    avanzada: '#f43f5e',
    sin_clasificacion: '#94a3b8',
};

const shortMatterLabel = (name) => {
    if (name?.includes('Razonamiento Académico')) return 'PAA';
    return name || 'Sin materia';
};

function ChartCard({ icon: Icon, title, description, children }) {
    return (
        <Card className="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <CardHeader className="pb-2">
                <CardTitle className="flex items-center gap-2 text-sm font-black uppercase tracking-wider text-slate-900 dark:text-slate-100">
                    <Icon className="h-4.5 w-4.5 text-brand-secondary dark:text-brand-secondary" />
                    {title}
                </CardTitle>
                <p className="text-xs leading-5 text-slate-500 dark:text-slate-400">
                    {description}
                </p>
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}

function EmptyState({ message }) {
    return (
        <div className="flex h-72 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/40 p-6 text-center text-xs font-semibold text-slate-400 dark:border-slate-800 dark:bg-slate-800/10 dark:text-slate-500">
            {message}
        </div>
    );
}

export default function Index({
    metricas = {},
    postulantesPorUniversidad = [],
    postulantesPorCarrera = [],
    preguntasPorArea = [],
    coberturaMaterias = [],
    dificultadPorMateria = [],
    plantillasPorTipo = [],
    plantillasDetalle = [],
    postulantesList = [],
    preguntasSinMateria = 0,
}) {
    const { isDark } = useTheme();
    const [searchQuery, setSearchQuery] = useState('');
    const [expandedPostulanteId, setExpandedPostulanteId] = useState(null);

    const gridColor = isDark ? '#334155' : '#e2e8f0';
    const axisColor = isDark ? '#94a3b8' : '#64748b';
    const tooltipStyle = {
        backgroundColor: isDark ? '#020617' : '#ffffff',
        borderColor: isDark ? '#1e293b' : '#e2e8f0',
        borderRadius: 12,
        color: isDark ? '#f8fafc' : '#0f172a',
        fontSize: 12,
    };

    const metricCards = [
        {
            title: 'Postulantes',
            value: metricas.totalPostulantes ?? 0,
            detail: 'Perfiles registrados',
            icon: GraduationCap,
            color: 'text-brand-primary dark:text-slate-300',
        },
        {
            title: 'Materias',
            value: metricas.totalMaterias ?? 0,
            detail: 'Base curricular',
            icon: LibraryBig,
            color: 'text-brand-primary dark:text-slate-300',
        },
        {
            title: 'Preguntas',
            value: metricas.totalPreguntas ?? 0,
            detail: 'Banco académico',
            icon: FileQuestion,
            color: 'text-brand-secondary dark:text-brand-secondary',
        },
        {
            title: 'Plantillas activas',
            value: metricas.totalPlantillasActivas ?? 0,
            detail: 'Instrumentos disponibles',
            icon: BookCopy,
            color: 'text-brand-secondary dark:text-brand-secondary',
        },
        {
            title: 'Áreas',
            value: metricas.totalAreas ?? 0,
            detail: 'Campos curriculares',
            icon: Layers3,
            color: 'text-brand-primary dark:text-slate-300',
        },
        {
            title: 'Temas',
            value: metricas.totalTemas ?? 0,
            detail: 'Contenidos evaluables',
            icon: Tags,
            color: 'text-emerald-600',
        },
        {
            title: 'Universidades',
            value: metricas.totalUniversidades ?? 0,
            detail: 'Destinos académicos',
            icon: Building2,
            color: 'text-amber-600',
        },
        {
            title: 'Carreras',
            value: metricas.totalCarreras ?? 0,
            detail: 'Opciones postuladas',
            icon: BarChart3,
            color: 'text-slate-500',
        },
    ];

    const dataUniversidades = postulantesPorUniversidad.map((item) => ({
        name: item.sigla_uni || item.nombre_uni,
        value: Number(item.total) || 0,
    }));

    const dataCarreras = postulantesPorCarrera.slice(0, 8).map((item) => ({
        name:
            item.nombre_car.length > 22
                ? `${item.nombre_car.slice(0, 22)}...`
                : item.nombre_car,
        fullName: item.nombre_car,
        total: Number(item.total) || 0,
    }));

    const dataPreguntasMateria = coberturaMaterias.map((materia) => ({
        name: materia.codigo_mat,
        fullName: materia.nombre_mat,
        preguntas: Number(materia.preguntas) || 0,
    }));

    const dataCobertura = coberturaMaterias.map((materia) => ({
        name: materia.codigo_mat,
        fullName: materia.nombre_mat,
        areas: Number(materia.areas) || 0,
        temas: Number(materia.temas) || 0,
        preguntas: Number(materia.preguntas) || 0,
        plantillas: Number(materia.plantillas) || 0,
    }));

    const dataDificultad = dificultadPorMateria.map((materia) => ({
        name: materia.codigo_mat,
        fullName: materia.nombre_mat,
        basica: Number(materia.basica) || 0,
        media: Number(materia.media) || 0,
        avanzada: Number(materia.avanzada) || 0,
        sin_clasificacion: Number(materia.sin_clasificacion) || 0,
    }));

    const dataPlantillas = plantillasPorTipo.map((item) => ({
        name: item.tipo,
        value: Number(item.total) || 0,
    }));

    const filteredPostulantes = useMemo(() => {
        const query = searchQuery.trim().toLowerCase();
        if (!query) return postulantesList;

        return postulantesList.filter((postulante) =>
            [
                postulante.nombre_completo,
                postulante.colegio,
                postulante.universidad,
                postulante.carrera,
            ]
                .filter(Boolean)
                .some((value) => value.toLowerCase().includes(query)),
        );
    }, [postulantesList, searchQuery]);

    const materiaLider = [...coberturaMaterias].sort(
        (a, b) => Number(b.preguntas) - Number(a.preguntas),
    )[0];
    const areaLider = preguntasPorArea[0];

    return (
        <AdminLayout
            title="Reportes Académicos"
            subtitle="Cobertura institucional en ciencias exactas y razonamiento académico."
            wide
        >
            <Head title="Reportes Académicos - INTELECTA" />

            <div className="space-y-8">
                <section className="flex flex-col justify-between gap-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:flex-row md:items-center">
                    <div>
                        <Badge className="bg-brand-primary/10 text-brand-primary hover:bg-brand-primary/15 dark:bg-brand-primary/20 dark:text-slate-200">
                            <Sparkles className="mr-1 h-3.5 w-3.5" />
                            Lectura institucional
                        </Badge>
                        <p className="mt-3 max-w-4xl text-sm leading-6 text-slate-600 dark:text-slate-400">
                            La cobertura académica se distribuye entre Matemática,
                            Física, Química y Razonamiento Académico. Las métricas
                            proceden de materias, áreas, temas, preguntas y
                            plantillas registradas.
                        </p>
                    </div>
                    <Link
                        href="/dashboard"
                        className="inline-flex h-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 px-4 text-xs font-semibold text-white transition hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600"
                    >
                        Volver al Dashboard
                    </Link>
                </section>

                <section className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    {metricCards.map(({ title, value, detail, icon: Icon, color }) => (
                        <Card
                            key={title}
                            className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div className="flex items-center justify-between">
                                <p className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {title}
                                </p>
                                <Icon className={`h-4 w-4 ${color}`} />
                            </div>
                            <p className="mt-3 text-2xl font-black text-slate-900 dark:text-slate-100">
                                {value}
                            </p>
                            <p className="mt-1 text-[10px] font-semibold text-slate-400">
                                {detail}
                            </p>
                        </Card>
                    ))}
                </section>

                <section className="grid gap-6 md:grid-cols-2">
                    <ChartCard
                        icon={Building2}
                        title="Postulantes por universidad"
                        description="Distribución de destinos académicos registrados."
                    >
                        {dataUniversidades.length === 0 ? (
                            <EmptyState message="No hay universidades asociadas a postulantes." />
                        ) : (
                            <div className="h-72">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={dataUniversidades}
                                            dataKey="value"
                                            nameKey="name"
                                            innerRadius={62}
                                            outerRadius={94}
                                            paddingAngle={3}
                                        >
                                            {dataUniversidades.map((item, index) => (
                                                <Cell
                                                    key={item.name}
                                                    fill={
                                                        chartColors[
                                                            index % chartColors.length
                                                        ]
                                                    }
                                                />
                                            ))}
                                        </Pie>
                                        <Tooltip contentStyle={tooltipStyle} />
                                        <Legend
                                            wrapperStyle={{
                                                fontSize: 11,
                                                color: axisColor,
                                            }}
                                        />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                        )}
                    </ChartCard>

                    <ChartCard
                        icon={GraduationCap}
                        title="Postulantes por carrera"
                        description="Carreras con mayor cantidad de postulantes registrados."
                    >
                        {dataCarreras.length === 0 ? (
                            <EmptyState message="No hay carreras asociadas a postulantes." />
                        ) : (
                            <div className="h-72">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        layout="vertical"
                                        data={dataCarreras}
                                        margin={{ left: 10, right: 20 }}
                                    >
                                        <CartesianGrid
                                            stroke={gridColor}
                                            strokeDasharray="3 3"
                                            horizontal={false}
                                        />
                                        <XAxis
                                            type="number"
                                            stroke={axisColor}
                                            fontSize={10}
                                            tickLine={false}
                                        />
                                        <YAxis
                                            type="category"
                                            dataKey="name"
                                            width={135}
                                            stroke={axisColor}
                                            fontSize={10}
                                            tickLine={false}
                                        />
                                        <Tooltip contentStyle={tooltipStyle} />
                                        <Bar
                                            dataKey="total"
                                            name="Postulantes"
                                            fill="#4f46e5"
                                            radius={[0, 6, 6, 0]}
                                            barSize={15}
                                        />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        )}
                    </ChartCard>

                    <ChartCard
                        icon={FileQuestion}
                        title="Preguntas por materia"
                        description="Distribución real del banco entre materias académicas."
                    >
                        {dataPreguntasMateria.length === 0 ? (
                            <EmptyState message="No hay materias para analizar." />
                        ) : (
                            <div className="h-72">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={dataPreguntasMateria}>
                                        <CartesianGrid
                                            stroke={gridColor}
                                            strokeDasharray="3 3"
                                            vertical={false}
                                        />
                                        <XAxis
                                            dataKey="name"
                                            stroke={axisColor}
                                            fontSize={11}
                                            tickLine={false}
                                        />
                                        <YAxis
                                            stroke={axisColor}
                                            fontSize={10}
                                            tickLine={false}
                                        />
                                        <Tooltip contentStyle={tooltipStyle} />
                                        <Bar
                                            dataKey="preguntas"
                                            name="Preguntas"
                                            radius={[6, 6, 0, 0]}
                                            barSize={34}
                                        >
                                            {dataPreguntasMateria.map((item, index) => (
                                                <Cell
                                                    key={item.name}
                                                    fill={
                                                        chartColors[
                                                            index % chartColors.length
                                                        ]
                                                    }
                                                />
                                            ))}
                                        </Bar>
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        )}
                    </ChartCard>

                    <ChartCard
                        icon={Layers3}
                        title="Cobertura curricular por materia"
                        description="Áreas, temas, preguntas y plantillas donde participa cada materia."
                    >
                        {dataCobertura.length === 0 ? (
                            <EmptyState message="No hay estructura curricular disponible." />
                        ) : (
                            <div className="h-72">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        layout="vertical"
                                        data={dataCobertura}
                                        margin={{ left: 5, right: 20 }}
                                    >
                                        <CartesianGrid
                                            stroke={gridColor}
                                            strokeDasharray="3 3"
                                            horizontal={false}
                                        />
                                        <XAxis
                                            type="number"
                                            stroke={axisColor}
                                            fontSize={10}
                                            tickLine={false}
                                        />
                                        <YAxis
                                            type="category"
                                            dataKey="name"
                                            width={45}
                                            stroke={axisColor}
                                            fontSize={11}
                                            tickLine={false}
                                        />
                                        <Tooltip contentStyle={tooltipStyle} />
                                        <Legend wrapperStyle={{ fontSize: 11 }} />
                                        <Bar
                                            dataKey="areas"
                                            name="Áreas"
                                            fill="#4f46e5"
                                            radius={[0, 4, 4, 0]}
                                        />
                                        <Bar
                                            dataKey="temas"
                                            name="Temas"
                                            fill="#0ea5e9"
                                            radius={[0, 4, 4, 0]}
                                        />
                                        <Bar
                                            dataKey="preguntas"
                                            name="Preguntas"
                                            fill="#06b6d4"
                                            radius={[0, 4, 4, 0]}
                                        />
                                        <Bar
                                            dataKey="plantillas"
                                            name="Plantillas"
                                            fill="#7c3aed"
                                            radius={[0, 4, 4, 0]}
                                        />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        )}
                    </ChartCard>

                    <ChartCard
                        icon={Activity}
                        title="Dificultad por materia"
                        description="Composición básica, media y avanzada de cada materia."
                    >
                        {dataDificultad.length === 0 ? (
                            <EmptyState message="No hay dificultad curricular clasificada." />
                        ) : (
                            <div className="h-72">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={dataDificultad}>
                                        <CartesianGrid
                                            stroke={gridColor}
                                            strokeDasharray="3 3"
                                            vertical={false}
                                        />
                                        <XAxis
                                            dataKey="name"
                                            stroke={axisColor}
                                            fontSize={11}
                                            tickLine={false}
                                        />
                                        <YAxis
                                            stroke={axisColor}
                                            fontSize={10}
                                            tickLine={false}
                                        />
                                        <Tooltip contentStyle={tooltipStyle} />
                                        <Legend wrapperStyle={{ fontSize: 11 }} />
                                        <Bar
                                            dataKey="basica"
                                            name="Básica"
                                            stackId="dificultad"
                                            fill={difficultyColors.basica}
                                        />
                                        <Bar
                                            dataKey="media"
                                            name="Media"
                                            stackId="dificultad"
                                            fill={difficultyColors.media}
                                        />
                                        <Bar
                                            dataKey="avanzada"
                                            name="Avanzada"
                                            stackId="dificultad"
                                            fill={difficultyColors.avanzada}
                                        />
                                        <Bar
                                            dataKey="sin_clasificacion"
                                            name="Sin clasificación"
                                            stackId="dificultad"
                                            fill={difficultyColors.sin_clasificacion}
                                        />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        )}
                    </ChartCard>

                    <ChartCard
                        icon={BookCopy}
                        title="Plantillas por tipo académico"
                        description="Instrumentos clasificados según nombre y composición por materia."
                    >
                        {dataPlantillas.length === 0 ? (
                            <EmptyState message="No hay plantillas académicas registradas." />
                        ) : (
                            <div className="h-72">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={dataPlantillas}
                                            dataKey="value"
                                            nameKey="name"
                                            innerRadius={58}
                                            outerRadius={90}
                                            paddingAngle={3}
                                        >
                                            {dataPlantillas.map((item, index) => (
                                                <Cell
                                                    key={item.name}
                                                    fill={
                                                        chartColors[
                                                            index % chartColors.length
                                                        ]
                                                    }
                                                />
                                            ))}
                                        </Pie>
                                        <Tooltip contentStyle={tooltipStyle} />
                                        <Legend
                                            wrapperStyle={{
                                                fontSize: 10,
                                                color: axisColor,
                                            }}
                                        />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                        )}
                    </ChartCard>
                </section>

                <section>
                    <div className="mb-4">
                        <h2 className="text-lg font-black text-slate-900 dark:text-slate-100">
                            Composición de plantillas
                        </h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                            Materias y ponderación general de cada instrumento académico.
                        </p>
                    </div>
                    <Card className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                        <CardContent className="p-0">
                            <div className="hidden md:block">
                            <Table className="w-full table-fixed">
                                <TableHeader>
                                    <TableRow className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableHead className="w-[34%] px-4 py-3">
                                            Plantilla
                                        </TableHead>
                                        <TableHead className="w-[28%] px-4 py-3">
                                            Materias
                                        </TableHead>
                                        <TableHead className="w-[10%] px-4 py-3 text-center">
                                            Preguntas
                                        </TableHead>
                                        <TableHead className="w-[10%] px-4 py-3 text-center">
                                            Puntaje
                                        </TableHead>
                                        <TableHead className="w-[10%] px-4 py-3">
                                            Dificultad
                                        </TableHead>
                                        <TableHead className="w-[8%] px-4 py-3 text-right">
                                            Estado
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {plantillasDetalle.map((plantilla) => (
                                        <TableRow key={plantilla.id_plan}>
                                            <TableCell className="whitespace-normal break-words px-4 py-4 align-top text-sm font-semibold leading-snug text-slate-900 dark:text-slate-100">
                                                {plantilla.nombre_plan}
                                            </TableCell>
                                            <TableCell className="px-4 py-4 align-top">
                                                <div className="flex max-w-full flex-wrap gap-1.5">
                                                    {plantilla.materias.map(
                                                        (materia) => (
                                                            <Badge
                                                                key={materia.materia}
                                                                variant="outline"
                                                                className="inline-flex max-w-full shrink-0 rounded-md px-2 py-1 text-[11px] leading-none"
                                                            >
                                                                {shortMatterLabel(materia.materia)}:{' '}
                                                                {materia.cantidad}
                                                            </Badge>
                                                        ),
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="whitespace-nowrap px-4 py-4 text-center align-top">
                                                {plantilla.cantidad_preguntas}
                                            </TableCell>
                                            <TableCell className="whitespace-nowrap px-4 py-4 text-center align-top">
                                                {Number(
                                                    plantilla.puntaje_total,
                                                ).toFixed(2)}
                                            </TableCell>
                                            <TableCell className="whitespace-normal break-words px-4 py-4 align-top text-xs capitalize leading-snug">
                                                {plantilla.dificultad_plan?.replace(
                                                    '-',
                                                    ' ',
                                                ) || 'Sin clasificación'}
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
                                </TableBody>
                            </Table>
                            </div>

                            <div className="space-y-3 md:hidden">
                                {plantillasDetalle.map((plantilla) => (
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
                                                    {plantilla.cantidad_preguntas} preguntas ·{' '}
                                                    {Number(plantilla.puntaje_total).toFixed(2)} pts
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
                                            {plantilla.materias.map((materia) => (
                                                <Badge
                                                    key={materia.materia}
                                                    variant="outline"
                                                    className="inline-flex shrink-0 rounded-md px-2 py-1 text-[11px]"
                                                >
                                                    {shortMatterLabel(materia.materia)}:{' '}
                                                    {materia.cantidad}
                                                </Badge>
                                            ))}
                                        </div>
                                        <p className="mt-3 text-xs capitalize text-slate-500 dark:text-slate-400">
                                            Dificultad:{' '}
                                            {plantilla.dificultad_plan?.replace('-', ' ') ||
                                                'Sin clasificación'}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </section>

                <section>
                    <div className="mb-4 flex flex-col justify-between gap-3 md:flex-row md:items-end">
                        <div>
                            <h2 className="text-lg font-black text-slate-900 dark:text-slate-100">
                                Perfil académico preliminar por postulante
                            </h2>
                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                Contexto institucional según carrera y exigencia
                                registrada, sin resultados evaluativos atribuidos.
                            </p>
                        </div>
                        <div className="relative w-full md:w-96">
                            <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                            <input
                                value={searchQuery}
                                onChange={(event) =>
                                    setSearchQuery(event.target.value)
                                }
                                className="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                                aria-label="Buscar postulante"
                                placeholder="Buscar postulante, colegio, universidad o carrera"
                            />
                        </div>
                    </div>
                    <Card className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                        <CardContent className="p-0">
                            <div className="hidden lg:block">
                            <Table className="w-full table-fixed">
                                <TableHeader>
                                    <TableRow className="bg-slate-50 dark:bg-slate-800/50">
                                        <TableHead className="w-[22%] px-4 py-3">
                                            Postulante
                                        </TableHead>
                                        <TableHead className="w-[18%] px-4 py-3">
                                            Colegio
                                        </TableHead>
                                        <TableHead className="w-[22%] px-4 py-3">
                                            Universidad / Carrera
                                        </TableHead>
                                        <TableHead className="w-[10%] px-4 py-3">
                                            Exigencia
                                        </TableHead>
                                        <TableHead className="w-[18%] px-4 py-3">
                                            Seguimiento
                                        </TableHead>
                                        <TableHead className="w-[10%] px-4 py-3 text-right">
                                            Lectura
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredPostulantes.map((postulante) => {
                                        const expanded =
                                            expandedPostulanteId ===
                                            postulante.id_post;

                                        return (
                                            <Fragment key={postulante.id_post}>
                                                <TableRow>
                                                    <TableCell className="whitespace-normal break-words px-4 py-4 align-top leading-snug">
                                                        <p className="text-sm font-semibold leading-snug text-slate-900 dark:text-slate-100">
                                                            {
                                                                postulante.nombre_completo
                                                            }
                                                        </p>
                                                        <p className="mt-1 text-xs text-slate-400">
                                                            {postulante.edad}{' '}
                                                            {postulante.edad !==
                                                            'No registrado'
                                                                ? 'años'
                                                                : ''}
                                                        </p>
                                                    </TableCell>
                                                    <TableCell className="whitespace-normal break-words px-4 py-4 align-top text-xs leading-snug text-slate-600 dark:text-slate-400">
                                                        {postulante.colegio}
                                                    </TableCell>
                                                    <TableCell className="whitespace-normal break-words px-4 py-4 align-top text-xs leading-snug">
                                                        <p className="font-semibold leading-snug text-slate-800 dark:text-slate-200">
                                                            {postulante.carrera}
                                                        </p>
                                                        <p className="mt-1 whitespace-normal break-words text-[10px] uppercase leading-snug text-slate-400">
                                                            {
                                                                postulante.universidad
                                                            }
                                                        </p>
                                                    </TableCell>
                                                    <TableCell className="px-4 py-4 align-top">
                                                        <Badge
                                                            variant="outline"
                                                            className="inline-flex shrink-0 whitespace-nowrap text-[10px]"
                                                        >
                                                            {
                                                                postulante.exigencia
                                                            }
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell className="whitespace-normal break-words px-4 py-4 align-top text-xs font-semibold leading-snug text-slate-700 dark:text-slate-300">
                                                        <span className="line-clamp-2">
                                                            {postulante.perfil}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell className="whitespace-nowrap px-4 py-4 text-right align-top">
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                setExpandedPostulanteId(
                                                                    expanded
                                                                        ? null
                                                                        : postulante.id_post,
                                                                )
                                                            }
                                                            className="inline-flex items-center gap-1 rounded-lg bg-brand-primary/10 px-2.5 py-1.5 text-[10px] font-bold text-brand-primary dark:bg-brand-primary/20 dark:text-slate-200"
                                                        >
                                                            Ver contexto
                                                            {expanded ? (
                                                                <ChevronUp className="h-3 w-3" />
                                                            ) : (
                                                                <ChevronDown className="h-3 w-3" />
                                                            )}
                                                        </button>
                                                    </TableCell>
                                                </TableRow>
                                                {expanded && (
                                                    <TableRow
                                                        className="bg-slate-50/60 dark:bg-slate-800/30"
                                                    >
                                                        <TableCell
                                                            colSpan={6}
                                                            className="px-4 py-4"
                                                        >
                                                            <div className="rounded-xl border border-brand-primary/20 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
                                                                <p className="text-xs font-bold text-brand-primary dark:text-slate-200">
                                                                    Lectura
                                                                    institucional
                                                                </p>
                                                                <p className="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-400">
                                                                    {
                                                                        postulante.lectura_materias
                                                                    }{' '}
                                                                    Se recomienda
                                                                    aplicar una
                                                                    evaluación
                                                                    diagnóstica
                                                                    acorde con la
                                                                    exigencia de{' '}
                                                                    {
                                                                        postulante.carrera
                                                                    }
                                                                    .
                                                                </p>
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                )}
                                            </Fragment>
                                        );
                                    })}
                                    {filteredPostulantes.length === 0 && (
                                        <TableRow>
                                            <TableCell
                                                colSpan={6}
                                                className="py-10 text-center text-sm text-slate-400"
                                            >
                                                No se encontraron postulantes con
                                                el criterio indicado.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                            </div>

                            <div className="space-y-3 lg:hidden">
                                {filteredPostulantes.map((postulante) => {
                                    const expanded =
                                        expandedPostulanteId === postulante.id_post;

                                    return (
                                        <div
                                            key={postulante.id_post}
                                            className="rounded-xl border border-slate-200 p-4 dark:border-slate-800"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <p className="whitespace-normal break-words text-sm font-semibold leading-snug text-slate-900 dark:text-slate-100">
                                                        {postulante.nombre_completo}
                                                    </p>
                                                    <p className="mt-1 text-xs text-slate-400">
                                                        {postulante.edad}{' '}
                                                        {postulante.edad !== 'No registrado'
                                                            ? 'años'
                                                            : ''}
                                                    </p>
                                                </div>
                                                <Badge
                                                    variant="outline"
                                                    className="inline-flex shrink-0 whitespace-nowrap text-[10px]"
                                                >
                                                    {postulante.exigencia}
                                                </Badge>
                                            </div>
                                            <div className="mt-4 grid gap-3 sm:grid-cols-2">
                                                <div>
                                                    <p className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                        Colegio
                                                    </p>
                                                    <p className="mt-1 whitespace-normal break-words text-xs leading-snug text-slate-600 dark:text-slate-400">
                                                        {postulante.colegio}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                        Carrera / Universidad
                                                    </p>
                                                    <p className="mt-1 whitespace-normal break-words text-xs font-semibold leading-snug text-slate-800 dark:text-slate-200">
                                                        {postulante.carrera}
                                                    </p>
                                                    <p className="mt-1 whitespace-normal break-words text-[10px] uppercase leading-snug text-slate-400">
                                                        {postulante.universidad}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="mt-4 flex items-end justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                                                <div className="min-w-0">
                                                    <p className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                        Seguimiento
                                                    </p>
                                                    <p className="mt-1 whitespace-normal break-words text-xs font-semibold leading-snug text-slate-700 dark:text-slate-300">
                                                        {postulante.perfil}
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setExpandedPostulanteId(
                                                            expanded
                                                                ? null
                                                                : postulante.id_post,
                                                        )
                                                    }
                                                    className="inline-flex shrink-0 items-center gap-1 rounded-lg bg-brand-primary/10 px-2.5 py-1.5 text-[10px] font-bold text-brand-primary dark:bg-brand-primary/20 dark:text-slate-200"
                                                >
                                                    Ver contexto
                                                    {expanded ? (
                                                        <ChevronUp className="h-3 w-3" />
                                                    ) : (
                                                        <ChevronDown className="h-3 w-3" />
                                                    )}
                                                </button>
                                            </div>
                                            {expanded && (
                                                <div className="mt-3 rounded-xl border border-brand-secondary/20 bg-brand-secondary/5 p-3 dark:border-brand-secondary/30 dark:bg-brand-secondary/10">
                                                    <p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary dark:text-brand-secondary">
                                                        Lectura institucional
                                                    </p>
                                                    <p className="mt-1 whitespace-normal break-words text-xs leading-5 text-slate-600 dark:text-slate-400">
                                                        {postulante.lectura_materias}{' '}
                                                        Se recomienda aplicar una
                                                        evaluación diagnóstica acorde
                                                        con la exigencia de{' '}
                                                        {postulante.carrera}.
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    );
                                })}
                                {filteredPostulantes.length === 0 && (
                                    <p className="py-8 text-center text-sm text-slate-400">
                                        No se encontraron postulantes con el criterio
                                        indicado.
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 lg:grid-cols-4">
                    {[
                        {
                            title: 'Cobertura académica',
                            text: `La estructura registra ${metricas.totalMaterias ?? 0} materias, ${metricas.totalAreas ?? 0} áreas y ${metricas.totalTemas ?? 0} temas evaluables.`,
                        },
                        {
                            title: 'Banco institucional',
                            text: materiaLider
                                ? `${materiaLider.nombre_mat} concentra ${materiaLider.preguntas} preguntas; la lectura debe complementarse con las demás materias.`
                                : 'El banco está preparado para organizar preguntas por materia.',
                        },
                        {
                            title: 'Cobertura por área',
                            text: areaLider
                                ? `${areaLider.nombre_area} es el área con mayor cantidad de preguntas registradas.`
                                : 'Las áreas podrán compararse cuando existan preguntas asociadas.',
                        },
                        {
                            title: 'Base para Learning Analytics',
                            text: 'Las plantillas mixtas permiten observar equilibrio curricular cuando se registren evaluaciones aplicadas y resultados históricos.',
                        },
                    ].map((item) => (
                        <Card
                            key={item.title}
                            className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                        >
                            <BrainCircuit className="h-5 w-5 text-brand-secondary dark:text-brand-secondary" />
                            <h3 className="mt-3 text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                {item.title}
                            </h3>
                            <p className="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                {item.text}
                            </p>
                        </Card>
                    ))}
                </section>

                <section className="rounded-3xl border border-brand-primary/20 bg-brand-primary/5 p-6 dark:border-brand-primary/30 dark:bg-brand-primary/10">
                    <div className="flex gap-4">
                        <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20 dark:text-slate-200">
                            <BrainCircuit className="h-5 w-5" />
                        </span>
                        <div>
                            <h3 className="font-bold text-brand-primary dark:text-slate-100">
                                Interpretación responsable
                            </h3>
                            <p className="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-300">
                                Los datos actuales representan cobertura académica
                                y lectura institucional. No constituyen resultados
                                de admisión ni permiten estimar el ingreso. El análisis
                                por desempeño requiere evaluaciones aplicadas y
                                resultados históricos consistentes.
                            </p>
                            {preguntasSinMateria > 0 && (
                                <p className="mt-2 text-xs font-semibold text-amber-700 dark:text-amber-300">
                                    {preguntasSinMateria} preguntas permanecen sin
                                    materia asociada y se presentan como pendientes
                                    de clasificación curricular.
                                </p>
                            )}
                        </div>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
