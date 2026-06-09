import MetricCard from '@/Components/MetricCard';
import StatusBadge from '@/Components/StatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import React from 'react';
import {
    GraduationCap,
    Building2,
    FileQuestion,
    BookCopy,
    SlidersHorizontal,
    TrendingUp,
    Award,
    Lightbulb,
    Sparkles,
    CheckCircle2,
    Target
} from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';

export default function Index({
    metricas,
    postulantesPorUniversidad,
    postulantesPorCarrera,
    postulantesPorColegio,
    preguntasPorArea,
    preguntasPorDificultad,
    plantillasDetalle,
    exigenciaCarrera
}) {
    // Definición de las tarjetas de métricas superiores
    const metricsList = [
        {
            title: 'Postulantes registrados',
            value: metricas.totalPostulantes.toString(),
            detail: 'Seguimiento del proceso activo',
            trend: 'neutral',
            icon: GraduationCap,
            accent: 'indigo',
        },
        {
            title: 'Universidades objetivo',
            value: metricas.totalUniversidades.toString(),
            detail: 'Instituciones de destino',
            trend: 'neutral',
            icon: Building2,
            accent: 'blue',
        },
        {
            title: 'Banco de preguntas',
            value: metricas.totalPreguntas.toString(),
            detail: 'Reactivos clasificados',
            trend: 'neutral',
            icon: FileQuestion,
            accent: 'cyan',
        },
        {
            title: 'Plantillas académicas',
            value: metricas.totalPlantillas.toString(),
            detail: 'Evaluaciones estructuradas',
            trend: 'neutral',
            icon: BookCopy,
            accent: 'sky',
        },
        {
            title: 'Carreras postuladas',
            value: metricas.totalCarreras.toString(),
            detail: 'Oferta académica vinculada',
            trend: 'neutral',
            icon: SlidersHorizontal,
            accent: 'violet',
        },
        {
            title: 'Colegios de procedencia',
            value: metricas.totalColegios.toString(),
            detail: 'Unidades educativas paceñas',
            trend: 'neutral',
            icon: Building2,
            accent: 'rose',
        },
    ];

    return (
        <AdminLayout
            title="Reportes Académicos"
            subtitle="Indicadores institucionales para el seguimiento del desempeño lógico-matemático preuniversitario."
        >
            <Head title="Reportes Académicos - INTELECTA" />

            {/* 2. Cards superiores */}
            <section className="mb-8">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {metricsList.map((metric) => (
                        <MetricCard key={metric.title} {...metric} />
                    ))}
                </div>
            </section>

            {/* Fila de Gráficos de Distribución de Postulantes */}
            <section className="mb-8 grid gap-6 xl:grid-cols-2">
                {/* 3. Postulantes por universidad */}
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="text-lg text-slate-900 flex items-center gap-2">
                            <Building2 className="h-5 w-5 text-indigo-600" />
                            Postulantes por universidad
                        </CardTitle>
                        <CardDescription>
                            Distribución registrada por universidad de postulación meta.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {postulantesPorUniversidad.map((uni) => {
                            const percentage = metricas.totalPostulantes > 0
                                ? Math.round((uni.total / metricas.totalPostulantes) * 100)
                                : 0;
                            return (
                                <div key={uni.sigla_uni} className="space-y-1">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="font-semibold text-slate-700">
                                            {uni.sigla_uni} <span className="font-normal text-slate-500 text-xs">({uni.nombre_uni})</span>
                                        </span>
                                        <span className="text-xs text-slate-500 font-bold">
                                            {uni.total} postulantes · {percentage}%
                                        </span>
                                    </div>
                                    <div className="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div
                                            className="h-full bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-full"
                                            style={{ width: `${percentage}%` }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>

                {/* 4. Postulantes por carrera (Ranking) */}
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="text-lg text-slate-900 flex items-center gap-2">
                            <SlidersHorizontal className="h-5 w-5 text-indigo-600" />
                            Postulantes por carrera
                        </CardTitle>
                        <CardDescription>
                            Ranking de carreras por cantidad de postulantes registrados.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {postulantesPorCarrera.map((car, index) => {
                            const maxTotal = postulantesPorCarrera.length > 0 ? postulantesPorCarrera[0].total : 1;
                            const widthPercent = Math.round((car.total / maxTotal) * 100);
                            return (
                                <div key={car.nombre_car} className="flex items-center gap-4">
                                    <span className="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-600 shrink-0">
                                        {index + 1}
                                    </span>
                                    <div className="flex-1 space-y-1">
                                        <div className="flex items-center justify-between text-xs sm:text-sm">
                                            <span className="font-semibold text-slate-800">{car.nombre_car}</span>
                                            <span className="text-slate-500 font-bold text-xs">{car.total} postulantes</span>
                                        </div>
                                        <div className="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                            <div
                                                className="h-full bg-indigo-600 rounded-full"
                                                style={{ width: `${widthPercent}%` }}
                                            />
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>
            </section>

            {/* Fila de Gráficos de Preguntas */}
            <section className="mb-8 grid gap-6 xl:grid-cols-2">
                {/* 5. Banco de preguntas por área */}
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="text-lg text-slate-900 flex items-center gap-2">
                            <FileQuestion className="h-5 w-5 text-indigo-600" />
                            Banco de preguntas por área
                        </CardTitle>
                        <CardDescription>
                            Reactivos agrupados por área de conocimiento del desempeño lógico-matemático.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {preguntasPorArea.map((area) => {
                            const maxTotal = preguntasPorArea.length > 0 ? preguntasPorArea[0].total : 1;
                            const widthPercent = Math.round((area.total / maxTotal) * 100);
                            return (
                                <div key={area.nombre_area} className="space-y-1">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="font-semibold text-slate-700">{area.nombre_area}</span>
                                        <span className="text-xs text-slate-500 font-bold">
                                            {area.total} reactivos
                                        </span>
                                    </div>
                                    <div className="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div
                                            className="h-full bg-gradient-to-r from-blue-600 to-cyan-500 rounded-full"
                                            style={{ width: `${widthPercent}%` }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>

                {/* 6. Dificultad de preguntas */}
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="text-lg text-slate-900 flex items-center gap-2">
                            <TrendingUp className="h-5 w-5 text-indigo-600" />
                            Dificultad de preguntas
                        </CardTitle>
                        <CardDescription>
                            Distribución de reactivos según el nivel de complejidad.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-5">
                        {preguntasPorDificultad.map((dif) => {
                            let label = 'Básica';
                            let badgeColor = 'bg-emerald-500';
                            let barColor = 'from-emerald-500 to-emerald-400';
                            let trackColor = 'bg-emerald-50';

                            if (dif.dificultad_preg === 'media') {
                                label = 'Media';
                                badgeColor = 'bg-amber-500';
                                barColor = 'from-amber-500 to-amber-400';
                                trackColor = 'bg-amber-50';
                            } else if (dif.dificultad_preg === 'avanzada') {
                                label = 'Avanzada';
                                badgeColor = 'bg-rose-500';
                                barColor = 'from-rose-500 to-rose-400';
                                trackColor = 'bg-rose-50';
                            }

                            const percentage = metricas.totalPreguntas > 0
                                ? Math.round((dif.total / metricas.totalPreguntas) * 100)
                                : 0;

                            return (
                                <div key={dif.dificultad_preg} className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <span className={`h-2.5 w-2.5 rounded-full ${badgeColor}`} />
                                            <span className="text-sm font-semibold text-slate-700">{label}</span>
                                        </div>
                                        <span className="text-xs font-bold text-slate-500">
                                            {dif.total} preguntas · {percentage}%
                                        </span>
                                    </div>
                                    <div className={`h-3 w-full ${trackColor} rounded-full overflow-hidden`}>
                                        <div
                                            className={`h-full bg-gradient-to-r ${barColor} rounded-full`}
                                            style={{ width: `${percentage}%` }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>
            </section>

            {/* Fila de Exigencia Matemática & Colegios */}
            <section className="mb-8 grid gap-6 xl:grid-cols-2">
                {/* Exigencia Matemática */}
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="text-lg text-slate-900 flex items-center gap-2">
                            <Award className="h-5 w-5 text-indigo-600" />
                            Exigencia matemática de carreras
                        </CardTitle>
                        <CardDescription>
                            Distribución de postulantes por nivel de exigencia lógica requerido.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {exigenciaCarrera.map((ex) => {
                            const percentage = metricas.totalPostulantes > 0
                                ? Math.round((ex.total / metricas.totalPostulantes) * 100)
                                : 0;
                            return (
                                <div key={ex.nivel} className="space-y-1">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="font-semibold text-slate-700">{ex.nivel}</span>
                                        <span className="text-xs text-slate-500 font-bold">
                                            {ex.total} postulantes · {percentage}%
                                        </span>
                                    </div>
                                    <div className="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div
                                            className="h-full bg-gradient-to-r from-violet-600 to-indigo-500 rounded-full"
                                            style={{ width: `${percentage}%` }}
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>

                {/* Postulantes por colegio (ranking) */}
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="text-lg text-slate-900 flex items-center gap-2">
                            <GraduationCap className="h-5 w-5 text-indigo-600" />
                            Postulantes por colegio de procedencia
                        </CardTitle>
                        <CardDescription>
                            Ranking de colegios paceños con mayor representación.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {postulantesPorColegio.map((col, index) => {
                            const maxTotal = postulantesPorColegio.length > 0 ? postulantesPorColegio[0].total : 1;
                            const widthPercent = Math.round((col.total / maxTotal) * 100);
                            return (
                                <div key={col.nombre_col} className="flex items-center gap-4">
                                    <span className="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-600 shrink-0">
                                        {index + 1}
                                    </span>
                                    <div className="flex-1 space-y-1">
                                        <div className="flex items-center justify-between text-xs sm:text-sm">
                                            <span className="font-semibold text-slate-800 truncate max-w-[200px] sm:max-w-xs">{col.nombre_col}</span>
                                            <span className="text-slate-500 font-bold text-xs shrink-0">{col.total} postulantes</span>
                                        </div>
                                        <div className="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                            <div
                                                className="h-full bg-indigo-600 rounded-full"
                                                style={{ width: `${widthPercent}%` }}
                                            />
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>
            </section>

            {/* 7. Sección: Plantillas académicas */}
            <section className="mb-8">
                <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden">
                    <CardHeader className="border-b border-slate-100">
                        <CardTitle className="text-lg text-slate-900 flex items-center gap-2">
                            <BookCopy className="h-5 w-5 text-indigo-600" />
                            Plantillas académicas estructuradas
                        </CardTitle>
                        <CardDescription>
                            Detalle de instrumentos de medición configurados en la plataforma.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="p-0 overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-slate-50/80">
                                    <TableHead className="pl-5">Nombre de la Plantilla</TableHead>
                                    <TableHead>Dificultad</TableHead>
                                    <TableHead className="text-center">Cantidad de Reactivos</TableHead>
                                    <TableHead className="text-center">Puntaje Máximo</TableHead>
                                    <TableHead className="pr-5">Estado</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {plantillasDetalle.map((plantilla) => (
                                    <TableRow key={plantilla.id_plan}>
                                        <TableCell className="pl-5 font-semibold text-slate-900">
                                            {plantilla.nombre_plan}
                                        </TableCell>
                                        <TableCell>
                                            <span className="capitalize text-xs font-semibold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full">
                                                {plantilla.dificultad_plan}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-center font-bold text-slate-700">
                                            {plantilla.preguntas_count}
                                        </TableCell>
                                        <TableCell className="text-center font-bold text-indigo-600">
                                            {plantilla.puntaje_total} Pts
                                        </TableCell>
                                        <TableCell className="pr-5">
                                            <StatusBadge status={plantilla.estado_plan === 'activo' ? 'Activa' : plantilla.estado_plan} />
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </section>

            {/* 8. Sección: Lectura académica preliminar */}
            <section className="mb-8">
                <Card className="border-0 bg-gradient-to-br from-white to-sky-50/70 shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg text-slate-900">
                            <Lightbulb className="h-5 w-5 text-amber-500" />
                            Lectura académica preliminar
                        </CardTitle>
                        <CardDescription>
                            Orientaciones derivadas de los indicadores institucionales consolidados.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 lg:grid-cols-3">
                        <div className="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <TrendingUp className="h-4 w-4" />
                            </span>
                            <p className="text-sm leading-relaxed text-slate-700">
                                Reforzar áreas con mayor concentración de dificultad avanzada.
                            </p>
                        </div>
                        <div className="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <Target className="h-4 w-4" />
                            </span>
                            <p className="text-sm leading-relaxed text-slate-700">
                                Priorizar seguimiento a postulantes orientados a carreras de alta exigencia matemática.
                            </p>
                        </div>
                        <div className="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <Sparkles className="h-4 w-4" />
                            </span>
                            <p className="text-sm leading-relaxed text-slate-700">
                                Usar plantillas diagnósticas para identificar brechas antes de evaluaciones finales.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </section>

            {/* 9. Sección: Learning Analytics Explanatory Card */}
            <section>
                <Alert className="border-indigo-150 bg-indigo-50/70 p-5 text-indigo-950 shadow-sm rounded-2xl">
                    <TrendingUp className="h-5 w-5 text-indigo-600" />
                    <AlertTitle className="font-bold text-indigo-900">Learning Analytics</AlertTitle>
                    <AlertDescription className="mt-2 text-sm text-indigo-700 leading-relaxed">
                        Los indicadores presentados permiten consolidar información académica para una futura capa de Learning Analytics orientada a riesgo académico, recomendaciones de refuerzo y predicción de desempeño.
                    </AlertDescription>
                </Alert>
            </section>
        </AdminLayout>
    );
}
