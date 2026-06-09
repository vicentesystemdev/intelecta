import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { 
    GraduationCap, 
    ArrowRight, 
    ArrowLeft,
    BookOpen,
    BrainCircuit,
    Award,
    Clock,
    BookCopy,
    Lock,
    Percent,
    Calculator,
    Compass,
    Binary,
    TrendingUp,
    CheckCircle2
} from 'lucide-react';

const publicTemplates = [
    {
        name: 'Simulacro PSA - Facultad de Ingeniería UMSA',
        detail: 'Álgebra, Cálculo y Física aplicada a la ingeniería.',
        questions: 40,
        difficulty: 'Alta',
        badge: 'UMSA - Ingeniería'
    },
    {
        name: 'Prueba de Aptitud Académica - Razonamiento Lógico UCB',
        detail: 'Habilidades de inferencia lógica, relaciones analíticas y razonamiento numérico.',
        questions: 50,
        difficulty: 'Media-Alta',
        badge: 'UCB - Aptitud'
    },
    {
        name: 'Diagnóstico Matemático - Ciencias Económicas y Financieras',
        detail: 'Aritmética comercial, porcentajes, proporciones y modelación matemática simple.',
        questions: 30,
        difficulty: 'Media',
        badge: 'Economía y Financieras'
    },
    {
        name: 'Simulacro Psicotécnico - ESFM / Normal Simón Bolívar',
        detail: 'Lógica espacial, sucesiones conceptuales y aptitud docente.',
        questions: 60,
        difficulty: 'Media',
        badge: 'ESFM - Normal'
    },
    {
        name: 'Evaluación Mixta de Nivelación - Álgebra y Trigonometría',
        detail: 'Teoría de exponentes, ecuaciones de primer y segundo grado e identidades fundamentales.',
        questions: 35,
        difficulty: 'Intermedia',
        badge: 'Nivelación'
    }
];

const areas = [
    { name: 'Aritmética', icon: Percent, desc: 'Operaciones fundamentales, razones y proporciones.' },
    { name: 'Álgebra', icon: Calculator, desc: 'Ecuaciones, polinomios, funciones y despejes.' },
    { name: 'Geometría y Trigonometría', icon: Compass, desc: 'Triángulos, teoremas de Euclides y Pitágoras.' },
    { name: 'Razonamiento Lógico-Matemático', icon: Binary, desc: 'Sucesiones numéricas, lógica formal y abstracciones.' },
    { name: 'Estadística y Probabilidades', icon: TrendingUp, desc: 'Análisis de frecuencias, muestras y eventos aleatorios.' },
    { name: 'Resolución de Problemas', icon: BookOpen, desc: 'Modelación de enunciados y aplicación práctica.' }
];

export default function EvaluacionesPostulante() {
    return (
        <>
            <Head title="Evaluaciones Académicas - Postulantes" />
            
            <div className="min-h-screen bg-slate-50 text-slate-900 font-sans">
                {/* Header simple */}
                <header className="sticky top-0 z-50 w-full border-b border-slate-200/80 bg-white/95 backdrop-blur-xl">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex h-20 items-center justify-between">
                            <Link href="/" className="flex items-center gap-3">
                                <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white shadow-lg shadow-indigo-600/20">
                                    <GraduationCap className="h-6 w-6" />
                                </span>
                                <span className="text-2xl font-black tracking-[0.15em] text-slate-900">
                                    INTELECTA
                                </span>
                            </Link>

                            <Link 
                                href="/"
                                className="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-indigo-600 transition"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Volver a Inicio
                            </Link>
                        </div>
                    </div>
                </header>

                {/* Hero / Banner principal */}
                <section className="relative overflow-hidden py-16 sm:py-20 bg-gradient-to-b from-indigo-950 via-indigo-900 to-blue-950 text-white text-center">
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:20px_20px]" />
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />
                    
                    <div className="relative z-10 mx-auto max-w-3xl px-4 sm:px-6">
                        <span className="inline-flex items-center gap-2 rounded-full bg-cyan-500/10 px-4 py-1.5 text-xs font-semibold text-cyan-300 ring-1 ring-cyan-500/20 mb-6">
                            <Award className="h-4 w-4" />
                            Instrumentos de Evaluación Académica
                        </span>
                        
                        <h1 className="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-[1.1]">
                            Evaluaciones académicas para postulantes
                        </h1>
                        
                        <p className="mt-4 text-base sm:text-lg text-indigo-200">
                            Explora las áreas lógico-matemáticas y plantillas académicas disponibles para el seguimiento preuniversitario.
                        </p>
                    </div>
                </section>

                <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 space-y-16">
                    {/* Alerta de bloqueo */}
                    <div className="rounded-3xl border border-amber-200 bg-amber-50/70 p-6 sm:p-8 flex flex-col md:flex-row justify-between items-center gap-6 shadow-sm">
                        <div className="flex gap-4 items-start">
                            <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                <Lock className="h-6 w-6" />
                            </span>
                            <div className="space-y-1">
                                <h3 className="text-base font-bold text-amber-900">Acceso Restringido a la Evaluación</h3>
                                <p className="text-sm text-amber-700 leading-relaxed max-w-2xl">
                                    El acceso a evaluaciones aplicadas se realiza mediante una cuenta asignada por el instituto. Inicia sesión con tus credenciales de postulante asignadas para resolver las pruebas.
                                </p>
                            </div>
                        </div>
                        <div className="flex flex-col sm:flex-row gap-3 w-full md:w-auto shrink-0">
                            <Link href={route('login')} className="w-full sm:w-auto">
                                <button className="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-6 py-3.5 shadow-lg shadow-slate-900/10 transition">
                                    Iniciar sesión
                                    <ArrowRight className="h-4 w-4" />
                                </button>
                            </Link>
                        </div>
                    </div>

                    {/* Plantillas académicas */}
                    <section className="space-y-6">
                        <div className="flex flex-col md:flex-row justify-between items-start md:items-end gap-2">
                            <div>
                                <h2 className="text-2xl font-black text-slate-900">Plantillas Disponibles</h2>
                                <p className="text-sm text-slate-500 mt-1">Modelos de pruebas configuradas según exigencias institucionales.</p>
                            </div>
                            <span className="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full">
                                {publicTemplates.length} Exámenes planificados
                            </span>
                        </div>

                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {publicTemplates.map((template) => (
                                <div 
                                    key={template.name}
                                    className="group relative flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-350 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-500/5"
                                >
                                    <div className="space-y-4">
                                        <div className="flex justify-between items-center">
                                            <span className="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-600">
                                                {template.badge}
                                            </span>
                                            <span className="flex items-center gap-1 text-[10px] text-slate-400 font-semibold">
                                                <Clock className="h-3.5 w-3.5" />
                                                Tiempo Limitado
                                            </span>
                                        </div>

                                        <div className="space-y-1">
                                            <h3 className="text-base font-bold text-slate-950 leading-snug group-hover:text-indigo-950">
                                                {template.name}
                                            </h3>
                                            <p className="text-xs leading-relaxed text-slate-500">
                                                {template.detail}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                                        <span className="text-slate-500 font-medium">Reactivos: <span className="font-bold text-slate-800">{template.questions}</span></span>
                                        <span className="text-slate-500 font-medium">Dificultad: <span className="font-bold text-indigo-600">{template.difficulty}</span></span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>

                    {/* Áreas metodológicas */}
                    <section className="space-y-6">
                        <div>
                            <h2 className="text-2xl font-black text-slate-900">Áreas de Evaluación</h2>
                            <p className="text-sm text-slate-500 mt-1">Estructura curricular de competencias evaluadas mediante analíticas de aprendizaje.</p>
                        </div>

                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {areas.map((area) => {
                                const Icon = area.icon;
                                return (
                                    <div 
                                        key={area.name}
                                        className="flex gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-slate-300 transition"
                                    >
                                        <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                            <Icon className="h-6 w-6" />
                                        </span>
                                        <div className="space-y-1">
                                            <h3 className="text-sm font-bold text-slate-900">{area.name}</h3>
                                            <p className="text-xs leading-relaxed text-slate-500">{area.desc}</p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </section>

                    {/* Footer / CTA final */}
                    <section className="rounded-3xl bg-slate-900 text-white p-8 sm:p-12 text-center relative overflow-hidden">
                        <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:16px_16px]" />
                        <div className="relative z-10 max-w-xl mx-auto space-y-6">
                            <h3 className="text-xl font-bold">¿Listo para resolver tu evaluación?</h3>
                            <p className="text-sm text-indigo-200">
                                Inicia sesión para responder de forma interactiva el simulacro y recibir tu diagnóstico de desempeño inmediato.
                            </p>
                            <div className="flex flex-wrap gap-4 justify-center">
                                <Link href={route('login')}>
                                    <button className="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 hover:bg-cyan-500 text-indigo-950 font-bold text-xs px-6 py-3.5 shadow-lg shadow-cyan-400/10 transition">
                                        Ingresar al Sistema
                                        <ArrowRight className="h-4 w-4" />
                                    </button>
                                </Link>
                                <Link href="/">
                                    <button className="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/10 hover:bg-white/5 text-white font-semibold text-xs px-6 py-3.5 transition">
                                        Volver a Inicio
                                    </button>
                                </Link>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}
