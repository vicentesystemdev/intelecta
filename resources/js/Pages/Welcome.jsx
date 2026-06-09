import { Head, Link } from '@inertiajs/react';
import React, { useState, useEffect } from 'react';
import {
    GraduationCap,
    ArrowRight,
    BookOpen,
    Award,
    TrendingUp,
    MapPin,
    LayoutDashboard,
    BookCopy,
    FileQuestion,
    FileText,
    Activity,
    CheckCircle2,
    Sparkles,
    Calculator,
    Percent,
    Binary,
    Compass,
    ChevronRight
} from 'lucide-react';

// Componente ImageFrame reutilizable local
function ImageFrame({ src, alt, title, description, icon: Icon, children }) {
    const [hasError, setHasError] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!src) {
            setHasError(true);
            setLoading(false);
        }
    }, [src]);

    return (
        <div className="relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-900 shadow-2xl transition-all duration-300 hover:shadow-indigo-500/5 min-h-[350px] flex flex-col justify-between">
            {!hasError && src && (
                <img
                    src={src}
                    alt={alt}
                    onError={() => setHasError(true)}
                    onLoad={() => setLoading(false)}
                    className={`absolute inset-0 h-full w-full object-cover transition-opacity duration-500 ${
                        loading ? 'opacity-0 pointer-events-none' : 'opacity-100'
                    }`}
                />
            )}

            {/* Fallback container - mostrado en error o mientras carga */}
            {(hasError || loading) && (
                <div className="flex h-full min-h-[350px] w-full flex-col justify-between bg-gradient-to-tr from-indigo-950 via-indigo-900 to-cyan-900 p-6 sm:p-8 text-white relative">
                    {/* Patrón de fondo abstracto */}
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:20px_20px] pointer-events-none" />
                    
                    {/* Brillo de acento */}
                    <div className="absolute -right-20 -top-20 h-60 w-60 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none" />
                    <div className="absolute -left-20 -bottom-20 h-60 w-60 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none" />

                    {/* Cabecera del Fallback */}
                    <div className="relative z-10 flex items-center justify-between border-b border-white/10 pb-4">
                        <div className="flex items-center gap-3">
                            {Icon && <Icon className="h-5 w-5 text-cyan-400" />}
                            <span className="text-xs font-bold tracking-[0.15em] uppercase text-cyan-300">
                                {title}
                            </span>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                            <span className="text-[10px] font-medium text-emerald-400">Consolidado</span>
                        </div>
                    </div>

                    {/* Contenido Central */}
                    <div className="relative z-10 my-4 flex-1 flex flex-col justify-center">
                        {children}
                    </div>

                    {/* Pie del Fallback */}
                    <div className="relative z-10 border-t border-white/10 pt-4">
                        <p className="text-[11px] font-medium leading-relaxed text-indigo-200">
                            {description}
                        </p>
                    </div>
                </div>
            )}
        </div>
    );
}

// Fallbacks de diseño específicos para cada imagen esperada
const HeroDashboardFallback = () => (
    <div className="grid grid-cols-2 gap-3 sm:gap-4 py-2">
        <div className="col-span-2 rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur-md">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-[10px] uppercase tracking-wider text-indigo-300 font-semibold">Postulantes registrados</p>
                    <p className="mt-1 text-2xl font-bold tracking-tight text-white">1,248</p>
                </div>
                <span className="rounded-full bg-emerald-500/20 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">
                    +12% gestión
                </span>
            </div>
        </div>
        
        <div className="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4 backdrop-blur-md">
            <p className="text-[10px] uppercase tracking-wider text-indigo-300 font-semibold">Plantillas</p>
            <p className="mt-1 text-base font-bold text-white">18 activas</p>
            <p className="text-[9px] text-indigo-200 mt-1 leading-none">Estructuradas por carrera</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4 backdrop-blur-md">
            <p className="text-[10px] uppercase tracking-wider text-indigo-300 font-semibold">Banco de preguntas</p>
            <p className="mt-1 text-base font-bold text-white">4,850 reactivos</p>
            <p className="text-[9px] text-indigo-200 mt-1 leading-none">Preguntas categorizadas</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4 backdrop-blur-md">
            <p className="text-[10px] uppercase tracking-wider text-indigo-300 font-semibold">Riesgo académico</p>
            <p className="mt-1 text-base font-bold text-white">6.8% crítico</p>
            <p className="text-[9px] text-indigo-200 mt-1 leading-none">Alerta de nivelación</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4 backdrop-blur-md">
            <p className="text-[10px] uppercase tracking-wider text-indigo-300 font-semibold">Nivel de exigencia</p>
            <p className="mt-1 text-base font-bold text-cyan-300">Avanzado</p>
            <p className="text-[9px] text-indigo-200 mt-1 leading-none">Lógico-Matemático</p>
        </div>
    </div>
);

const EvaluationsFallback = () => (
    <div className="space-y-4 py-2">
        <div className="rounded-xl border border-white/10 bg-white/5 p-3">
            <div className="flex justify-between items-center text-xs">
                <span className="font-semibold text-white">Estructura: Simulacro PSA</span>
                <span className="text-cyan-300 font-bold">100 Pts</span>
            </div>
            <div className="mt-2 h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                <div className="h-full w-3/4 bg-gradient-to-r from-cyan-400 to-indigo-500 rounded-full" />
            </div>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/10 p-4 relative">
            <div className="flex justify-between items-center text-[10px] text-indigo-300 mb-2">
                <span>PREGUNTA 14 (Álgebra)</span>
                <span className="bg-cyan-500/20 text-cyan-300 px-1.5 py-0.5 rounded">Exigencia Alta</span>
            </div>
            <p className="text-xs text-white leading-relaxed mb-3">
                Si la ecuación cuadrática x² - 5x + 6 = 0 tiene por raíces a α y β, calcule el valor de α² + β².
            </p>
            
            <div className="grid grid-cols-2 gap-2 text-[10px]">
                <div className="flex items-center gap-2 rounded bg-white/5 p-1.5 border border-white/5">
                    <span className="w-4 h-4 rounded-full bg-white/10 flex items-center justify-center text-[9px] text-white">A</span>
                    <span>12</span>
                </div>
                <div className="flex items-center gap-2 rounded bg-white/5 p-1.5 border border-white/5">
                    <span className="w-4 h-4 rounded-full bg-white/10 flex items-center justify-center text-[9px] text-white">B</span>
                    <span>13</span>
                </div>
                <div className="flex items-center gap-2 rounded bg-cyan-500/20 p-1.5 border border-cyan-500/30">
                    <span className="w-4 h-4 rounded-full bg-cyan-400 flex items-center justify-center text-[9px] text-indigo-950 font-bold">C</span>
                    <span className="text-cyan-200">13 (Correcta)</span>
                </div>
                <div className="flex items-center gap-2 rounded bg-white/5 p-1.5 border border-white/5">
                    <span className="w-4 h-4 rounded-full bg-white/10 flex items-center justify-center text-[9px] text-white">D</span>
                    <span>25</span>
                </div>
            </div>
        </div>
    </div>
);

const ContextLaPazFallback = () => (
    <div className="grid grid-cols-2 gap-3 py-1">
        <div className="rounded-xl border border-white/10 bg-white/5 p-3 hover:bg-white/10 transition duration-300">
            <p className="text-[10px] font-bold text-cyan-300 uppercase">UMSA</p>
            <p className="text-[11px] font-semibold text-white mt-0.5">Facultad de Ingeniería</p>
            <p className="text-[9px] text-indigo-200 mt-1 leading-normal">Alta exigencia en cálculo, física y álgebra superior.</p>
        </div>
        
        <div className="rounded-xl border border-white/10 bg-white/5 p-3 hover:bg-white/10 transition duration-300">
            <p className="text-[10px] font-bold text-cyan-300 uppercase">UCB</p>
            <p className="text-[11px] font-semibold text-white mt-0.5">Aptitud Académica</p>
            <p className="text-[9px] text-indigo-200 mt-1 leading-normal">Evaluación de razonamiento lógico e inferencia matemática.</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 hover:bg-white/10 transition duration-300">
            <p className="text-[10px] font-bold text-cyan-300 uppercase">ESFM / Normal</p>
            <p className="text-[11px] font-semibold text-white mt-0.5">Prueba Psicotécnica</p>
            <p className="text-[9px] text-indigo-200 mt-1 leading-normal">Enfoque en lógica abstracta, espacial y comprensión analítica.</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 hover:bg-white/10 transition duration-300">
            <p className="text-[10px] font-bold text-cyan-300 uppercase">Economía / Financieras</p>
            <p className="text-[11px] font-semibold text-white mt-0.5">Ciencias Económicas</p>
            <p className="text-[9px] text-indigo-200 mt-1 leading-normal">Problemas aplicados de porcentajes, proporciones y estadística.</p>
        </div>
    </div>
);

const LearningAnalyticsFallback = () => (
    <div className="space-y-4 py-2">
        <div className="grid grid-cols-3 gap-3">
            <div className="col-span-2 rounded-xl border border-white/10 bg-white/5 p-3">
                <p className="text-[9px] uppercase tracking-wider text-indigo-300 mb-2 font-semibold">Desempeño por Área</p>
                <div className="flex items-end justify-between h-20 pt-2 px-1">
                    <div className="flex flex-col items-center gap-1 w-1/4">
                        <div className="w-full bg-gradient-to-t from-indigo-500 to-cyan-400 rounded-t h-12" />
                        <span className="text-[8px] text-indigo-200">Arit</span>
                    </div>
                    <div className="flex flex-col items-center gap-1 w-1/4">
                        <div className="w-full bg-gradient-to-t from-indigo-500 to-cyan-400 rounded-t h-16" />
                        <span className="text-[8px] text-indigo-200">Álg</span>
                    </div>
                    <div className="flex flex-col items-center gap-1 w-1/4">
                        <div className="w-full bg-gradient-to-t from-indigo-500 to-cyan-400 rounded-t h-8" />
                        <span className="text-[8px] text-indigo-200">Geom</span>
                    </div>
                    <div className="flex flex-col items-center gap-1 w-1/4">
                        <div className="w-full bg-gradient-to-t from-indigo-500 to-cyan-400 rounded-t h-20" />
                        <span className="text-[8px] text-indigo-200">Lóg</span>
                    </div>
                </div>
            </div>
            
            <div className="rounded-xl border border-white/10 bg-white/5 p-3 flex flex-col justify-between">
                <div>
                    <p className="text-[9px] uppercase tracking-wider text-indigo-300 font-semibold">Riesgo</p>
                    <p className="text-[8px] text-indigo-200">Prevención</p>
                </div>
                <div className="my-1.5 flex justify-center">
                    <div className="relative flex items-center justify-center">
                        <svg className="w-12 h-12 transform -rotate-90">
                            <circle cx="24" cy="24" r="20" stroke="currentColor" className="text-white/10" strokeWidth="2.5" fill="transparent" />
                            <circle cx="24" cy="24" r="20" stroke="currentColor" className="text-cyan-400" strokeWidth="2.5" strokeDasharray="125" strokeDashoffset="45" fill="transparent" />
                        </svg>
                        <span className="absolute text-[9px] font-bold text-white">68%</span>
                    </div>
                </div>
                <p className="text-[8px] text-center text-cyan-300 font-medium">Optimizando</p>
            </div>
        </div>

        <div className="rounded-xl border border-white/10 bg-indigo-500/15 p-2 text-center">
            <span className="text-[9px] font-semibold text-cyan-300">Modelo Predictivo Activo</span>
            <span className="mx-2 text-white/30">|</span>
            <span className="text-[9px] text-white">Evolución de Refuerzo del Postulante</span>
        </div>
    </div>
);

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="INTELECTA - Plataforma Académica Preuniversitaria" />

            <div className="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-indigo-500 selection:text-white">
                {/* 1. Header superior */}
                <header className="sticky top-0 z-50 w-full border-b border-slate-200/80 bg-white/95 backdrop-blur-xl transition-all">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex h-20 items-center justify-between">
                            {/* Logo */}
                            <div className="flex items-center gap-3">
                                <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-lg shadow-indigo-600/20">
                                    <GraduationCap className="h-6 w-6" />
                                </span>
                                <span className="text-2xl font-black tracking-[0.15em] bg-gradient-to-r from-slate-900 via-indigo-950 to-indigo-900 bg-clip-text text-transparent">
                                    INTELECTA
                                </span>
                            </div>

                            {/* Navegación */}
                            <nav className="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-600">
                                <a href="#enfoque" className="transition hover:text-indigo-600">Enfoque académico</a>
                                <a href="#modulos" className="transition hover:text-indigo-600">Módulos</a>
                                <a href="#evaluaciones" className="transition hover:text-indigo-600">Plantillas</a>
                                <a href="#analytics" className="transition hover:text-indigo-600">Learning Analytics</a>
                                <a href="#contexto" className="transition hover:text-indigo-600">Contexto preuniversitario</a>
                            </nav>

                            {/* Botón de Acción */}
                            <div>
                                {auth.user ? (
                                    <Link
                                        href={route('dashboard')}
                                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-indigo-600/30 hover:-translate-y-0.5 active:translate-y-0"
                                    >
                                        Ir al panel
                                        <ArrowRight className="h-4 w-4" />
                                    </Link>
                                ) : (
                                    <Link
                                        href={route('login')}
                                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-950/20 transition-all hover:bg-slate-900 hover:shadow-slate-950/30 hover:-translate-y-0.5 active:translate-y-0"
                                    >
                                        Iniciar sesión
                                        <ArrowRight className="h-4 w-4" />
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>
                </header>

                {/* 2. Hero principal */}
                <section className="relative overflow-hidden py-16 sm:py-24 lg:py-28 bg-gradient-to-b from-white via-slate-50 to-slate-100">
                    {/* Elementos abstractos de fondo */}
                    <div className="absolute top-1/4 left-10 w-72 h-72 rounded-full bg-indigo-100/50 blur-3xl pointer-events-none" />
                    <div className="absolute bottom-10 right-10 w-96 h-96 rounded-full bg-cyan-100/40 blur-3xl pointer-events-none" />

                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                            {/* Text content */}
                            <div className="lg:col-span-7 text-center lg:text-left">
                                <div className="inline-flex items-center gap-2.5 rounded-full border border-indigo-100 bg-indigo-50/50 px-4 py-1.5 text-xs font-semibold text-indigo-700 mb-6">
                                    <Sparkles className="h-3.5 w-3.5" />
                                    Evaluación Preuniversitaria de Alto Rendimiento
                                </div>
                                <h1 className="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-950 leading-[1.1]">
                                    INTELECTA
                                </h1>
                                <h2 className="mt-4 text-xl sm:text-2xl font-bold text-slate-800 leading-normal">
                                    Evaluación y análisis del desempeño lógico-matemático preuniversitario
                                </h2>
                                <p className="mt-6 text-base sm:text-lg leading-relaxed text-slate-600 max-w-2xl mx-auto lg:mx-0">
                                    Plataforma web para registrar postulantes, construir evaluaciones académicas, organizar bancos de preguntas y preparar información para futuras predicciones de desempeño.
                                </p>
                                <div className="mt-10 flex flex-wrap gap-4 justify-center lg:justify-start">
                                    {auth.user ? (
                                        <Link
                                            href={route('dashboard')}
                                            className="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-indigo-600/30 hover:-translate-y-0.5"
                                        >
                                            Ir al panel
                                            <ArrowRight className="h-5 w-5" />
                                        </Link>
                                    ) : (
                                        <Link
                                            href={route('login')}
                                            className="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-indigo-600/30 hover:-translate-y-0.5"
                                        >
                                            Iniciar sesión
                                            <ArrowRight className="h-5 w-5" />
                                        </Link>
                                    )}
                                    <a
                                        href="#enfoque"
                                        className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3.5 text-base font-semibold text-slate-800 shadow-sm transition-all hover:border-slate-350 hover:bg-slate-50 hover:-translate-y-0.5"
                                    >
                                        Ver enfoque académico
                                    </a>
                                </div>
                            </div>

                            {/* Elemento visual (ImageFrame) */}
                            <div className="lg:col-span-5 w-full">
                                <ImageFrame
                                    src="/images/landing/hero-dashboard.png"
                                    alt="Panel de control de INTELECTA"
                                    title="Panel de control"
                                    description="Consola unificada para la gestión académica y configuración del entorno de pruebas."
                                    icon={LayoutDashboard}
                                >
                                    <HeroDashboardFallback />
                                </ImageFrame>
                            </div>
                        </div>
                    </div>
                </section>

                {/* 3. Sección tipo categorías educativas */}
                <section id="enfoque" className="py-20 sm:py-28 bg-white border-t border-slate-200/50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center max-w-3xl mx-auto mb-16 sm:mb-20">
                            <h2 className="text-xs font-bold uppercase tracking-[0.25em] text-indigo-600">Áreas Metodológicas</h2>
                            <p className="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                                Preparación académica orientada al contexto preuniversitario
                            </p>
                            <div className="mt-4 h-1 w-12 bg-indigo-600 mx-auto rounded-full" />
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {/* Card Aritmética */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                    <Percent className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-indigo-950">Aritmética</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Fundamentos numéricos, proporciones, porcentajes y operaciones básicas aplicadas.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Inicial-Intermedio
                                </span>
                            </div>

                            {/* Card Álgebra */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                    <Calculator className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-indigo-950">Álgebra</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Ecuaciones, sistemas lineales, polinomios y funciones esenciales para el cálculo.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Intermedio
                                </span>
                            </div>

                            {/* Card Geometría y Trigonometría */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                    <Compass className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-indigo-950">Geometría y Trigonometría</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Relaciones espaciales, teoremas fundamentales e identidades trigonométricas.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Intermedio-Avanzado
                                </span>
                            </div>

                            {/* Card Razonamiento Lógico-Matemático */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                    <Binary className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-indigo-950">Razonamiento Lógico-Matemático</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Patrones, inducción, deducción y lógica formal para aptitud académica.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Avanzado
                                </span>
                            </div>

                            {/* Card Estadística y Probabilidades */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                    <TrendingUp className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-indigo-950">Estadística y Probabilidades</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Análisis de datos, medidas de tendencia central y cálculo de probabilidades.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Intermedio
                                </span>
                            </div>

                            {/* Card Resolución de Problemas */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                    <BookOpen className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-indigo-950">Resolución de Problemas</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Metodologías de modelamiento y heurísticas para plantear situaciones complejas.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Enfoque Práctico
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                {/* 4. Sección visual de evaluaciones */}
                <section id="evaluaciones" className="py-20 sm:py-28 bg-slate-100 border-t border-slate-200/50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center max-w-3xl mx-auto mb-16">
                            <h2 className="text-xs font-bold uppercase tracking-[0.25em] text-indigo-600">Instrumentos Académicos</h2>
                            <p className="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                                Plantillas académicas listas para evaluación
                            </p>
                            <p className="mt-4 text-base text-slate-600">
                                Composición estructurada de evaluaciones alineadas a los objetivos preuniversitarios de la región.
                            </p>
                            <div className="mt-4 h-1 w-12 bg-indigo-600 mx-auto rounded-full" />
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                            {/* Cards list */}
                            <div className="lg:col-span-7 space-y-4">
                                {/* Simulacro UMSA */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                                    <div className="space-y-1">
                                        <span className="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                            UMSA - Ingeniería
                                        </span>
                                        <h3 className="text-base font-bold text-slate-900">Simulacro PSA - Facultad de Ingeniería UMSA</h3>
                                        <p className="text-xs text-slate-500">Álgebra, Cálculo y Física</p>
                                    </div>
                                    <div className="flex items-center gap-4 shrink-0">
                                        <div className="text-right">
                                            <p className="text-xs text-slate-500">Reactivos: <span className="font-bold text-slate-800">40</span></p>
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-indigo-600">Alta</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>

                                {/* UCB */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                                    <div className="space-y-1">
                                        <span className="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                            UCB - Aptitud
                                        </span>
                                        <h3 className="text-base font-bold text-slate-900">Prueba de Aptitud Académica - Razonamiento Lógico UCB</h3>
                                        <p className="text-xs text-slate-500">Aptitud Matemática e Inferencia</p>
                                    </div>
                                    <div className="flex items-center gap-4 shrink-0">
                                        <div className="text-right">
                                            <p className="text-xs text-slate-500">Reactivos: <span className="font-bold text-slate-800">50</span></p>
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-indigo-600">Media-Alta</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>

                                {/* Ciencias Económicas */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                                    <div className="space-y-1">
                                        <span className="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                            Economía y Financieras
                                        </span>
                                        <h3 className="text-base font-bold text-slate-900">Diagnóstico Matemático - Ciencias Económicas y Financieras</h3>
                                        <p className="text-xs text-slate-500">Aritmética y Álgebra Financiera</p>
                                    </div>
                                    <div className="flex items-center gap-4 shrink-0">
                                        <div className="text-right">
                                            <p className="text-xs text-slate-500">Reactivos: <span className="font-bold text-slate-800">30</span></p>
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-indigo-600">Media</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>

                                {/* ESFM */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                                    <div className="space-y-1">
                                        <span className="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                            ESFM - Normal
                                        </span>
                                        <h3 className="text-base font-bold text-slate-900">Simulacro Psicotécnico - ESFM / Normal Simón Bolívar</h3>
                                        <p className="text-xs text-slate-500">Lógica, Espacial y Comprensión</p>
                                    </div>
                                    <div className="flex items-center gap-4 shrink-0">
                                        <div className="text-right">
                                            <p className="text-xs text-slate-500">Reactivos: <span className="font-bold text-slate-800">60</span></p>
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-indigo-600">Media</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>

                                {/* Mixta Álgebra */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                                    <div className="space-y-1">
                                        <span className="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                            Nivelación
                                        </span>
                                        <h3 className="text-base font-bold text-slate-900">Evaluación Mixta de Nivelación - Álgebra y Trigonometría</h3>
                                        <p className="text-xs text-slate-500">Conceptos Fundamentales e Identidades</p>
                                    </div>
                                    <div className="flex items-center gap-4 shrink-0">
                                        <div className="text-right">
                                            <p className="text-xs text-slate-500">Reactivos: <span className="font-bold text-slate-800">35</span></p>
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-indigo-600">Intermedia</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* ImageFrame */}
                            <div className="lg:col-span-5 w-full">
                                <ImageFrame
                                    src="/images/landing/evaluaciones-academicas.png"
                                    alt="Diseñador de evaluaciones académicas"
                                    title="Evaluaciones Académicas"
                                    description="Interfaz interactiva para la creación de reactivos y ordenación de secciones temáticas."
                                    icon={BookCopy}
                                >
                                    <EvaluationsFallback />
                                </ImageFrame>
                            </div>
                        </div>
                    </div>
                </section>

                {/* 5. Sección de flujo académico */}
                <section id="modulos" className="py-20 sm:py-28 bg-white border-t border-slate-200/50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center max-w-3xl mx-auto mb-20">
                            <h2 className="text-xs font-bold uppercase tracking-[0.25em] text-indigo-600">Proceso Metodológico</h2>
                            <p className="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                                Del registro del postulante al análisis académico
                            </p>
                            <div className="mt-4 h-1 w-12 bg-indigo-600 mx-auto rounded-full" />
                        </div>

                        <div className="relative">
                            {/* Line separator (desktop) */}
                            <div className="absolute top-1/2 left-4 right-4 hidden lg:block -translate-y-1/2 h-0.5 bg-slate-200 pointer-events-none" />

                            <div className="relative z-10 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
                                {/* Paso 1 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 border-2 border-indigo-600 text-lg font-bold text-indigo-600 transition duration-300 group-hover:bg-indigo-600 group-hover:text-white">
                                        1
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Registro de postulantes</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Ingreso y centralización de perfiles, colegios y datos sociodemográficos.
                                    </p>
                                </div>

                                {/* Paso 2 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 border-2 border-indigo-600 text-lg font-bold text-indigo-600 transition duration-300 group-hover:bg-indigo-600 group-hover:text-white">
                                        2
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Universidad y carrera</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Asignación del perfil de postulación y el nivel de exigencia lógica.
                                    </p>
                                </div>

                                {/* Paso 3 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 border-2 border-indigo-600 text-lg font-bold text-indigo-600 transition duration-300 group-hover:bg-indigo-600 group-hover:text-white">
                                        3
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Banco de preguntas</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Organización de reactivos y alternativas por nivel de dificultad.
                                    </p>
                                </div>

                                {/* Paso 4 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 border-2 border-indigo-600 text-lg font-bold text-indigo-600 transition duration-300 group-hover:bg-indigo-600 group-hover:text-white">
                                        4
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Plantillas de evaluación</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Generación de pruebas estructuradas por tema y ponderación.
                                    </p>
                                </div>

                                {/* Paso 5 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 border-2 border-indigo-600 text-lg font-bold text-indigo-600 transition duration-300 group-hover:bg-indigo-600 group-hover:text-white">
                                        5
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Learning Analytics</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Resultados unificados para identificar riesgo académico y proyecciones.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* 6. Sección contexto La Paz */}
                <section id="contexto" className="py-20 sm:py-28 bg-slate-100 border-t border-slate-200/50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                            {/* Text content */}
                            <div className="lg:col-span-7 space-y-6">
                                <div className="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    <MapPin className="h-3.5 w-3.5" />
                                    Contexto Regional La Paz
                                </div>
                                <h2 className="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">
                                    Diseñado para el entorno preuniversitario paceño
                                </h2>
                                <p className="text-base sm:text-lg leading-relaxed text-slate-600">
                                    INTELECTA organiza el seguimiento académico considerando colegio de procedencia, universidad postulada, carrera y nivel de exigencia matemática, permitiendo diferenciar perfiles de preparación según el objetivo académico del postulante.
                                </p>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                                    <div className="flex gap-3">
                                        <CheckCircle2 className="h-5 w-5 text-indigo-600 shrink-0 mt-0.5" />
                                        <div>
                                            <p className="text-sm font-bold text-slate-900">Perfiles dinámicos</p>
                                            <p className="text-xs text-slate-500">Segmentación precisa según la casa de estudios seleccionada.</p>
                                        </div>
                                    </div>
                                    <div className="flex gap-3">
                                        <CheckCircle2 className="h-5 w-5 text-indigo-600 shrink-0 mt-0.5" />
                                        <div>
                                            <p className="text-sm font-bold text-slate-900">Variables paceñas</p>
                                            <p className="text-xs text-slate-500">Mapeo del colegio de procedencia y metas académicas locales.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* ImageFrame */}
                            <div className="lg:col-span-5 w-full">
                                <ImageFrame
                                    src="/images/landing/preuniversitario-la-paz.jpg"
                                    alt="Variables preuniversitarias en La Paz"
                                    title="Estructura de Exigencia Regional"
                                    description="Perfiles de preparación adaptados a las principales instituciones académicas de La Paz."
                                    icon={MapPin}
                                >
                                    <ContextLaPazFallback />
                                </ImageFrame>
                            </div>
                        </div>
                    </div>
                </section>

                {/* 7. Sección Learning Analytics */}
                <section id="analytics" className="py-20 sm:py-28 bg-white border-t border-slate-200/50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                            {/* ImageFrame (Left side on desktop) */}
                            <div className="lg:col-span-5 w-full order-last lg:order-first">
                                <ImageFrame
                                    src="/images/landing/learning-analytics.png"
                                    alt="Visualización de Learning Analytics"
                                    title="Learning Analytics"
                                    description="Visualización avanzada del progreso de los postulantes y estimaciones de desempeño futuro."
                                    icon={TrendingUp}
                                >
                                    <LearningAnalyticsFallback />
                                </ImageFrame>
                            </div>

                            {/* Text content & cards */}
                            <div className="lg:col-span-7 space-y-6">
                                <div className="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    <TrendingUp className="h-3.5 w-3.5" />
                                    Modelos de Datos y Analítica
                                </div>
                                <h2 className="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">
                                    Base preparada para análisis y predicción de desempeño
                                </h2>
                                <p className="text-base text-slate-600">
                                    Acceso inmediato a los indicadores más relevantes para la toma de decisiones pedagógicas y la identificación oportuna de necesidades de nivelación.
                                </p>
                                
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8">
                                    {/* Card 1 */}
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-indigo-150">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                                <FileText className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Indicadores por postulante</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-500">
                                            Progreso de avance temático y nivel de precisión por reactivo.
                                        </p>
                                    </div>

                                    {/* Card 2 */}
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-indigo-150">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                                <Activity className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Resultados por área</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-500">
                                            Desempeño en Aritmética, Álgebra, Geometría y Razonamiento Lógico.
                                        </p>
                                    </div>

                                    {/* Card 3 */}
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-indigo-150">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                                <CheckCircle2 className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Riesgo académico</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-500">
                                            Alertas inmediatas ante puntajes inferiores al estándar institucional.
                                        </p>
                                    </div>

                                    {/* Card 4 */}
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-indigo-150">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                                <Award className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Recomendaciones de refuerzo</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-500">
                                            Detección de falencias y sugerencias automáticas de repaso específico.
                                        </p>
                                    </div>

                                    {/* Card 5 (Full width on sm) */}
                                    <div className="sm:col-span-2 rounded-2xl border border-slate-200 bg-indigo-50/20 p-5 transition hover:border-indigo-150">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                                <Sparkles className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Evolución futura hacia modelos predictivos</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-600">
                                            Arquitectura preparada para la integración de modelos de regresión y clasificación, estimando la probabilidad de ingreso en función del desempeño lógico-matemático.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* 8. Cierre fuerte */}
                <section className="py-20 sm:py-24 bg-slate-900 text-white relative overflow-hidden">
                    {/* Background glows */}
                    <div className="absolute inset-0 bg-gradient-to-tr from-indigo-950 via-slate-950 to-indigo-950 pointer-events-none" />
                    <div className="absolute -left-40 top-0 h-96 w-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none" />
                    <div className="absolute -right-40 bottom-0 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none" />

                    <div className="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 text-center space-y-8">
                        <span className="flex h-16 w-16 items-center justify-center rounded-3xl bg-indigo-600 text-white mx-auto shadow-xl shadow-indigo-600/30">
                            <GraduationCap className="h-8 w-8" />
                        </span>
                        <h2 className="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-[1.15]">
                            Una plataforma académica para transformar la evaluación preuniversitaria
                        </h2>
                        <p className="max-w-3xl mx-auto text-base sm:text-lg text-indigo-200/80 leading-relaxed">
                            INTELECTA integra postulantes, bancos de preguntas, plantillas académicas y análisis institucional en una base preparada para el seguimiento y la mejora del desempeño lógico-matemático.
                        </p>
                        <div className="pt-4">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-8 py-4 text-base font-bold text-white shadow-xl shadow-indigo-600/30 transition-all hover:bg-indigo-700 hover:shadow-indigo-600/40 hover:-translate-y-0.5"
                                >
                                    Ingresar al sistema
                                    <ArrowRight className="h-5 w-5" />
                                </Link>
                            ) : (
                                <Link
                                    href={route('login')}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-8 py-4 text-base font-bold text-white shadow-xl shadow-indigo-600/30 transition-all hover:bg-indigo-700 hover:shadow-indigo-600/40 hover:-translate-y-0.5"
                                >
                                    Ingresar al sistema
                                    <ArrowRight className="h-5 w-5" />
                                </Link>
                            )}
                        </div>
                    </div>
                </section>

                {/* Footer simple */}
                <footer className="bg-slate-950 text-slate-500 py-8 border-t border-slate-900">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-xs">
                        <p>© {new Date().getFullYear()} INTELECTA. Sistema de Evaluación Académica. Todos los derechos reservados.</p>
                    </div>
                </footer>
            </div>
        </>
    );
}
