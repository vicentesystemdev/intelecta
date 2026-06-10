import { Head, Link } from '@inertiajs/react';
import ThemeToggle from '@/Components/ThemeToggle';
import useTheme from '@/Hooks/useTheme';
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
        <div className="relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-900 shadow-2xl transition-all duration-300 hover:shadow-brand-primary/5 min-h-[350px] flex flex-col justify-between">
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
                <div className="flex h-full min-h-[350px] w-full flex-col justify-between bg-gradient-to-tr from-[#0A1628] via-[#16213E] to-[#0F2A3A] p-6 sm:p-8 text-white relative">
                    {/* Patrón de fondo abstracto */}
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:20px_20px] pointer-events-none" />
                    
                    {/* Brillo de acento */}
                    <div className="absolute -right-20 -top-20 h-60 w-60 rounded-full bg-brand-secondary/10 blur-3xl pointer-events-none" />
                    <div className="absolute -left-20 -bottom-20 h-60 w-60 rounded-full bg-brand-accent/10 blur-3xl pointer-events-none" />

                    {/* Cabecera del Fallback */}
                    <div className="relative z-10 flex items-center justify-between border-b border-white/10 pb-4">
                        <div className="flex items-center gap-3">
                            {Icon && <Icon className="h-5 w-5 text-brand-accent" />}
                            <span className="text-xs font-bold tracking-[0.15em] uppercase text-brand-accent">
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
                        <p className="text-[11px] font-medium leading-relaxed text-slate-300">
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
                    <p className="text-[10px] uppercase tracking-wider text-brand-accent font-semibold">Postulantes registrados</p>
                    <p className="mt-1 text-2xl font-bold tracking-tight text-white">1,248</p>
                </div>
                <span className="rounded-full bg-emerald-500/20 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">
                    +12% gestión
                </span>
            </div>
        </div>
        
        <div className="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4 backdrop-blur-md">
            <p className="text-[10px] uppercase tracking-wider text-brand-accent font-semibold">Plantillas</p>
            <p className="mt-1 text-base font-bold text-white">18 activas</p>
            <p className="text-[9px] text-slate-300 mt-1 leading-none">Estructuradas por carrera</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4 backdrop-blur-md">
            <p className="text-[10px] uppercase tracking-wider text-brand-accent font-semibold">Banco de preguntas</p>
            <p className="mt-1 text-base font-bold text-white">4,850 reactivos</p>
            <p className="text-[9px] text-slate-300 mt-1 leading-none">Preguntas categorizadas</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4 backdrop-blur-md">
            <p className="text-[10px] uppercase tracking-wider text-brand-accent font-semibold">Riesgo académico</p>
            <p className="mt-1 text-base font-bold text-white">6.8% crítico</p>
            <p className="text-[9px] text-slate-300 mt-1 leading-none">Alerta de nivelación</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4 backdrop-blur-md">
            <p className="text-[10px] uppercase tracking-wider text-brand-accent font-semibold">Nivel de exigencia</p>
            <p className="mt-1 text-base font-bold text-brand-secondary">Avanzado</p>
            <p className="text-[9px] text-slate-300 mt-1 leading-none">Lógico-Matemático</p>
        </div>
    </div>
);

const EvaluationsFallback = () => (
    <div className="space-y-4 py-2">
        <div className="rounded-xl border border-white/10 bg-white/5 p-3">
            <div className="flex justify-between items-center text-xs">
                <span className="font-semibold text-white">Estructura: Simulacro PSA</span>
                <span className="text-brand-secondary font-bold">100 Pts</span>
            </div>
            <div className="mt-2 h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                <div className="h-full w-3/4 bg-gradient-to-r from-brand-secondary to-brand-primary rounded-full" />
            </div>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/10 p-4 relative">
            <div className="flex justify-between items-center text-[10px] text-slate-400 mb-2">
                <span>PREGUNTA 14 (Álgebra)</span>
                <span className="bg-brand-secondary/20 text-brand-secondary px-1.5 py-0.5 rounded">Exigencia Alta</span>
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
                <div className="flex items-center gap-2 rounded bg-brand-secondary/20 p-1.5 border border-brand-secondary/30">
                    <span className="w-4 h-4 rounded-full bg-brand-secondary flex items-center justify-center text-[9px] text-white font-bold">C</span>
                    <span className="text-brand-accent">13 (Correcta)</span>
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
            <p className="text-[10px] font-bold text-brand-accent uppercase">UMSA</p>
            <p className="text-[11px] font-semibold text-white mt-0.5">Facultad de Ingeniería</p>
            <p className="text-[9px] text-slate-300 mt-1 leading-normal">Alta exigencia en cálculo, física y álgebra superior.</p>
        </div>
        
        <div className="rounded-xl border border-white/10 bg-white/5 p-3 hover:bg-white/10 transition duration-300">
            <p className="text-[10px] font-bold text-brand-accent uppercase">UCB</p>
            <p className="text-[11px] font-semibold text-white mt-0.5">Aptitud Académica</p>
            <p className="text-[9px] text-slate-300 mt-1 leading-normal">Evaluación de razonamiento lógico e inferencia matemática.</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 hover:bg-white/10 transition duration-300">
            <p className="text-[10px] font-bold text-brand-accent uppercase">ESFM / Normal</p>
            <p className="text-[11px] font-semibold text-white mt-0.5">Prueba Psicotécnica</p>
            <p className="text-[9px] text-slate-300 mt-1 leading-normal">Enfoque en lógica abstracta, espacial y comprensión analítica.</p>
        </div>

        <div className="rounded-xl border border-white/10 bg-white/5 p-3 hover:bg-white/10 transition duration-300">
            <p className="text-[10px] font-bold text-brand-accent uppercase">Economía / Financieras</p>
            <p className="text-[11px] font-semibold text-white mt-0.5">Ciencias Económicas</p>
            <p className="text-[9px] text-slate-300 mt-1 leading-normal">Problemas aplicados de porcentajes, proporciones y estadística.</p>
        </div>
    </div>
);

const LearningAnalyticsFallback = () => (
    <div className="space-y-4 py-2">
        <div className="grid grid-cols-3 gap-3">
            <div className="col-span-2 rounded-xl border border-white/10 bg-white/5 p-3">
                <p className="text-[9px] uppercase tracking-wider text-brand-accent mb-2 font-semibold">Desempeño por Área</p>
                <div className="flex items-end justify-between h-20 pt-2 px-1">
                    <div className="flex flex-col items-center gap-1 w-1/4">
                        <div className="w-full bg-gradient-to-t from-brand-primary to-brand-secondary rounded-t h-12" />
                        <span className="text-[8px] text-slate-300">Arit</span>
                    </div>
                    <div className="flex flex-col items-center gap-1 w-1/4">
                        <div className="w-full bg-gradient-to-t from-brand-primary to-brand-secondary rounded-t h-16" />
                        <span className="text-[8px] text-slate-300">Álg</span>
                    </div>
                    <div className="flex flex-col items-center gap-1 w-1/4">
                        <div className="w-full bg-gradient-to-t from-brand-primary to-brand-secondary rounded-t h-8" />
                        <span className="text-[8px] text-slate-300">Geom</span>
                    </div>
                    <div className="flex flex-col items-center gap-1 w-1/4">
                        <div className="w-full bg-gradient-to-t from-brand-primary to-brand-secondary rounded-t h-20" />
                        <span className="text-[8px] text-slate-300">Lóg</span>
                    </div>
                </div>
            </div>
            
            <div className="rounded-xl border border-white/10 bg-white/5 p-3 flex flex-col justify-between">
                <div>
                    <p className="text-[9px] uppercase tracking-wider text-brand-accent font-semibold">Riesgo</p>
                    <p className="text-[8px] text-slate-300">Prevención</p>
                </div>
                <div className="my-1.5 flex justify-center">
                    <div className="relative flex items-center justify-center">
                        <svg className="w-12 h-12 transform -rotate-90">
                            <circle cx="24" cy="24" r="20" stroke="currentColor" className="text-white/10" strokeWidth="2.5" fill="transparent" />
                            <circle cx="24" cy="24" r="20" stroke="currentColor" className="text-brand-secondary" strokeWidth="2.5" strokeDasharray="125" strokeDashoffset="45" fill="transparent" />
                        </svg>
                        <span className="absolute text-[9px] font-bold text-white">68%</span>
                    </div>
                </div>
                <p className="text-[8px] text-center text-brand-accent font-medium">Optimizando</p>
            </div>
        </div>

        <div className="rounded-xl border border-white/10 bg-brand-primary/20 p-2 text-center">
            <span className="text-[9px] font-semibold text-brand-secondary">Modelo Predictivo Activo</span>
            <span className="mx-2 text-white/30">|</span>
            <span className="text-[9px] text-white">Evolución de Refuerzo del Postulante</span>
        </div>
    </div>
);
export default function Welcome({ auth }) {
    useTheme();
    const roles = auth?.roles || auth?.user?.roles || [];
    const isStudent = roles.includes('Estudiante');
    const isAdminLike = roles.some((role) =>
        ['Super Administrador', 'Administrador', 'Docente'].includes(role)
    );

    return (
        <>
            <Head title="INTELECTA - Plataforma Académica Preuniversitaria" />

            <div className="min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-brand-secondary selection:text-white dark:bg-slate-950 dark:text-slate-100 dark:[&_section]:border-slate-800 dark:[&_section]:bg-slate-950 dark:[&_.rounded-3xl]:border-slate-800 dark:[&_.rounded-3xl]:bg-slate-900 dark:[&_.bg-white]:bg-slate-900 dark:[&_.bg-slate-50]:bg-slate-950 dark:[&_.bg-slate-100]:bg-slate-900 dark:[&_.text-slate-950]:text-slate-50 dark:[&_.text-slate-900]:text-slate-100 dark:[&_.text-slate-800]:text-slate-200 dark:[&_.text-slate-700]:text-slate-300 dark:[&_.text-slate-600]:text-slate-400 dark:[&_.text-slate-500]:text-slate-400 dark:[&_.border-slate-200]:border-slate-800 dark:[&_.border-slate-100]:border-slate-800">
                {/* 1. Header superior */}
                <header className="sticky top-0 z-50 w-full border-b border-slate-200/80 bg-white/95 backdrop-blur-xl transition-all dark:border-slate-800 dark:bg-slate-950/95">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex h-20 items-center justify-between">
                            {/* Logo */}
                            <div className="flex items-center gap-3">
                                <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-brand-primary to-brand-secondary text-white shadow-lg shadow-brand-primary/20">
                                    <GraduationCap className="h-6 w-6" />
                                </span>
                                <span className="bg-gradient-to-r from-slate-900 via-brand-primary to-brand-primary bg-clip-text text-2xl font-black tracking-[0.15em] text-transparent dark:from-white dark:via-slate-200 dark:to-brand-accent">
                                    INTELECTA
                                </span>
                            </div>

                            {/* Navegación */}
                            <nav className="hidden items-center gap-8 text-sm font-semibold text-slate-600 dark:text-slate-300 lg:flex">
                                <a href="#enfoque" className="transition hover:text-brand-secondary">Enfoque académico</a>
                                <a href="#modulos" className="transition hover:text-brand-secondary">Módulos</a>
                                <a href="#evaluaciones" className="transition hover:text-brand-secondary">Plantillas</a>
                                <a href="#analytics" className="transition hover:text-brand-secondary">Learning Analytics</a>
                                <a href="#contexto" className="transition hover:text-brand-secondary">Contexto preuniversitario</a>
                            </nav>

                            {/* Botón de Acción */}
                            <div className="flex items-center gap-2">
                                <ThemeToggle />
                                {!auth.user ? (
                                    <>
                                        <Link
                                            href="/evaluaciones-postulante"
                                            className="hidden items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-800 transition-all hover:-translate-y-0.5 hover:bg-slate-50 active:translate-y-0 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 xl:inline-flex"
                                        >
                                            Ver evaluaciones académicas
                                        </Link>
                                        <Link
                                            href={route('login')}
                                            className="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-950/20 transition-all hover:bg-slate-900 hover:shadow-slate-950/30 hover:-translate-y-0.5 active:translate-y-0"
                                        >
                                            Iniciar sesión
                                            <ArrowRight className="h-4 w-4" />
                                        </Link>
                                    </>
                                ) : isStudent ? (
                                    <>
                                        <Link
                                            href="/estudiante/evaluaciones"
                                            className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-secondary px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-secondary/10 transition-all hover:bg-brand-secondary/90 hover:-translate-y-0.5 active:translate-y-0"
                                        >
                                            Ingresar a Evaluaciones
                                        </Link>
                                        <Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                            className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 hover:bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-800 transition-all hover:-translate-y-0.5 active:translate-y-0"
                                        >
                                            Cerrar sesión
                                        </Link>
                                    </>
                                ) : isAdminLike ? (
                                    <>
                                        <Link
                                            href={route('dashboard')}
                                            className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-primary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-primary/20 transition-all hover:bg-brand-primary/90 hover:shadow-brand-primary/30 hover:-translate-y-0.5 active:translate-y-0"
                                        >
                                            Ir al panel
                                            <ArrowRight className="h-4 w-4" />
                                        </Link>
                                        <Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                            className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 hover:bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-800 transition-all hover:-translate-y-0.5 active:translate-y-0"
                                        >
                                            Cerrar sesión
                                        </Link>
                                    </>
                                ) : (
                                    <>
                                        <Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                            className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 hover:bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-800 transition-all hover:-translate-y-0.5 active:translate-y-0"
                                        >
                                            Cerrar sesión
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </header>

                {/* 2. Hero principal */}
                <section className="relative overflow-hidden bg-gradient-to-b from-white via-slate-50 to-slate-100 py-16 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 sm:py-24 lg:py-28">
                    {/* Elementos abstractos de fondo */}
                                <div className="absolute top-1/4 left-10 w-72 h-72 rounded-full bg-brand-primary/10 blur-3xl pointer-events-none" />
                    <div className="absolute bottom-10 right-10 w-96 h-96 rounded-full bg-brand-secondary/10 blur-3xl pointer-events-none" />

                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                            {/* Text content */}
                            <div className="lg:col-span-7 text-center lg:text-left">
                                <div className="inline-flex items-center gap-2.5 rounded-full border border-brand-secondary/20 bg-brand-secondary/5 px-4 py-1.5 text-xs font-semibold text-brand-secondary mb-6">
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
                                    {!auth.user ? (
                                        <>
                                            <Link
                                                href="/evaluaciones-postulante"
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-secondary px-6 py-3.5 text-base font-bold text-white shadow-xl shadow-brand-secondary/10 transition-all hover:bg-brand-secondary/90 hover:-translate-y-0.5"
                                            >
                                                Ver evaluaciones académicas
                                            </Link>
                                            <Link
                                                href={route('login')}
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 hover:bg-slate-800 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-slate-900/20 transition-all hover:-translate-y-0.5"
                                            >
                                                Iniciar sesión
                                                <ArrowRight className="h-5 w-5" />
                                            </Link>
                                        </>
                                    ) : isStudent ? (
                                        <>
                                            <Link
                                                href="/estudiante/evaluaciones"
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-secondary px-6 py-3.5 text-base font-bold text-white shadow-xl shadow-brand-secondary/10 transition-all hover:bg-brand-secondary/90 hover:-translate-y-0.5"
                                            >
                                                Ingresar a Evaluaciones
                                            </Link>
                                            <Link
                                                href={route('logout')}
                                                method="post"
                                                as="button"
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3.5 text-base font-semibold text-slate-800 shadow-sm transition-all hover:-translate-y-0.5"
                                            >
                                                Cerrar sesión
                                            </Link>
                                        </>
                                    ) : isAdminLike ? (
                                        <>
                                            <Link
                                                href={route('dashboard')}
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-primary px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-brand-primary/20 transition-all hover:bg-brand-primary/90 hover:shadow-brand-primary/30 hover:-translate-y-0.5"
                                            >
                                                Ir al panel
                                                <ArrowRight className="h-5 w-5" />
                                            </Link>
                                            <Link
                                                href={route('logout')}
                                                method="post"
                                                as="button"
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3.5 text-base font-semibold text-slate-800 shadow-sm transition-all hover:-translate-y-0.5"
                                            >
                                                Cerrar sesión
                                            </Link>
                                        </>
                                    ) : (
                                        <>
                                            <Link
                                                href={route('logout')}
                                                method="post"
                                                as="button"
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3.5 text-base font-semibold text-slate-800 shadow-sm transition-all hover:-translate-y-0.5"
                                            >
                                                Cerrar sesión
                                            </Link>
                                        </>
                                    )}
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
                            <h2 className="text-xs font-bold uppercase tracking-[0.25em] text-brand-secondary">Áreas Metodológicas</h2>
                            <p className="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                                Preparación académica orientada al contexto preuniversitario
                            </p>
                            <div className="mt-4 h-1 w-12 bg-brand-secondary mx-auto rounded-full" />
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {/* Card Aritmética */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-brand-secondary/30 hover:shadow-lg hover:shadow-brand-secondary/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary/8 text-brand-primary transition group-hover:bg-brand-primary group-hover:text-white">
                                    <Percent className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-brand-primary">Aritmética</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Fundamentos numéricos, proporciones, porcentajes y operaciones básicas aplicadas.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Inicial-Intermedio
                                </span>
                            </div>

                            {/* Card Álgebra */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-brand-secondary/30 hover:shadow-lg hover:shadow-brand-secondary/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary/8 text-brand-primary transition group-hover:bg-brand-primary group-hover:text-white">
                                    <Calculator className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-brand-primary">Álgebra</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Ecuaciones, sistemas lineales, polinomios y funciones esenciales para el cálculo.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Intermedio
                                </span>
                            </div>

                            {/* Card Geometría y Trigonometría */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-brand-secondary/30 hover:shadow-lg hover:shadow-brand-secondary/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary/8 text-brand-primary transition group-hover:bg-brand-primary group-hover:text-white">
                                    <Compass className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-brand-primary">Geometría y Trigonometría</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Relaciones espaciales, teoremas fundamentales e identidades trigonométricas.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Intermedio-Avanzado
                                </span>
                            </div>

                            {/* Card Razonamiento Lógico-Matemático */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-brand-secondary/30 hover:shadow-lg hover:shadow-brand-secondary/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary/8 text-brand-primary transition group-hover:bg-brand-primary group-hover:text-white">
                                    <Binary className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-brand-primary">Razonamiento Lógico-Matemático</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Patrones, inducción, deducción y lógica formal para aptitud académica.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Avanzado
                                </span>
                            </div>

                            {/* Card Estadística y Probabilidades */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-brand-secondary/30 hover:shadow-lg hover:shadow-brand-secondary/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary/8 text-brand-primary transition group-hover:bg-brand-primary group-hover:text-white">
                                    <TrendingUp className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-brand-primary">Estadística y Probabilidades</h3>
                                <p className="mt-3 text-sm leading-relaxed text-slate-600">
                                    Análisis de datos, medidas de tendencia central y cálculo de probabilidades.
                                </p>
                                <span className="inline-flex mt-5 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                    Nivel Intermedio
                                </span>
                            </div>

                            {/* Card Resolución de Problemas */}
                            <div className="group rounded-3xl border border-slate-200/80 bg-slate-50/50 p-8 shadow-sm transition-all duration-350 hover:bg-white hover:border-brand-secondary/30 hover:shadow-lg hover:shadow-brand-secondary/5 hover:-translate-y-1">
                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-primary/8 text-brand-primary transition group-hover:bg-brand-primary group-hover:text-white">
                                    <BookOpen className="h-6 w-6" />
                                </div>
                                <h3 className="mt-6 text-lg font-bold text-slate-900 group-hover:text-brand-primary">Resolución de Problemas</h3>
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
                            <h2 className="text-xs font-bold uppercase tracking-[0.25em] text-brand-secondary">Instrumentos Académicos</h2>
                            <p className="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                                Plantillas académicas listas para evaluación
                            </p>
                            <p className="mt-4 text-base text-slate-600">
                                Composición estructurada de evaluaciones alineadas a los objetivos preuniversitarios de la región.
                            </p>
                            <div className="mt-4 h-1 w-12 bg-brand-secondary mx-auto rounded-full" />
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                            {/* Cards list */}
                            <div className="lg:col-span-7 space-y-4">
                                {/* Simulacro UMSA */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-brand-secondary/30 hover:shadow-md">
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
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-brand-secondary">Alta</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-primary/8 text-brand-primary text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>

                                {/* UCB */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-brand-secondary/30 hover:shadow-md">
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
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-brand-secondary">Media-Alta</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-primary/8 text-brand-primary text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>

                                {/* Ciencias Económicas */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-brand-secondary/30 hover:shadow-md">
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
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-brand-secondary">Media</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-primary/8 text-brand-primary text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>

                                {/* ESFM */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-brand-secondary/30 hover:shadow-md">
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
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-brand-secondary">Media</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-primary/8 text-brand-primary text-sm font-bold">
                                            100
                                        </div>
                                    </div>
                                </div>

                                {/* Mixta Álgebra */}
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition hover:border-brand-secondary/30 hover:shadow-md">
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
                                            <p className="text-xs text-slate-500">Dificultad: <span className="font-bold text-brand-secondary">Intermedia</span></p>
                                        </div>
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-primary/8 text-brand-primary text-sm font-bold">
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
                            <h2 className="text-xs font-bold uppercase tracking-[0.25em] text-brand-secondary">Proceso Metodológico</h2>
                            <p className="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
                                Del registro del postulante al análisis académico
                            </p>
                            <div className="mt-4 h-1 w-12 bg-brand-secondary mx-auto rounded-full" />
                        </div>

                        <div className="relative">
                            {/* Line separator (desktop) */}
                            <div className="absolute top-1/2 left-4 right-4 hidden lg:block -translate-y-1/2 h-0.5 bg-slate-200 pointer-events-none" />

                            <div className="relative z-10 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
                                {/* Paso 1 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-brand-primary/8 border-2 border-brand-primary text-lg font-bold text-brand-primary transition duration-300 group-hover:bg-brand-primary group-hover:text-white">
                                        1
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Registro de postulantes</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Ingreso y centralización de perfiles, colegios y datos sociodemográficos.
                                    </p>
                                </div>

                                {/* Paso 2 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-brand-primary/8 border-2 border-brand-primary text-lg font-bold text-brand-primary transition duration-300 group-hover:bg-brand-primary group-hover:text-white">
                                        2
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Universidad y carrera</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Asignación del perfil de postulación y el nivel de exigencia lógica.
                                    </p>
                                </div>

                                {/* Paso 3 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-brand-primary/8 border-2 border-brand-primary text-lg font-bold text-brand-primary transition duration-300 group-hover:bg-brand-primary group-hover:text-white">
                                        3
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Banco de preguntas</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Organización de reactivos y alternativas por nivel de dificultad.
                                    </p>
                                </div>

                                {/* Paso 4 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-brand-primary/8 border-2 border-brand-primary text-lg font-bold text-brand-primary transition duration-300 group-hover:bg-brand-primary group-hover:text-white">
                                        4
                                    </span>
                                    <h3 className="mt-6 text-base font-bold text-slate-900">Plantillas de evaluación</h3>
                                    <p className="mt-2 text-xs leading-relaxed text-slate-500 max-w-[200px]">
                                        Generación de pruebas estructuradas por tema y ponderación.
                                    </p>
                                </div>

                                {/* Paso 5 */}
                                <div className="flex flex-col items-center text-center group">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-brand-primary/8 border-2 border-brand-primary text-lg font-bold text-brand-primary transition duration-300 group-hover:bg-brand-primary group-hover:text-white">
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
                                <div className="inline-flex items-center gap-2 rounded-full bg-brand-primary/8 px-3 py-1 text-xs font-semibold text-brand-primary">
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
                                        <CheckCircle2 className="h-5 w-5 text-brand-secondary shrink-0 mt-0.5" />
                                        <div>
                                            <p className="text-sm font-bold text-slate-900">Perfiles dinámicos</p>
                                            <p className="text-xs text-slate-500">Segmentación precisa según la casa de estudios seleccionada.</p>
                                        </div>
                                    </div>
                                    <div className="flex gap-3">
                                        <CheckCircle2 className="h-5 w-5 text-brand-secondary shrink-0 mt-0.5" />
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
                                <div className="inline-flex items-center gap-2 rounded-full bg-brand-primary/8 px-3 py-1 text-xs font-semibold text-brand-primary">
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
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-brand-secondary/20">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-primary/10 text-brand-primary">
                                                <FileText className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Indicadores por postulante</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-500">
                                            Progreso de avance temático y nivel de precisión por reactivo.
                                        </p>
                                    </div>

                                    {/* Card 2 */}
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-brand-secondary/20">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-primary/10 text-brand-primary">
                                                <Activity className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Resultados por área</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-500">
                                            Desempeño en Aritmética, Álgebra, Geometría y Razonamiento Lógico.
                                        </p>
                                    </div>

                                    {/* Card 3 */}
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-brand-secondary/20">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-primary/10 text-brand-primary">
                                                <CheckCircle2 className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Riesgo académico</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-500">
                                            Alertas inmediatas ante puntajes inferiores al estándar institucional.
                                        </p>
                                    </div>

                                    {/* Card 4 */}
                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-brand-secondary/20">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-primary/10 text-brand-primary">
                                                <Award className="h-4 w-4" />
                                            </span>
                                            <h3 className="text-sm font-bold text-slate-900">Recomendaciones de refuerzo</h3>
                                        </div>
                                        <p className="mt-2 text-xs leading-relaxed text-slate-500">
                                            Detección de falencias y sugerencias automáticas de repaso específico.
                                        </p>
                                    </div>

                                    {/* Card 5 (Full width on sm) */}
                                    <div className="sm:col-span-2 rounded-2xl border border-brand-secondary/20 bg-brand-secondary/5 p-5 transition hover:border-brand-secondary/30">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-primary/10 text-brand-primary">
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
                    <div className="absolute inset-0 bg-gradient-to-tr from-[#0A1628] via-[#16213E] to-[#0A1628] pointer-events-none" />
                    <div className="absolute -left-40 top-0 h-96 w-96 rounded-full bg-brand-secondary/10 blur-3xl pointer-events-none" />
                    <div className="absolute -right-40 bottom-0 h-96 w-96 rounded-full bg-brand-accent/10 blur-3xl pointer-events-none" />

                    <div className="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 text-center space-y-8">
                        <span className="flex h-16 w-16 items-center justify-center rounded-3xl bg-brand-primary text-white mx-auto shadow-xl shadow-brand-primary/30">
                            <GraduationCap className="h-8 w-8" />
                        </span>
                        <h2 className="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-[1.15]">
                            Una plataforma académica para transformar la evaluación preuniversitaria
                        </h2>
                        <p className="max-w-3xl mx-auto text-base sm:text-lg text-slate-300 leading-relaxed">
                            INTELECTA integra postulantes, bancos de preguntas, plantillas académicas y análisis institucional en una base preparada para el seguimiento y la mejora del desempeño lógico-matemático.
                        </p>
                        <div className="pt-4">
                            {!auth.user ? (
                                <Link
                                    href={route('login')}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-primary px-8 py-4 text-base font-bold text-white shadow-xl shadow-brand-primary/30 transition-all hover:bg-brand-primary/90 hover:shadow-brand-primary/40 hover:-translate-y-0.5"
                                >
                                    Ingresar al sistema
                                    <ArrowRight className="h-5 w-5" />
                                </Link>
                            ) : isStudent ? (
                                <Link
                                    href="/estudiante/evaluaciones"
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-secondary px-8 py-4 text-base font-bold text-white shadow-xl shadow-brand-secondary/10 transition-all hover:bg-brand-secondary/90 hover:-translate-y-0.5"
                                >
                                    Ingresar a Evaluaciones
                                    <ArrowRight className="h-5 w-5" />
                                </Link>
                            ) : isAdminLike ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-primary px-8 py-4 text-base font-bold text-white shadow-xl shadow-brand-primary/30 transition-all hover:bg-brand-primary/90 hover:shadow-brand-primary/40 hover:-translate-y-0.5"
                                >
                                    Ir al panel
                                    <ArrowRight className="h-5 w-5" />
                                </Link>
                            ) : (
                                <Link
                                    href="/"
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-primary px-8 py-4 text-base font-bold text-white shadow-xl shadow-brand-primary/30 transition-all hover:bg-brand-primary/90 hover:shadow-brand-primary/40 hover:-translate-y-0.5"
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
