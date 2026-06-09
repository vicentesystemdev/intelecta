import React, { useState, useEffect } from 'react';
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
import {
    ResponsiveContainer,
    PieChart,
    Pie,
    Cell,
    Tooltip,
    Legend,
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    AreaChart,
    Area
} from 'recharts';


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
    const PAGE_SIZE = 15;
    const [searchQuery, setSearchQuery] = useState('');
    const [filterUni, setFilterUni] = useState('');
    const [filterCarrera, setFilterCarrera] = useState('');
    const [filterExigencia, setFilterExigencia] = useState('');
    const [filterEstado, setFilterEstado] = useState('');
    const [expandedPostulanteId, setExpandedPostulanteId] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);

    // Detección dinámica de modo oscuro
    const [isDark, setIsDark] = useState(() => 
        typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
    );
    useEffect(() => {
        const observer = new MutationObserver(() => {
            setIsDark(document.documentElement.classList.contains('dark'));
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        return () => observer.disconnect();
    }, []);

    // Colores y temas de los gráficos
    const gridColor = isDark ? '#334155' : '#e2e8f0'; // slate-700 / slate-200
    const axisColor = isDark ? '#94a3b8' : '#64748b'; // slate-400 / slate-500
    const CHART_COLORS = ['#6366f1', '#06b6d4', '#a855f7', '#3b82f6', '#f59e0b', '#10b981', '#f43f5e', '#64748b'];

    // Tooltip personalizado
    const CustomTooltip = ({ active, payload }) => {
        if (active && payload && payload.length) {
            const item = payload[0];
            const value = Number(item.value) || 0;
            const name = item.name || item.payload?.name || '';
            const percentage = item.payload?.percent !== undefined 
                ? Math.round(item.payload.percent * 100) 
                : (item.payload?.porcentaje ?? undefined);

            return (
                <div className={`rounded-xl border p-3 shadow-lg text-xs font-semibold ${
                    isDark 
                        ? 'bg-slate-950 border-slate-800 text-slate-100' 
                        : 'bg-white border-slate-200 text-slate-900'
                }`}>
                    <p className="font-black mb-1">{item.payload?.fullName || name}</p>
                    <p className="flex justify-between items-center gap-4 text-indigo-600 dark:text-indigo-400">
                        <span>Cantidad:</span>
                        <span className="font-bold text-slate-800 dark:text-slate-200">{value}</span>
                    </p>
                    {percentage !== undefined && (
                        <p className="flex justify-between items-center gap-4 text-[10px] text-slate-400 dark:text-slate-500 font-bold mt-0.5">
                            <span>Porcentaje:</span>
                            <span>{percentage}%</span>
                        </p>
                    )}
                </div>
            );
        }
        return null;
    };

    // Estado vacío para los gráficos
    const EmptyState = ({ message = "No hay datos disponibles para mostrar" }) => (
        <div className="flex flex-col items-center justify-center h-72 w-full text-slate-400 dark:text-slate-500 bg-slate-50/30 dark:bg-slate-800/10 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-6">
            <Activity className="h-8 w-8 mb-2 animate-pulse text-slate-300 dark:text-slate-700" />
            <p className="text-xs font-semibold text-center">{message}</p>
        </div>
    );


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

    // Paginación de postulantes
    const totalPages = Math.max(1, Math.ceil(filteredPostulantes.length / PAGE_SIZE));
    const pagedPostulantes = filteredPostulantes.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);
    const isFiltered = searchQuery || filterUni || filterCarrera || filterExigencia || filterEstado;

    const handleFilterChange = (setter) => (e) => {
        setter(e.target.value);
        setCurrentPage(1);
        setExpandedPostulanteId(null);
    };

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

    // Mapeos de datos para Recharts
    const dataUni = (postulantesPorUniversidad ?? []).map((uni, idx) => ({
        name: uni.sigla_uni,
        fullName: uni.sigla_uni,
        value: Number(uni.total) || 0,
        percent: (metricas?.totalPostulantes ?? 0) > 0 ? (uni.total / metricas.totalPostulantes) : 0
    }));

    const topCarreras = (postulantesPorCarrera ?? []).slice(0, 8).map((car, idx) => ({
        name: car.nombre_car.length > 20 ? car.nombre_car.substring(0, 20) + '...' : car.nombre_car,
        fullName: car.nombre_car,
        total: Number(car.total) || 0,
        isTop: idx === 0
    }));

    const topColegios = (postulantesPorColegio ?? []).slice(0, 8).map((col, idx) => ({
        name: col.nombre_col.length > 20 ? col.nombre_col.substring(0, 20) + '...' : col.nombre_col,
        fullName: col.nombre_col,
        total: Number(col.total) || 0
    }));

    const dataAreas = (preguntasPorArea ?? []).map(area => ({
        name: area.nombre_area.length > 15 ? area.nombre_area.substring(0, 15) + '...' : area.nombre_area,
        fullName: area.nombre_area,
        total: Number(area.total) || 0
    }));

    const DIFICULTAD_MAP = {
        'basica': 'Básica',
        'media': 'Media',
        'avanzada': 'Avanzada'
    };
    const DIFICULTAD_COLORS = {
        'basica': '#10b981', // emerald
        'media': '#f59e0b', // amber
        'avanzada': '#f43f5e' // rose
    };
    const dataDificultad = (preguntasPorDificultad ?? []).map(dif => {
        const name = DIFICULTAD_MAP[dif.dificultad_preg.toLowerCase()] || dif.dificultad_preg;
        return {
            name,
            fullName: `Reactivos de dificultad ${name}`,
            value: Number(dif.total) || 0,
            color: DIFICULTAD_COLORS[dif.dificultad_preg.toLowerCase()] || '#64748b',
            percent: (metricas?.totalPreguntas ?? 0) > 0 ? (dif.total / metricas.totalPreguntas) : 0
        };
    });

    const EXIGENCIA_LABELS = {
        'alta': 'Alta',
        'media-alta': 'Media-Alta',
        'media': 'Media',
        'baja-media': 'Baja-Media'
    };
    const EXIGENCIA_COLORS = {
        'Alta': '#f43f5e',
        'Media-Alta': '#f59e0b',
        'Media': '#3b82f6',
        'Baja-Media': '#10b981'
    };
    
    // Normalizar y agrupar exigencias
    const exigenciaMap = {};
    (postulantesPorExigenciaMatematica ?? []).forEach(ex => {
        if (!ex.nivel) return;
        const key = ex.nivel.toLowerCase().replace('_', '-');
        const label = EXIGENCIA_LABELS[key] || ex.nivel;
        exigenciaMap[label] = (exigenciaMap[label] || 0) + (Number(ex.total) || 0);
    });
    
    const orderedExigenciaKeys = ['Alta', 'Media-Alta', 'Media', 'Baja-Media'];
    const dataExigencia = orderedExigenciaKeys.map(key => {
        const val = exigenciaMap[key] || 0;
        return {
            name: key,
            fullName: `Exigencia matemática ${key}`,
            total: val,
            porcentaje: (metricas?.totalPostulantes ?? 0) > 0 ? Math.round((val / metricas.totalPostulantes) * 100) : 0,
            color: EXIGENCIA_COLORS[key] || '#94a3b8'
        };
    });

    // Síntesis institucional conceptual
    const dataSintesis = [
        { name: 'Áreas', cantidad: Number(metricas?.totalAreas) || 0, fullName: 'Áreas de Conocimiento' },
        { name: 'Plantillas', cantidad: Number(metricas?.totalPlantillas) || 0, fullName: 'Plantillas Académicas' },
        { name: 'Colegios', cantidad: Number(metricas?.totalColegios) || 0, fullName: 'Colegios Registrados' },
        { name: 'Carreras', cantidad: Number(metricas?.totalCarreras) || 0, fullName: 'Carreras de Interés' },
        { name: 'Postulantes', cantidad: Number(metricas?.totalPostulantes) || 0, fullName: 'Postulantes Activos' },
        { name: 'Reactivos', cantidad: Number(metricas?.totalPreguntas) || 0, fullName: 'Reactivos en el Banco' }
    ];

    return (

        <AdminLayout
            title="Reportes Académicos"
            subtitle="Indicadores institucionales para el seguimiento del desempeño lógico-matemático preuniversitario."
            wide
        >
            <Head title="Reportes Académicos - INTELECTA" />

            <div className="space-y-8">
                
                {/* 1. Tarjeta de lectura rápida */}
                <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6
                    dark:border-slate-800 dark:bg-slate-900">
                    <div className="space-y-2">
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-700/10
                            dark:bg-indigo-950 dark:text-indigo-300 dark:ring-indigo-800">
                            <Sparkles className="h-3 w-3 animate-pulse" />
                            Lectura Rápida
                        </span>
                        <p className="text-sm text-slate-600 max-w-4xl leading-relaxed dark:text-slate-400">
                            Este panel consolida información de postulantes, universidades, carreras, colegios, banco de preguntas y plantillas académicas para apoyar la toma de decisiones del instituto. Permite un análisis modular de competencias y procedencias estudiantiles.
                        </p>
                    </div>
                    <Link href="/dashboard" className="shrink-0 w-full md:w-auto">
                        <button className="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold h-10 px-4 transition
                            dark:bg-slate-700 dark:hover:bg-slate-600">
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
                                <Card key={metric.title} className={`border-0 bg-white shadow-sm ring-1 ring-slate-200/80 transition duration-300 ${cardColor}
                                    dark:bg-slate-900 dark:ring-slate-800`}>
                                    <CardHeader className="p-4 pb-2">
                                        <div className="flex items-center justify-between">
                                            <CardDescription className="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate max-w-[120px]">
                                                {metric.title}
                                            </CardDescription>
                                            <span className="p-1 rounded-lg bg-white shadow-sm dark:bg-slate-800">
                                                <Icon className="h-4 w-4" />
                                            </span>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="p-4 pt-0">
                                        <p className="text-2xl font-black text-slate-900 dark:text-slate-100 leading-none">{metric.value}</p>
                                        <p className="text-[9px] text-slate-400 mt-1 font-semibold leading-none">{metric.detail}</p>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                </section>

                {/* 3. Panel Estado general del instituto */}
                <section className="grid gap-6 md:grid-cols-3">
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <GraduationCap className="h-5 w-5 text-indigo-600" />
                                Demanda Académica
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Universidad principal</span>
                                <span className="font-black text-slate-800 dark:text-slate-200">{uniLider}</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Carrera principal</span>
                                <span className="font-black text-slate-800 truncate max-w-[150px]">{carreraLider}</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Total postulantes</span>
                                <span className="font-black text-indigo-600">{metricas?.totalPostulantes ?? 0}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <BookCopy className="h-5 w-5 text-indigo-600" />
                                Cobertura Evaluativa
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Áreas evaluadas</span>
                                <span className="font-black text-slate-800 dark:text-slate-200">{metricas?.totalAreas ?? 0}</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Preguntas totales</span>
                                <span className="font-black text-slate-800 dark:text-slate-200">{metricas?.totalPreguntas ?? 0}</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Plantillas activas</span>
                                <span className="font-black text-indigo-600">{metricas?.totalPlantillas ?? 0}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader className="pb-3 border-b border-slate-50">
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <Activity className="h-5 w-5 text-indigo-600" />
                                Preparación para Learning Analytics
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Registros consolidados</span>
                                <span className="font-black text-emerald-600">Listo</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Perfiles exigencia</span>
                                <span className="font-black text-slate-800 dark:text-slate-200">4 niveles</span>
                            </div>
                            <div className="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                <span>Base analítica</span>
                                <span className="font-black text-indigo-650">Completada</span>
                            </div>
                        </CardContent>
                    </Card>
                </section>


                {/* 4. Gráficos Visuales */}
                <section className="grid gap-6 md:grid-cols-2">
                    
                    {/* A. Postulantes por universidad (PieChart tipo Donut) */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <Building2 className="h-4.5 w-4.5 text-indigo-600 dark:text-indigo-400" />
                                Postulantes por universidad
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500 dark:text-slate-400">
                                Segmentación y porcentaje de postulantes agrupados por universidad meta.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-2">
                            {dataUni.length === 0 ? (
                                <EmptyState message="No hay datos de universidades meta registradas." />
                            ) : (
                                <div className="flex flex-col sm:flex-row items-center justify-around gap-6">
                                    {/* Recharts Donut Visual */}
                                    <div className="relative flex items-center justify-center h-72 w-full max-w-[280px] shrink-0">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={dataUni}
                                                    cx="50%"
                                                    cy="50%"
                                                    innerRadius={65}
                                                    outerRadius={95}
                                                    paddingAngle={3}
                                                    dataKey="value"
                                                >
                                                    {dataUni.map((entry, index) => (
                                                        <Cell key={`cell-${index}`} fill={CHART_COLORS[index % CHART_COLORS.length]} />
                                                    ))}
                                                </Pie>
                                                <Tooltip content={<CustomTooltip />} />
                                            </PieChart>
                                        </ResponsiveContainer>
                                        <div className="absolute text-center pointer-events-none">
                                            <span className="block text-2xl font-black text-slate-900 dark:text-slate-100">{metricas?.totalPostulantes ?? 0}</span>
                                            <span className="block text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Postulantes</span>
                                        </div>
                                    </div>

                                    {/* Leyenda */}
                                    <div className="flex-1 space-y-2.5 w-full">
                                        {dataUni.map((uni, idx) => {
                                            const percentVal = Math.round(uni.percent * 100);
                                            const color = CHART_COLORS[idx % CHART_COLORS.length];
                                            
                                            return (
                                                <div key={uni.name} className="flex items-center justify-between text-xs">
                                                    <div className="flex items-center gap-2">
                                                        <span className="h-2.5 w-2.5 rounded-full shrink-0" style={{ backgroundColor: color }} />
                                                        <span className="font-semibold text-slate-700 dark:text-slate-300">{uni.name}</span>
                                                    </div>
                                                    <span className="text-slate-500 dark:text-slate-400 font-bold">
                                                        {uni.value} ({percentVal}%)
                                                    </span>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* B. Postulantes por carrera (BarChart horizontal) */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <SlidersHorizontal className="h-4.5 w-4.5 text-indigo-600 dark:text-indigo-400" />
                                Postulantes por carrera
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500 dark:text-slate-400">
                                Demanda registrada y distribución de las top 8 carreras seleccionadas.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-2">
                            {topCarreras.length === 0 ? (
                                <EmptyState message="No hay datos de carreras registradas." />
                            ) : (
                                <div className="h-72 w-full">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart
                                            layout="vertical"
                                            data={topCarreras}
                                            margin={{ top: 5, right: 20, left: 10, bottom: 5 }}
                                        >
                                            <CartesianGrid strokeDasharray="3 3" stroke={gridColor} horizontal={false} />
                                            <XAxis type="number" stroke={axisColor} fontSize={10} tickLine={false} />
                                            <YAxis type="category" dataKey="name" stroke={axisColor} fontSize={10} width={130} tickLine={false} />
                                            <Tooltip content={<CustomTooltip />} cursor={{ fill: isDark ? '#1e293b' : '#f8fafc', opacity: 0.4 }} />
                                            <Bar dataKey="total" radius={[0, 6, 6, 0]} barSize={14}>
                                                {topCarreras.map((entry, index) => (
                                                    <Cell 
                                                        key={`cell-${index}`} 
                                                        fill={index === 0 ? '#4f46e5' : '#818cf8'} 
                                                    />
                                                ))}
                                            </Bar>
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* C. Postulantes por colegio (BarChart horizontal) */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <GraduationCap className="h-4.5 w-4.5 text-indigo-600 dark:text-indigo-400" />
                                Postulantes por colegio
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500 dark:text-slate-400">
                                Top 8 unidades educativas de procedencia con mayor representación.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-2">
                            {topColegios.length === 0 ? (
                                <EmptyState message="No hay datos de colegios registrados." />
                            ) : (
                                <div className="h-72 w-full">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart
                                            layout="vertical"
                                            data={topColegios}
                                            margin={{ top: 5, right: 20, left: 10, bottom: 5 }}
                                        >
                                            <CartesianGrid strokeDasharray="3 3" stroke={gridColor} horizontal={false} />
                                            <XAxis type="number" stroke={axisColor} fontSize={10} tickLine={false} />
                                            <YAxis type="category" dataKey="name" stroke={axisColor} fontSize={10} width={130} tickLine={false} />
                                            <Tooltip content={<CustomTooltip />} cursor={{ fill: isDark ? '#1e293b' : '#f8fafc', opacity: 0.4 }} />
                                            <Bar dataKey="total" radius={[0, 6, 6, 0]} barSize={14}>
                                                {topColegios.map((entry, index) => (
                                                    <Cell 
                                                        key={`cell-${index}`} 
                                                        fill={index === 0 ? '#0891b2' : '#22d3ee'} 
                                                    />
                                                ))}
                                            </Bar>
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* D. Banco de preguntas por área (BarChart) */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <Layers className="h-4.5 w-4.5 text-indigo-600 dark:text-indigo-400" />
                                Banco de preguntas por área
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500 dark:text-slate-400">
                                Cobertura del banco de reactivos por área lógico-matemática.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-2">
                            {dataAreas.length === 0 ? (
                                <EmptyState message="No hay preguntas ni áreas de conocimiento registradas." />
                            ) : (
                                <div className="h-72 w-full">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart
                                            data={dataAreas}
                                            margin={{ top: 10, right: 10, left: -10, bottom: 5 }}
                                        >
                                            <CartesianGrid strokeDasharray="3 3" stroke={gridColor} vertical={false} />
                                            <XAxis dataKey="name" stroke={axisColor} fontSize={9} tickLine={false} />
                                            <YAxis stroke={axisColor} fontSize={10} tickLine={false} />
                                            <Tooltip content={<CustomTooltip />} cursor={{ fill: isDark ? '#1e293b' : '#f8fafc', opacity: 0.4 }} />
                                            <Bar dataKey="total" radius={[6, 6, 0, 0]} barSize={24}>
                                                {dataAreas.map((entry, index) => (
                                                    <Cell 
                                                        key={`cell-${index}`} 
                                                        fill={CHART_COLORS[(index + 2) % CHART_COLORS.length]} 
                                                    />
                                                ))}
                                            </Bar>
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* E. Dificultad del banco de reactivos (PieChart) */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <TrendingUp className="h-4.5 w-4.5 text-indigo-600 dark:text-indigo-400" />
                                Dificultad del banco de reactivos
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500 dark:text-slate-400">
                                Equilibrio de complejidad en las preguntas registradas.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-2">
                            {dataDificultad.length === 0 ? (
                                <EmptyState message="No hay preguntas para categorizar su dificultad." />
                            ) : (
                                <div className="flex flex-col sm:flex-row items-center justify-around gap-6">
                                    {/* Recharts Donut Visual */}
                                    <div className="relative flex items-center justify-center h-72 w-full max-w-[280px] shrink-0">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={dataDificultad}
                                                    cx="50%"
                                                    cy="50%"
                                                    innerRadius={65}
                                                    outerRadius={95}
                                                    paddingAngle={3}
                                                    dataKey="value"
                                                >
                                                    {dataDificultad.map((entry, index) => (
                                                        <Cell key={`cell-${index}`} fill={entry.color} />
                                                    ))}
                                                </Pie>
                                                <Tooltip content={<CustomTooltip />} />
                                            </PieChart>
                                        </ResponsiveContainer>
                                        <div className="absolute text-center pointer-events-none">
                                            <span className="block text-2xl font-black text-slate-900 dark:text-slate-100">{metricas?.totalPreguntas ?? 0}</span>
                                            <span className="block text-[8px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Reactivos</span>
                                        </div>
                                    </div>

                                    {/* Leyenda */}
                                    <div className="flex-1 space-y-3 w-full">
                                        {dataDificultad.map((dif) => {
                                            const percentVal = Math.round(dif.percent * 100);
                                            return (
                                                <div key={dif.name} className="flex flex-col border border-slate-100 dark:border-slate-800 rounded-2xl p-3 bg-slate-50/50 dark:bg-slate-800/40">
                                                    <div className="flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase">
                                                        <span className="h-2.5 w-2.5 rounded-full shrink-0" style={{ backgroundColor: dif.color }} />
                                                        <span>{dif.name}</span>
                                                    </div>
                                                    <div className="flex justify-between items-baseline mt-1.5">
                                                        <span className="text-base font-black text-slate-900 dark:text-slate-100">{dif.value}</span>
                                                        <span className="text-[10px] text-indigo-650 dark:text-indigo-400 font-extrabold">{percentVal}% del banco</span>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* F. Nivel de exigencia matemática (BarChart) */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <Award className="h-4.5 w-4.5 text-indigo-600 dark:text-indigo-400" />
                                Distribución por nivel de exigencia matemática
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500 dark:text-slate-400">
                                Clasificación de postulantes según la exigencia lógica de su carrera objetivo.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-2">
                            {dataExigencia.every(d => d.total === 0) ? (
                                <EmptyState message="No hay datos de exigencia matemática registrados." />
                            ) : (
                                <div className="h-72 w-full">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart
                                            data={dataExigencia}
                                            margin={{ top: 10, right: 10, left: -10, bottom: 5 }}
                                        >
                                            <CartesianGrid strokeDasharray="3 3" stroke={gridColor} vertical={false} />
                                            <XAxis dataKey="name" stroke={axisColor} fontSize={11} tickLine={false} />
                                            <YAxis stroke={axisColor} fontSize={11} tickLine={false} />
                                            <Tooltip content={<CustomTooltip />} cursor={{ fill: isDark ? '#1e293b' : '#f8fafc', opacity: 0.4 }} />
                                            <Bar dataKey="total" radius={[6, 6, 0, 0]} barSize={35}>
                                                {dataExigencia.map((entry, index) => (
                                                    <Cell 
                                                        key={`cell-${index}`} 
                                                        fill={entry.color} 
                                                    />
                                                ))}
                                            </Bar>
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* G. Síntesis Conceptual del Volumen Académico */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 md:col-span-2 dark:bg-slate-900 dark:ring-slate-800">
                        <CardHeader>
                            <CardTitle className="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                                <Sparkles className="h-4.5 w-4.5 text-indigo-600 dark:text-indigo-400" />
                                Síntesis del volumen académico consolidado
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500 dark:text-slate-400">
                                Muestra una lectura institucional simplificada del crecimiento y escala de los componentes cargados en el sistema.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="pt-2">
                            <div className="h-64 w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart
                                        data={dataSintesis}
                                        margin={{ top: 10, right: 20, left: -10, bottom: 5 }}
                                    >
                                        <defs>
                                            <linearGradient id="colorConsolidado" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#6366f1" stopOpacity={0.3}/>
                                                <stop offset="95%" stopColor="#6366f1" stopOpacity={0.0}/>
                                            </linearGradient>
                                        </defs>
                                        <CartesianGrid strokeDasharray="3 3" stroke={gridColor} vertical={false} />
                                        <XAxis dataKey="name" stroke={axisColor} fontSize={11} tickLine={false} />
                                        <YAxis stroke={axisColor} fontSize={11} tickLine={false} />
                                        <Tooltip content={<CustomTooltip />} />
                                        <Area type="monotone" dataKey="cantidad" stroke="#6366f1" strokeWidth={2.5} fillOpacity={1} fill="url(#colorConsolidado)" />
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                </section>


                {/* 5. Sección Plantillas Académicas */}
                <section className="space-y-4">
                    <div className="border-b border-slate-200 dark:border-slate-800 pb-2">
                        <h2 className="text-lg font-black text-slate-900 dark:text-slate-100">Plantillas académicas estructuradas</h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Instrumentos curriculares y de nivelación configurados en la plataforma.</p>
                    </div>

                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {plantillasDetalle.map((plantilla) => (
                            <Card key={plantilla.id_plan} className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 hover:ring-indigo-300 transition duration-300 flex flex-col justify-between
                                dark:bg-slate-900 dark:ring-slate-800 dark:hover:ring-indigo-700">
                                <CardHeader className="pb-3 border-b border-slate-50 dark:border-slate-800">
                                    <div className="flex items-start justify-between gap-3">
                                        <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug">{plantilla.nombre_plan}</h3>
                                        <span className="capitalize text-[9px] font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full shrink-0
                                            dark:bg-slate-800 dark:text-slate-300">
                                            {plantilla.dificultad_plan}
                                        </span>
                                    </div>
                                </CardHeader>
                                <CardContent className="pt-4 space-y-4">
                                    <div className="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                                        <span>Cantidad de reactivos:</span>
                                        <span className="font-bold text-slate-800 dark:text-slate-200">{plantilla.cantidad_preguntas}</span>
                                    </div>
                                    
                                    <div className="space-y-1.5">
                                        <div className="flex justify-between text-xs font-semibold text-indigo-700 dark:text-indigo-400">
                                            <span>Puntaje de examen:</span>
                                            <span>{plantilla.puntaje_total} / 100 Pts</span>
                                        </div>
                                        <div className="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-100 dark:border-slate-700">
                                            <div 
                                                className="h-full bg-indigo-600 rounded-full transition-all"
                                                style={{ width: `${plantilla.puntaje_total}%` }}
                                            />
                                        </div>
                                    </div>
                                </CardContent>
                                <div className="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between rounded-b-3xl dark:bg-slate-800/50 dark:border-slate-700">
                                    <span className="text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">Todas sobre 100 Pts</span>
                                    <StatusBadge status={plantilla.estado_plan === 'activo' ? 'Activa' : plantilla.estado_plan} />
                                </div>
                            </Card>
                        ))}
                    </div>
                </section>

                {/* 6. Nueva sección Reporte por Postulante */}
                <section className="space-y-6">
                    <div className="border-b border-slate-200 dark:border-slate-800 pb-2">
                        <h2 className="text-lg font-black text-slate-900 dark:text-slate-100">Reporte por postulante</h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Vista individual para identificar perfil académico, universidad objetivo y nivel de exigencia matemática.</p>
                    </div>

                    {/* Barra de Búsqueda y Filtros */}
                    <div className="grid gap-4 md:grid-cols-5 bg-white p-5 rounded-3xl ring-1 ring-slate-200/80 shadow-sm dark:bg-slate-900 dark:ring-slate-800">
                        {/* Buscador */}
                        <div className="relative md:col-span-2">
                            <span className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search className="h-4.5 w-4.5 text-slate-400" />
                            </span>
                            <input
                                type="text"
                                placeholder="Buscar postulante, colegio, universidad o carrera..."
                                value={searchQuery}
                                onChange={e => { setSearchQuery(e.target.value); setCurrentPage(1); setExpandedPostulanteId(null); }}
                                className="pl-10 h-10 w-full rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500
                                    dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder-slate-500"
                            />
                        </div>

                        {/* Filtro Universidad */}
                        <select
                            value={filterUni}
                            onChange={handleFilterChange(setFilterUni)}
                            className="h-10 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">Universidad (Todas)</option>
                            {universidadesUnicas.map(u => (
                                <option key={u} value={u}>{u}</option>
                            ))}
                        </select>

                        {/* Filtro Carrera */}
                        <select
                            value={filterCarrera}
                            onChange={handleFilterChange(setFilterCarrera)}
                            className="h-10 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">Carrera (Todas)</option>
                            {carrerasUnicas.map(c => (
                                <option key={c} value={c}>{c}</option>
                            ))}
                        </select>

                        {/* Filtro Exigencia */}
                        <select
                            value={filterExigencia}
                            onChange={handleFilterChange(setFilterExigencia)}
                            className="h-10 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="">Exigencia (Todas)</option>
                            {exigenciasUnicas.map(ex => {
                                const label = ex === 'alta' ? 'Alta' : ex === 'media-alta' ? 'Media-Alta' : ex === 'media' ? 'Media' : 'Baja-Media';
                                return <option key={ex} value={ex}>{label}</option>;
                            })}
                        </select>
                    </div>

                    {/* Tabla de Postulantes */}
                    <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden dark:bg-slate-900 dark:ring-slate-800">
                        {/* Barra de estado de filtros */}
                        <div className="flex items-center justify-between px-5 py-3 border-b border-slate-100 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-800/40">
                            <p className="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                {isFiltered
                                    ? <span className="text-indigo-700 dark:text-indigo-400">Mostrando resultados filtrados — {filteredPostulantes.length} de {postulantesList.length} postulantes</span>
                                    : <span>Total: <strong>{postulantesList.length}</strong> postulantes registrados</span>
                                }
                            </p>
                            <p className="text-[11px] font-semibold text-slate-400 dark:text-slate-500">
                                Página {currentPage} de {totalPages}
                            </p>
                        </div>
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
                                        <TableRow className="bg-slate-50/80 dark:bg-slate-800/60">
                                            <TableHead className="pl-5 dark:text-slate-400">Postulante</TableHead>
                                            <TableHead className="dark:text-slate-400">Colegio</TableHead>
                                            <TableHead className="dark:text-slate-400">Universidad / Carrera</TableHead>
                                            <TableHead className="dark:text-slate-400">Exigencia</TableHead>
                                            <TableHead className="dark:text-slate-400">Perfil Académico Preliminar</TableHead>
                                            <TableHead className="text-center">Lectura</TableHead>
                                            <TableHead className="pr-5 text-right">Estado</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {pagedPostulantes.map((post) => {
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
                                                    <TableRow className="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                                        <TableCell className="pl-5 font-bold text-slate-900 dark:text-slate-100">
                                                            <div>
                                                                <p className="text-sm">{post.nombre_completo}</p>
                                                                <p className="text-[10px] text-slate-400 dark:text-slate-500 font-semibold">{post.edad} años</p>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell className="text-slate-600 dark:text-slate-400 text-xs font-semibold max-w-[150px] truncate">
                                                            {post.colegio}
                                                        </TableCell>
                                                        <TableCell className="text-xs">
                                                            <div className="font-semibold text-slate-800 dark:text-slate-200">{post.carrera}</div>
                                                            <div className="text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase">{post.universidad}</div>
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
                                                                className="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-xl transition
                                                                    dark:text-indigo-400 dark:bg-indigo-950 dark:hover:bg-indigo-900"
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
                                                        <TableRow className="bg-slate-50/55 hover:bg-slate-50/55 dark:bg-slate-800/30 dark:hover:bg-slate-800/30">
                                                            <TableCell colSpan={7} className="px-6 py-4">
                                                                <div className="rounded-2xl border border-indigo-100/50 bg-white p-4 space-y-3 dark:border-slate-700 dark:bg-slate-800">
                                                                    <div className="flex items-center gap-2 text-xs font-bold text-indigo-700 dark:text-indigo-300">
                                                                        <Lightbulb className="h-4 w-4 text-amber-500 shrink-0" />
                                                                        <span>Lectura Institucional y Recomendaciones</span>
                                                                    </div>
                                                                    <div className="grid gap-4 sm:grid-cols-3 text-xs leading-relaxed">
                                                                        <div>
                                                                            <span className="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Seguimiento sugerido</span>
                                                                            <p className="mt-1 font-semibold text-slate-800 dark:text-slate-200">{post.perfil}</p>
                                                                        </div>
                                                                        <div className="sm:col-span-2">
                                                                            <span className="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Recomendación de nivelación</span>
                                                                            <p className="mt-1 text-slate-600 dark:text-slate-400">
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
                        {/* Controles de paginación */}
                        {totalPages > 1 && (
                            <div className="flex items-center justify-center gap-2 px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                                <button
                                    onClick={() => { setCurrentPage(p => Math.max(1, p - 1)); setExpandedPostulanteId(null); }}
                                    disabled={currentPage === 1}
                                    className="px-4 py-1.5 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition
                                        dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                >
                                    ← Anterior
                                </button>
                                <span className="text-xs font-bold text-slate-500 dark:text-slate-400 px-2">
                                    {currentPage} / {totalPages}
                                </span>
                                <button
                                    onClick={() => { setCurrentPage(p => Math.min(totalPages, p + 1)); setExpandedPostulanteId(null); }}
                                    disabled={currentPage === totalPages}
                                    className="px-4 py-1.5 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition
                                        dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                >
                                    Siguiente →
                                </button>
                            </div>
                        )}
                    </Card>
                </section>

                {/* 7. Sección Lectura Académica Preliminar */}
                <section className="space-y-4">
                    <div className="border-b border-slate-200 dark:border-slate-800 pb-2">
                        <h2 className="text-lg font-black text-slate-900 dark:text-slate-100">Lectura académica preliminar</h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Conclusiones analíticas y pautas derivadas de la masa crítica del instituto.</p>
                    </div>

                    <div className="grid gap-4 lg:grid-cols-4">
                        {/* Conclusión 1 */}
                        <div className="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div className="space-y-2">
                                <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400">
                                    <Building2 className="h-4 w-4" />
                                </span>
                                <h3 className="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Concentración por universidad</h3>
                                <p className="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    La mayor concentración de postulantes se encuentra enfocada en la {uniLider}.
                                </p>
                            </div>
                            <p className="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-indigo-700 dark:text-indigo-400 leading-normal">
                                Recomendación: Priorizar la calendarización de plantillas evaluativas asociadas a la {uniLider} para validar el nivel inicial.
                            </p>
                        </div>

                        {/* Conclusión 2 */}
                        <div className="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div className="space-y-2">
                                <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400">
                                    <SlidersHorizontal className="h-4 w-4" />
                                </span>
                                <h3 className="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Carrera con mayor demanda</h3>
                                <p className="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    La carrera de {carreraLider} presenta el mayor volumen de postulantes inscritos.
                                </p>
                            </div>
                            <p className="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-indigo-700 dark:text-indigo-400 leading-normal">
                                Recomendación: Reforzar los módulos de álgebra y razonamiento lógico-matemático en los talleres de nivelación de esta rama.
                            </p>
                        </div>

                        {/* Conclusión 3 */}
                        <div className="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div className="space-y-2">
                                <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400">
                                    <Layers className="h-4 w-4" />
                                </span>
                                <h3 className="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Cobertura del banco</h3>
                                <p className="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    El área de {areaLider} contiene la mayor cantidad de reactivos cargados.
                                </p>
                            </div>
                            <p className="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-indigo-700 dark:text-indigo-400 leading-normal">
                                Recomendación: Ampliar la base de reactivos complejos en las áreas complementarias para equilibrar la cobertura de plantillas.
                            </p>
                        </div>

                        {/* Conclusión 4 */}
                        <div className="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div className="space-y-2">
                                <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400">
                                    <TrendingUp className="h-4 w-4" />
                                </span>
                                <h3 className="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Dificultad predominante</h3>
                                <p className="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    El banco de preguntas mantiene una concentración mayoritaria de complejidad {difPredominante}.
                                </p>
                            </div>
                            <p className="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-indigo-700 dark:text-indigo-400 leading-normal">
                                Recomendación: Incorporar de manera progresiva reactivos de nivel avanzado para simular la exigencia real de ingenierías.
                            </p>
                        </div>
                    </div>
                </section>

                {/* 8. Sección Base para Learning Analytics */}
                <section>
                    <div className="rounded-3xl border border-indigo-100 bg-indigo-50/60 p-6 sm:p-8 space-y-6 shadow-sm dark:border-indigo-900/50 dark:bg-indigo-950/40">
                        <div className="flex gap-4 items-start">
                            <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-400">
                                <Sparkles className="h-6 w-6" />
                            </span>
                            <div className="space-y-1">
                                <h3 className="text-base font-bold text-indigo-950 dark:text-indigo-200">Preparación para Learning Analytics</h3>
                                <p className="text-sm text-indigo-700 dark:text-indigo-400 leading-relaxed max-w-4xl">
                                    Los indicadores consolidados permiten construir una base académica para futuras capas de Learning Analytics, orientadas a riesgo académico, recomendaciones de refuerzo y predicción de desempeño.
                                </p>
                            </div>
                        </div>

                        {/* Línea de proceso visual */}
                        <div className="border-t border-indigo-100 dark:border-indigo-900 pt-6">
                            <div className="grid grid-cols-2 gap-4 md:grid-cols-5 text-center text-xs font-bold text-indigo-900 dark:text-indigo-200">
                                <div className="p-3 bg-white rounded-xl border border-indigo-100 flex flex-col justify-center items-center shadow-sm dark:bg-slate-900 dark:border-indigo-900">
                                    <span className="text-[10px] text-indigo-400 dark:text-indigo-500 font-semibold uppercase">Paso 1</span>
                                    <span className="mt-1">Datos</span>
                                </div>
                                <div className="hidden md:flex items-center justify-center text-indigo-400 dark:text-indigo-600">→</div>
                                <div className="p-3 bg-white rounded-xl border border-indigo-100 flex flex-col justify-center items-center shadow-sm dark:bg-slate-900 dark:border-indigo-900">
                                    <span className="text-[10px] text-indigo-400 dark:text-indigo-500 font-semibold uppercase">Paso 2</span>
                                    <span className="mt-1">Indicadores</span>
                                </div>
                                <div className="hidden md:flex items-center justify-center text-indigo-400 dark:text-indigo-600">→</div>
                                <div className="p-3 bg-white rounded-xl border border-indigo-100 flex flex-col justify-center items-center shadow-sm md:col-span-1 col-span-2 dark:bg-slate-900 dark:border-indigo-900">
                                    <span className="text-[10px] text-indigo-400 dark:text-indigo-500 font-semibold uppercase">Paso 3</span>
                                    <span className="mt-1">Lectura Académica</span>
                                </div>
                                <div className="hidden md:flex items-center justify-center text-indigo-400 dark:text-indigo-600">→</div>
                                <div className="p-3 bg-white rounded-xl border border-indigo-100 flex flex-col justify-center items-center shadow-sm dark:bg-slate-900 dark:border-indigo-900">
                                    <span className="text-[10px] text-indigo-400 dark:text-indigo-500 font-semibold uppercase">Paso 4</span>
                                    <span className="mt-1">Recomendaciones</span>
                                </div>
                                <div className="hidden md:flex items-center justify-center text-indigo-400 dark:text-indigo-600">→</div>
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
