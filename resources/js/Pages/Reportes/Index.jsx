import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import MetricCard from '@/Components/MetricCard';
import StatusBadge from '@/Components/StatusBadge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { 
    GraduationCap, 
    Building2, 
    SlidersHorizontal, 
    BookCopy, 
    FileQuestion, 
    TrendingUp, 
    Award, 
    Lightbulb, 
    Sparkles, 
    CheckCircle2, 
    AlertTriangle, 
    Search,
    ChevronDown,
    ChevronUp,
    BookmarkCheck,
    Layers,
    Activity,
    Compass,
    Calendar,
    Users
} from 'lucide-react';

export default function Index({
    metricas,
    postulantesPorUniversidad = [],
    postulantesPorCarrera = [],
    postulantesPorColegio = [],
    preguntasPorArea = [],
    preguntasPorDificultad = [],
    plantillasDetalle = [],
    postulantesPorExigenciaMatematica = [],
    postulantesList = []
}) {
    // Estados para el Buscador y Filtros locales de Postulantes
    const [searchQuery, setSearchQuery] = useState('');
    const [filterUni, setFilterUni] = useState('');
    const [filterCarrera, setFilterCarrera] = useState('');
    const [filterExigencia, setFilterExigencia] = useState('');
    const [filterEstado, setFilterEstado] = useState('');
    const [expandedPostulanteId, setExpandedPostulanteId] = useState(null);

    // Obtener valores únicos para poblar los selects de filtros
    const universidadesUnicas = Array.from(new Set(postulantesList.map(p => p.universidad).filter(Boolean)));
    const carrerasUnicas = Array.from(new Set(postulantesList.map(p => p.carrera).filter(Boolean)));
    const exigenciasUnicas = Array.from(new Set(postulantesList.map(p => p.exigencia).filter(Boolean)));
    const estadosUnicos = Array.from(new Set(postulantesList.map(p => p.estado).filter(Boolean)));

    // Filtrar postulantes
    const filteredPostulantes = postulantesList.filter(postulante => {
        const matchesSearch = 
            postulante.nombre_completo.toLowerCase().includes(searchQuery.toLowerCase()) ||
            postulante.colegio.toLowerCase().includes(searchQuery.toLowerCase()) ||
            postulante.carrera.toLowerCase().includes(searchQuery.toLowerCase()) ||
            postulante.universidad.toLowerCase().includes(searchQuery.toLowerCase());

        const matchesUni = filterUni ? postulante.universidad === filterUni : true;
        const matchesCarrera = filterCarrera ? postulante.carrera === filterCarrera : true;
        const matchesExigencia = filterExigencia ? postulante.exigencia === filterExigencia : true;
        const matchesEstado = filterEstado ? postulante.estado === filterEstado : true;

        return matchesSearch && matchesUni && matchesCarrera && matchesExigencia && matchesEstado;
    });

    const toggleExpandPostulante = (id) => {
        setExpandedPostulanteId(prev => (prev === id ? null : id));
    };

    // 1. Métricas superiores
    const metricsList = [
        {
            title: 'Postulantes registrados',
            value: (metricas?.totalPostulantes ?? 0).toString(),
            detail: 'Seguimiento activo del proceso',
            trend: 'neutral',
            icon: GraduationCap,
            accent: 'indigo',
        },
        {
            title: 'Universidades objetivo',
            value: (metricas?.totalUniversidades ?? 0).toString(),
            detail: 'Destinos académicos de interés',
            trend: 'neutral',
            icon: Building2,
            accent: 'blue',
        },
        {
            title: 'Carreras postuladas',
            value: (metricas?.totalCarreras ?? 0).toString(),
            detail: 'Ofertas académicas asociadas',
            trend: 'neutral',
            icon: SlidersHorizontal,
            accent: 'violet',
        },
        {
            title: 'Colegios de procedencia',
            value: (metricas?.totalColegios ?? 0).toString(),
            detail: 'Unidades educativas representadas',
            trend: 'neutral',
            icon: Building2,
            accent: 'rose',
        },
        {
            title: 'Banco de preguntas',
            value: (metricas?.totalPreguntas ?? 0).toString(),
            detail: 'Reactivos metodológicos en base',
            trend: 'neutral',
            icon: FileQuestion,
            accent: 'cyan',
        },
        {
            title: 'Áreas evaluadas',
            value: (metricas?.totalAreas ?? 0).toString(),
            detail: 'Competencias lógicas clave',
            trend: 'neutral',
            icon: Layers,
            accent: 'sky',
        },
        {
            title: 'Temas registrados',
            value: (metricas?.totalTemas ?? 0).toString(),
            detail: 'Subcategorías lógico-matemáticas',
            trend: 'neutral',
            icon: BookmarkCheck,
            accent: 'emerald',
        },
        {
            title: 'Plantillas académicas',
            value: (metricas?.totalPlantillas ?? 0).toString(),
            detail: 'Exámenes configurados',
            trend: 'neutral',
            icon: BookCopy,
            accent: 'amber',
        },
    ];

    // Cálculos dinámicos para conclusiones
    const uniLider = postulantesPorUniversidad?.[0]?.sigla_uni ?? 'UMSA';
    const carreraLider = postulantesPorCarrera?.[0]?.nombre_car ?? 'Ingeniería de Sistemas';
    const areaLider = preguntasPorArea?.[0]?.nombre_area ?? 'Razonamiento Lógico-Matemático';
    const difPredominante = preguntasPorDificultad?.[0]?.dificultad_preg ?? 'media';

    // Obtener recomendación de nivelación según exigencia
    const getRecomendacionNivelacion = (exigencia) => {
        if (exigencia === 'alta') {
            return 'Priorizar refuerzo en álgebra, trigonometría y resolución de problemas.';
        } else if (exigencia === 'media-alta') {
            return 'Reforzar razonamiento lógico y planteo de ecuaciones.';
        } else if (exigencia === 'media') {
            return 'Mantener seguimiento regular con evaluación diagnóstica.';
        } else {
            return 'Aplicar nivelación focalizada según resultados iniciales.';
        }
    };

    return (
        <AdminLayout
            title="Reportes Académicos"
            subtitle="Indicadores institucionales para el seguimiento del desempeño lógico-matemático preuniversitario."
        >
            <Head title="Reportes Académicos - INTELECTA" />

            <div className="space-y-8 max-w-7xl mx-auto">
                
                {/* 1. Tarjeta de lectura rápida */}
                <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div className="space-y-2">
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-700/10">
                            <Sparkles className="h-3 w-3 animate-pulse" />
                            Lectura Rápida
                        </span>
                        <p className="text-sm text-slate-650 max-w-4xl leading-relaxed">
                            Este panel consolida información de postulantes, universidades, carreras, colegios, banco de preguntas y plantillas académicas para apoyar la toma de decisiones del instituto. Permite un análisis modular de competencias y procedencias estudiantiles.
                        </p>
                    </div>
                    <Link href="/dashboard" className="shrink-0 w-full md:w-auto">
                        <button className="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-10 px-4 transition">
                            Volver al Dashboard
                        </button>
                    </Link>
                </div>

                {/* 2. KPIs superiores */}
                <section>
                    <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
                        {metricsList.map((metric) => {
                            const Icon = metric.icon;
                            let cardColor = 'text-indigo-600 bg-indigo-50/50 hover:ring-indigo-300';
                            if (metric.accent === 'blue') cardColor = 'text-blue-600 bg-blue-50/50 hover:ring-blue-300';
                            if (metric.accent === 'violet') cardColor = 'text-violet-600 bg-violet-50/50 hover:ring-violet-300';
                            if (metric.accent === 'rose') cardColor = 'text-rose-600 bg-rose-50/50 hover:ring-rose-300';
                            if (metric.accent === 'cyan') cardColor = 'text-cyan-600 bg-cyan-50/50 hover:ring-cyan-300';
                            if (metric.accent === 'sky') cardColor = 'text-sky-600 bg-sky-50/50 hover:ring-sky-300';
                            if (metric.accent === 'emerald') cardColor = 'text-emerald-600 bg-emerald-50/50 hover:ring-emerald-300';
                            if (metric.accent === 'amber') cardColor = 'text-amber-600 bg-amber-50/50 hover:ring-amber-300';

                            return (
                                <Card key={metric.title} className={`border-0 bg-white shadow-sm ring-1 ring-slate-200/80 transition duration-300 ${cardColor}`}>
                                    <CardHeader className="p-4 pb-2">
                                        <div className="flex items-center justify-between">
                                            <CardDescription className="text-[10px] font-bold uppercase tracking-wider text-slate-500 truncate max-w-[120px]">
                                                {metric.title}
                                            </CardDescription>
                                            <span className="p-1 rounded-lg bg-white shadow-sm">
                                                <Icon className="h-4 w-4" />
                                            </span>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="p-4 pt-0">
                                        <p className="text-2xl font-black text-slate-900 leading-none">{metric.value}</p>
                                        <p className="text-[9px] text-slate-400 mt-1 font-semibold leading-none">{metric.detail}</p>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                </section>

                {/* 3. Panel Estado general del instituto */}
                <section className="grid gap-6 md:grid-cols-3">
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <GraduationCap className="h-5 w-5 text-indigo-600" />
                                Demanda Académica
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-4 text-xs font-medium text-slate-600">
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Universidad principal</span>
                                <span className="font-black text-slate-800">{uniLider}</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Carrera principal</span>
                                <span className="font-black text-slate-800 truncate max-w-[150px]">{carreraLider}</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Total postulantes</span>
                                <span className="font-black text-indigo-600">{metricas?.totalPostulantes ?? 0}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <BookCopy className="h-5 w-5 text-indigo-600" />
                                Cobertura Evaluativa
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-4 text-xs font-medium text-slate-600">
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Áreas evaluadas</span>
                                <span className="font-black text-slate-800">{metricas?.totalAreas ?? 0}</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Preguntas totales</span>
                                <span className="font-black text-slate-800">{metricas?.totalPreguntas ?? 0}</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Plantillas activas</span>
                                <span className="font-black text-indigo-600">{metricas?.totalPlantillas ?? 0}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <Activity className="h-5 w-5 text-indigo-600" />
                                Preparación para Learning Analytics
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-4 text-xs font-medium text-slate-600">
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Registros consolidados</span>
                                <span className="font-black text-emerald-600">Listo</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Perfiles exigencia</span>
                                <span className="font-black text-slate-800">4 niveles</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span>Base analítica</span>
                                <span className="font-black text-indigo-650">Completada</span>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {/* 4. Gráficos Visuales */}
                <section className="grid gap-6 md:grid-cols-2">
                    
                    {/* A. Concentric SVG Ring Chart - Postulantes por universidad */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <Building2 className="h-4.5 w-4.5 text-indigo-600" />
                                Postulantes por universidad
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500">
                                Segmentación y porcentaje de postulantes agrupados por universidad meta.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col sm:flex-row items-center justify-around gap-6 pt-2">
                            {/* SVG Donut Visual */}
                            <div className="relative flex items-center justify-center h-32 w-32 shrink-0">
                                <svg className="h-32 w-32 transform -rotate-90">
                                    <circle cx="64" cy="64" r="50" stroke="#f1f5f9" strokeWidth="6" fill="transparent" />
                                    {postulantesPorUniversidad.map((uni, idx) => {
                                        const percent = metricas.totalPostulantes > 0 ? uni.total / metricas.totalPostulantes : 0;
                                        const radius = 50 - (idx * 10);
                                        const circumference = 2 * Math.PI * radius;
                                        const strokeDashoffset = circumference - (percent * circumference);
                                        const strokeColor = idx === 0 ? '#6366f1' : idx === 1 ? '#06b6d4' : idx === 2 ? '#a855f7' : '#94a3b8';
                                        
                                        return (
                                            <circle 
                                                key={uni.sigla_uni}
                                                cx="64" 
                                                cy="64" 
                                                r={radius} 
                                                stroke={strokeColor} 
                                                strokeWidth="6" 
                                                strokeDasharray={circumference}
                                                strokeDashoffset={strokeDashoffset}
                                                fill="transparent"
                                                className="transition-all duration-500"
                                            />
                                        );
                                    })}
                                </svg>
                                <div className="absolute text-center">
                                    <span className="block text-xl font-black text-slate-900">{metricas.totalPostulantes}</span>
                                    <span className="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Postulantes</span>
                                </div>
                            </div>

                            {/* Leyenda */}
                            <div className="flex-1 space-y-2.5 w-full">
                                {postulantesPorUniversidad.map((uni, idx) => {
                                    const percent = metricas.totalPostulantes > 0 ? Math.round((uni.total / metricas.totalPostulantes) * 100) : 0;
                                    const badgeColor = idx === 0 ? 'bg-indigo-500' : idx === 1 ? 'bg-cyan-500' : idx === 2 ? 'bg-purple-500' : 'bg-slate-400';
                                    
                                    return (
                                        <div key={uni.sigla_uni} className="flex items-center justify-between text-xs">
                                            <div className="flex items-center gap-2">
                                                <span className={`h-2.5 w-2.5 rounded-full ${badgeColor}`} />
                                                <span className="font-semibold text-slate-700">{uni.sigla_uni}</span>
                                            </div>
                                            <span className="text-slate-500 font-bold">
                                                {uni.total} ({percent}%)
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>

                    {/* B. Barras Horizontales - Postulantes por carrera */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <SlidersHorizontal className="h-4.5 w-4.5 text-indigo-600" />
                                Postulantes por carrera
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500">
                                Demanda registrada y distribución de carreras seleccionadas.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 pt-2">
                            {postulantesPorCarrera.map((car, index) => {
                                const isTop = index === 0;
                                const maxVal = postulantesPorCarrera?.[0]?.total ?? 1;
                                const widthPercent = Math.round((car.total / maxVal) * 100);
                                const barColor = isTop ? 'bg-indigo-650' : 'bg-indigo-400/80';
                                
                                return (
                                    <div key={car.nombre_car} className="flex items-center gap-3">
                                        <span className={`flex h-5.5 w-5.5 shrink-0 items-center justify-center rounded-lg text-[10px] font-black ${
                                            isTop ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-500'
                                        }`}>
                                            {index + 1}
                                        </span>
                                        <div className="flex-1 space-y-1">
                                            <div className="flex justify-between items-center text-xs">
                                                <span className="font-semibold text-slate-700 truncate max-w-[200px]">{car.nombre_car}</span>
                                                <span className="font-bold text-slate-800">{car.total} postulantes</span>
                                            </div>
                                            <div className="h-2 w-full bg-slate-50 rounded-full overflow-hidden border border-slate-100">
                                                <div 
                                                    className={`h-full ${barColor} rounded-full transition-all duration-500`}
                                                    style={{ width: `${widthPercent}%` }}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>

                    {/* C. Barras Horizontales - Postulantes por colegio */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <GraduationCap className="h-4.5 w-4.5 text-indigo-600" />
                                Postulantes por colegio
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500">
                                Unidades educativas paceñas con mayor volumen de egresados postulando.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 pt-2">
                            {postulantesPorColegio.map((col, index) => {
                                const maxVal = postulantesPorColegio?.[0]?.total ?? 1;
                                const widthPercent = Math.round((col.total / maxVal) * 100);
                                
                                return (
                                    <div key={col.nombre_col} className="flex items-center gap-3">
                                        <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[9px] font-bold text-slate-500">
                                            {index + 1}
                                        </span>
                                        <div className="flex-1 space-y-1">
                                            <div className="flex justify-between items-center text-xs">
                                                <span className="font-semibold text-slate-700 truncate max-w-[220px]">{col.nombre_col}</span>
                                                <span className="font-bold text-slate-850">{col.total} postulantes</span>
                                            </div>
                                            <div className="h-1.5 w-full bg-slate-50 rounded-full overflow-hidden border border-slate-150">
                                                <div 
                                                    className="h-full bg-blue-500/80 rounded-full transition-all duration-500"
                                                    style={{ width: `${widthPercent}%` }}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>

                    {/* D. Cobertura de Preguntas por Área */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <Layers className="h-4.5 w-4.5 text-indigo-600" />
                                Cobertura del banco por área lógico-matemática
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500">
                                Reactivos estructurados y distribuidos por área analítica.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3.5 pt-2">
                            {preguntasPorArea.map((area) => {
                                const maxVal = preguntasPorArea?.[0]?.total ?? 1;
                                const widthPercent = Math.round((area.total / maxVal) * 100);
                                
                                return (
                                    <div key={area.nombre_area} className="space-y-1">
                                        <div className="flex justify-between items-center text-xs">
                                            <span className="font-semibold text-slate-700">{area.nombre_area}</span>
                                            <span className="font-bold text-slate-900">{area.total} reactivos</span>
                                        </div>
                                        <div className="h-2 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                                            <div 
                                                className="h-full bg-gradient-to-r from-blue-600 to-cyan-500 rounded-full transition-all duration-500"
                                                style={{ width: `${widthPercent}%` }}
                                            />
                                        </div>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>

                    {/* E. Dificultad de Preguntas (Stacked Progress Bar) */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <TrendingUp className="h-4.5 w-4.5 text-indigo-600" />
                                Dificultad del banco de reactivos
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500">
                                Equilibrio de complejidad en las preguntas registradas.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6 pt-2">
                            {/* Stacked segmented bar */}
                            <div className="space-y-2">
                                <div className="flex justify-between items-center text-[10px] uppercase font-bold text-slate-400">
                                    <span>Segmentación de dificultad</span>
                                    <span>100% reactivos</span>
                                </div>
                                <div className="h-4 w-full bg-slate-100 rounded-xl overflow-hidden flex">
                                    {preguntasPorDificultad.map((dif, idx) => {
                                        const percent = metricas.totalPreguntas > 0 ? (dif.total / metricas.totalPreguntas) * 100 : 0;
                                        const colors = idx === 0 ? 'bg-amber-500' : idx === 1 ? 'bg-rose-500' : 'bg-emerald-500';
                                        return (
                                            <div 
                                                key={dif.dificultad_preg}
                                                className={`h-full ${colors}`}
                                                style={{ width: `${percent}%` }}
                                                title={`${dif.dificultad_preg}: ${Math.round(percent)}%`}
                                            />
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Legend details */}
                            <div className="grid grid-cols-3 gap-3">
                                {preguntasPorDificultad.map((dif, idx) => {
                                    const percent = metricas.totalPreguntas > 0 ? Math.round((dif.total / metricas.totalPreguntas) * 100) : 0;
                                    const dotColor = idx === 0 ? 'bg-amber-500' : idx === 1 ? 'bg-rose-500' : 'bg-emerald-500';
                                    const label = dif.dificultad_preg === 'basica' ? 'Básica' : dif.dificultad_preg === 'media' ? 'Media' : 'Avanzada';
                                    
                                    return (
                                        <div key={dif.dificultad_preg} className="border border-slate-100 rounded-2xl p-3 bg-slate-50/50 flex flex-col justify-between">
                                            <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-semibold uppercase">
                                                <span className={`h-2 w-2 rounded-full ${dotColor}`} />
                                                <span>{label}</span>
                                            </div>
                                            <p className="mt-2 text-base font-black text-slate-900">{dif.total}</p>
                                            <span className="text-[10px] text-indigo-600 font-bold">{percent}% del banco</span>
                                        </div>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>

                    {/* F. Exigencia Matemática */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <Award className="h-4.5 w-4.5 text-indigo-600" />
                                Distribución por nivel de exigencia matemática
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500">
                                Clasificación de postulantes según la exigencia lógica de su carrera objetivo.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 pt-2">
                            {postulantesPorExigenciaMatematica.map((ex) => {
                                const percent = metricas.totalPostulantes > 0 ? Math.round((ex.total / metricas.totalPostulantes) * 100) : 0;
                                const label = ex.nivel === 'alta' ? 'Alta' : ex.nivel === 'media-alta' ? 'Media-Alta' : ex.nivel === 'media' ? 'Media' : 'Baja-Media';
                                const barColor = ex.nivel === 'alta' ? 'from-rose-500 to-rose-400' : ex.nivel === 'media-alta' ? 'from-amber-500 to-amber-400' : ex.nivel === 'media' ? 'from-blue-500 to-blue-400' : 'from-emerald-500 to-emerald-400';
                                
                                return (
                                    <div key={ex.nivel} className="space-y-1">
                                        <div className="flex justify-between items-center text-xs">
                                            <span className="font-semibold text-slate-700">{label}</span>
                                            <span className="font-bold text-slate-900">{ex.total} postulantes ({percent}%)</span>
                                        </div>
                                        <div className="h-2 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                                            <div 
                                                className={`h-full bg-gradient-to-r ${barColor} rounded-full transition-all duration-500`}
                                                style={{ width: `${percent}%` }}
                                            />
                                        </div>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                </section>

                {/* 5. Sección Plantillas Académicas */}
                <section className="space-y-4">
                    <div className="border-b border-slate-200 pb-2">
                        <h2 className="text-lg font-black text-slate-900">Plantillas académicas estructuradas</h2>
                        <p className="text-xs text-slate-500">Instrumentos curriculares y de nivelación configurados en la plataforma.</p>
                    </div>

                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {plantillasDetalle.map((plantilla) => (
                            <Card key={plantilla.id_plan} className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 hover:ring-indigo-300 transition duration-300 flex flex-col justify-between">
                                <CardHeader className="pb-3 border-b border-slate-50">
                                    <div className="flex items-start justify-between gap-3">
                                        <h3 className="text-sm font-bold text-slate-900 leading-snug">{plantilla.nombre_plan}</h3>
                                        <span className="capitalize text-[9px] font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full shrink-0">
                                            {plantilla.dificultad_plan}
                                        </span>
                                    </div>
                                </CardHeader>
                                <CardContent className="pt-4 space-y-4">
                                    <div className="flex justify-between text-xs text-slate-500">
                                        <span>Cantidad de reactivos:</span>
                                        <span className="font-bold text-slate-800">{plantilla.cantidad_preguntas}</span>
                                    </div>
                                    
                                    <div className="space-y-1.5">
                                        <div className="flex justify-between text-xs font-semibold text-indigo-700">
                                            <span>Puntaje de examen:</span>
                                            <span>{plantilla.puntaje_total} / 100 Pts</span>
                                        </div>
                                        <div className="h-2 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                                            <div 
                                                className="h-full bg-indigo-600 rounded-full transition-all"
                                                style={{ width: `${plantilla.puntaje_total}%` }}
                                            />
                                        </div>
                                    </div>
                                </CardContent>
                                <div className="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl">
                                    <span className="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Todas sobre 100 Pts</span>
                                    <StatusBadge status={plantilla.estado_plan === 'activo' ? 'Activa' : plantilla.estado_plan} />
                                </div>
                            </Card>
                        ))}
                    </div>
                </section>

                {/* 6. Nueva sección Reporte por Postulante */}
                <section className="space-y-6">
                    <div className="border-b border-slate-200 pb-2">
                        <h2 className="text-lg font-black text-slate-900">Reporte por postulante</h2>
                        <p className="text-xs text-slate-500">Vista individual para identificar perfil académico, universidad objetivo y nivel de exigencia matemática.</p>
                    </div>

                    {/* Barra de Búsqueda y Filtros */}
                    <div className="grid gap-4 md:grid-cols-5 bg-white p-5 rounded-3xl ring-1 ring-slate-200/80 shadow-sm">
                        {/* Buscador */}
                        <div className="relative md:col-span-2">
                            <span className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search className="h-4.5 w-4.5 text-slate-400" />
                            </span>
                            <input
                                type="text"
                                placeholder="Buscar postulante, colegio, universidad o carrera..."
                                value={searchQuery}
                                onChange={e => setSearchQuery(e.target.value)}
                                className="pl-10 h-10 w-full rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                        </div>

                        {/* Filtro Universidad */}
                        <select
                            value={filterUni}
                            onChange={e => setFilterUni(e.target.value)}
                            className="h-10 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="">Universidad (Todas)</option>
                            {universidadesUnicas.map(u => (
                                <option key={u} value={u}>{u}</option>
                            ))}
                        </select>

                        {/* Filtro Carrera */}
                        <select
                            value={filterCarrera}
                            onChange={e => setFilterCarrera(e.target.value)}
                            className="h-10 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="">Carrera (Todas)</option>
                            {carrerasUnicas.map(c => (
                                <option key={c} value={c}>{c}</option>
                            ))}
                        </select>

                        {/* Filtro Exigencia */}
                        <select
                            value={filterExigencia}
                            onChange={e => setFilterExigencia(e.target.value)}
                            className="h-10 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="">Exigencia (Todas)</option>
                            {exigenciasUnicas.map(ex => {
                                const label = ex === 'alta' ? 'Alta' : ex === 'media-alta' ? 'Media-Alta' : ex === 'media' ? 'Media' : 'Baja-Media';
                                return <option key={ex} value={ex}>{label}</option>;
                            })}
                        </select>
                    </div>

                    {/* Tabla de Postulantes */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden">
                        <CardContent className="p-0 overflow-x-auto">
                            {filteredPostulantes.length === 0 ? (
                                <div className="text-center py-10 space-y-2">
                                    <AlertTriangle className="h-8 w-8 text-slate-400 mx-auto" />
                                    <h3 className="text-sm font-bold text-slate-800">No se encontraron registros</h3>
                                    <p className="text-xs text-slate-500">Ningún postulante coincide con la búsqueda o filtros seleccionados.</p>
                                </div>
                            ) : (
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50/80">
                                            <TableHead className="pl-5">Postulante</TableHead>
                                            <TableHead>Colegio</TableHead>
                                            <TableHead>Universidad / Carrera</TableHead>
                                            <TableHead>Exigencia</TableHead>
                                            <TableHead>Perfil Académico Preliminar</TableHead>
                                            <TableHead className="text-center">Lectura</TableHead>
                                            <TableHead className="pr-5 text-right">Estado</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredPostulantes.map((post) => {
                                            const isExpanded = expandedPostulanteId === post.id_post;
                                            
                                            // Badges de exigencia
                                            let exigenciaBadge = 'bg-emerald-50 text-emerald-700 ring-emerald-700/10';
                                            let exigencyLabel = 'Baja-Media';
                                            if (post.exigencia === 'alta') {
                                                exigenciaBadge = 'bg-rose-50 text-rose-700 ring-rose-700/10';
                                                exigencyLabel = 'Alta';
                                            } else if (post.exigencia === 'media-alta') {
                                                exigenciaBadge = 'bg-amber-50 text-amber-700 ring-amber-700/10';
                                                exigencyLabel = 'Media-Alta';
                                            } else if (post.exigencia === 'media') {
                                                exigenciaBadge = 'bg-blue-50 text-blue-700 ring-blue-700/10';
                                                exigencyLabel = 'Media';
                                            }

                                            // Badges de perfil
                                            let perfilBadge = 'bg-slate-100 text-slate-700';
                                            if (post.perfil === 'Requiere seguimiento intensivo') {
                                                perfilBadge = 'bg-rose-100/60 text-rose-800';
                                            } else if (post.perfil === 'Seguimiento regular recomendado') {
                                                perfilBadge = 'bg-blue-100/60 text-blue-800';
                                            } else {
                                                perfilBadge = 'bg-emerald-100/60 text-emerald-800';
                                            }

                                            return (
                                                <React.Fragment key={post.id_post}>
                                                    <TableRow className="hover:bg-slate-50/50 transition">
                                                        <TableCell className="pl-5 font-bold text-slate-900">
                                                            <div>
                                                                <p className="text-sm">{post.nombre_completo}</p>
                                                                <p className="text-[10px] text-slate-400 font-semibold">{post.edad} años</p>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell className="text-slate-600 text-xs font-semibold max-w-[150px] truncate">
                                                            {post.colegio}
                                                        </TableCell>
                                                        <TableCell className="text-xs">
                                                            <div className="font-semibold text-slate-800">{post.carrera}</div>
                                                            <div className="text-[10px] text-slate-400 font-semibold uppercase">{post.universidad}</div>
                                                        </TableCell>
                                                        <TableCell>
                                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 ${exigenciaBadge}`}>
                                                                {exigencyLabel}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell>
                                                            <span className={`inline-flex items-center rounded-xl px-2.5 py-1 text-[10px] font-bold ${perfilBadge}`}>
                                                                {post.perfil}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-center">
                                                            <button
                                                                onClick={() => toggleExpandPostulante(post.id_post)}
                                                                className="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-650 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-xl transition"
                                                            >
                                                                <span>Ver lectura académica</span>
                                                                {isExpanded ? <ChevronUp className="h-3 w-3" /> : <ChevronDown className="h-3 w-3" />}
                                                            </button>
                                                        </TableCell>
                                                        <TableCell className="pr-5 text-right">
                                                            <StatusBadge status={post.estado === 'activo' ? 'Activo' : post.estado} />
                                                        </TableCell>
                                                    </TableRow>
                                                    
                                                    {/* Fila expandible con lectura académica */}
                                                    {isExpanded && (
                                                        <TableRow className="bg-slate-50/55 hover:bg-slate-50/55">
                                                            <TableCell colSpan={7} className="px-6 py-4">
                                                                <div className="rounded-2xl border border-indigo-100/50 bg-white p-4 space-y-3">
                                                                    <div className="flex items-center gap-2 text-xs font-bold text-indigo-850">
                                                                        <Lightbulb className="h-4 w-4 text-amber-500 shrink-0" />
                                                                        <span>Lectura Institucional y Recomendaciones</span>
                                                                    </div>
                                                                    <div className="grid gap-4 sm:grid-cols-3 text-xs leading-relaxed">
                                                                        <div>
                                                                            <span className="text-[10px] uppercase font-bold text-slate-400">Seguimiento sugerido</span>
                                                                            <p className="mt-1 font-semibold text-slate-800">{post.perfil}</p>
                                                                        </div>
                                                                        <div className="sm:col-span-2">
                                                                            <span className="text-[10px] uppercase font-bold text-slate-400">Recomendación de nivelación</span>
                                                                            <p className="mt-1 text-slate-650">
                                                                                {getRecomendacionNivelacion(post.exigencia)} Basado en las ponderaciones curriculares exigidas por la carrera de {post.carrera} en la {post.universidad}.
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </TableCell>
                                                        </TableRow>
                                                    )}
                                                </React.Fragment>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            )}
                        </CardContent>
                    </Card>
                </section>

                {/* 7. Sección Lectura Académica Preliminar */}
                <section className="space-y-4">
                    <div className="border-b border-slate-200 pb-2">
                        <h2 className="text-lg font-black text-slate-900">Lectura académica preliminar</h2>
                        <p className="text-xs text-slate-500">Conclusiones analíticas y pautas derivadas de la masa crítica del instituto.</p>
                    </div>

                    <div className="grid gap-4 lg:grid-cols-4">
                        {/* Conclusión 1 */}
                        <div className="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div className="space-y-2">
                                <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-650">
                                    <Building2 className="h-4 w-4" />
                                </span>
                                <h3 className="text-xs font-black uppercase tracking-wider text-slate-800">Concentración por universidad</h3>
                                <p className="text-xs leading-relaxed text-slate-500">
                                    La mayor concentración de postulantes se encuentra enfocada en la {uniLider}.
                                </p>
                            </div>
                            <p className="mt-4 pt-3 border-t border-slate-100 text-[10px] font-bold text-indigo-700 leading-normal">
                                Recomendación: Priorizar la calendarización de plantillas evaluativas asociadas a la {uniLider} para validar el nivel inicial.
                            </p>
                        </div>

                        {/* Conclusión 2 */}
                        <div className="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div className="space-y-2">
                                <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-655">
                                    <SlidersHorizontal className="h-4 w-4" />
                                </span>
                                <h3 className="text-xs font-black uppercase tracking-wider text-slate-800">Carrera con mayor demanda</h3>
                                <p className="text-xs leading-relaxed text-slate-500">
                                    La carrera de {carreraLider} presenta el mayor volumen de postulantes inscritos.
                                </p>
                            </div>
                            <p className="mt-4 pt-3 border-t border-slate-100 text-[10px] font-bold text-indigo-700 leading-normal">
                                Recomendación: Reforzar los módulos de álgebra y razonamiento lógico-matemático en los talleres de nivelación de esta rama.
                            </p>
                        </div>

                        {/* Conclusión 3 */}
                        <div className="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div className="space-y-2">
                                <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-655">
                                    <Layers className="h-4 w-4" />
                                </span>
                                <h3 className="text-xs font-black uppercase tracking-wider text-slate-800">Cobertura del banco</h3>
                                <p className="text-xs leading-relaxed text-slate-500">
                                    El área de {areaLider} contiene la mayor cantidad de reactivos cargados.
                                </p>
                            </div>
                            <p className="mt-4 pt-3 border-t border-slate-100 text-[10px] font-bold text-indigo-700 leading-normal">
                                Recomendación: Ampliar la base de reactivos complejos en las áreas complementarias para equilibrar la cobertura de plantillas.
                            </p>
                        </div>

                        {/* Conclusión 4 */}
                        <div className="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div className="space-y-2">
                                <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-655">
                                    <TrendingUp className="h-4 w-4" />
                                </span>
                                <h3 className="text-xs font-black uppercase tracking-wider text-slate-800">Dificultad predominante</h3>
                                <p className="text-xs leading-relaxed text-slate-500">
                                    El banco de preguntas mantiene una concentración mayoritaria de complejidad {difPredominante}.
                                </p>
                            </div>
                            <p className="mt-4 pt-3 border-t border-slate-100 text-[10px] font-bold text-indigo-700 leading-normal">
                                Recomendación: Incorporar de manera progresiva reactivos de nivel avanzado para simular la exigencia real de ingenierías.
                            </p>
                        </div>
                    </div>
                </section>

                {/* 8. Sección Base para Learning Analytics */}
                <section>
                    <div className="rounded-3xl border border-indigo-100 bg-indigo-50/60 p-6 sm:p-8 space-y-6 shadow-sm">
                        <div className="flex gap-4 items-start">
                            <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                                <Sparkles className="h-6 w-6" />
                            </span>
                            <div className="space-y-1">
                                <h3 className="text-base font-bold text-indigo-950">Preparación para Learning Analytics</h3>
                                <p className="text-sm text-indigo-700 leading-relaxed max-w-4xl">
                                    Los indicadores consolidados permiten construir una base académica para futuras capas de Learning Analytics, orientadas a riesgo académico, recomendaciones de refuerzo y predicción de desempeño.
                                </p>
                            </div>
                        </div>

                        {/* Línea de proceso visual */}
                        <div className="border-t border-indigo-100 pt-6">
                            <div className="grid grid-cols-2 gap-4 md:grid-cols-5 text-center text-xs font-bold text-indigo-850">
                                <div className="p-3 bg-white rounded-xl border border-indigo-150 flex flex-col justify-center items-center shadow-sm">
                                    <span className="text-[10px] text-indigo-400 font-semibold uppercase">Paso 1</span>
                                    <span className="mt-1">Datos</span>
                                </div>
                                <div className="hidden md:flex items-center justify-center text-indigo-400">→</div>
                                <div className="p-3 bg-white rounded-xl border border-indigo-150 flex flex-col justify-center items-center shadow-sm">
                                    <span className="text-[10px] text-indigo-400 font-semibold uppercase">Paso 2</span>
                                    <span className="mt-1">Indicadores</span>
                                </div>
                                <div className="hidden md:flex items-center justify-center text-indigo-400">→</div>
                                <div className="p-3 bg-white rounded-xl border border-indigo-150 flex flex-col justify-center items-center shadow-sm md:col-span-1 col-span-2">
                                    <span className="text-[10px] text-indigo-400 font-semibold uppercase">Paso 3</span>
                                    <span className="mt-1">Lectura Académica</span>
                                </div>
                                <div className="hidden md:flex items-center justify-center text-indigo-400">→</div>
                                <div className="p-3 bg-white rounded-xl border border-indigo-150 flex flex-col justify-center items-center shadow-sm">
                                    <span className="text-[10px] text-indigo-400 font-semibold uppercase">Paso 4</span>
                                    <span className="mt-1">Recomendaciones</span>
                                </div>
                                <div className="hidden md:flex items-center justify-center text-indigo-400">→</div>
                                <div className="p-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl flex flex-col justify-center items-center shadow-md">
                                    <span className="text-[9px] text-indigo-200 font-bold uppercase tracking-wider">Futuro</span>
                                    <span className="mt-0.5">Modelo Predictivo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
