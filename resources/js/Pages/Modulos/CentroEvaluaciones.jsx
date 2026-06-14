import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { 
    ClipboardCheck, 
    BookCopy, 
    FileQuestion, 
    GraduationCap, 
    BarChart3,
    ArrowRight,
    CheckCircle2,
    Clock,
    Sparkles,
    Calendar
} from 'lucide-react';

const evaluationFlow = [
    { label: 'Plantillas', desc: 'Estructuración y ponderación del examen' },
    { label: 'Simulacros Programados', desc: 'Calendarización por cohorte y grupo' },
    { label: 'Aplicación', desc: 'Ejecución y temporización supervisada' },
    { label: 'Resultados', desc: 'Captura psicométrica del desempeño' },
    { label: 'Reportes', desc: 'Consolidación de brechas y reportes' },
];

const evaluationCards = [
    { name: 'Diagnóstico General', desc: 'Medición de perfil de ingreso y brechas iniciales transversales.', subjects: 'Aptitud lógica, Verbal, Matemática básica', duration: '120 min', status: 'Activo', type: 'Diagnóstico' },
    { name: 'Matemática Preuniversitaria', desc: 'Evaluación de álgebra, funciones, trigonometría y geometría analítica.', subjects: 'Álgebra, Trigonometría, Geometría', duration: '90 min', status: 'Activo', type: 'Focalizado' },
    { name: 'Física Preuniversitaria', desc: 'Examen enfocado en física clásica, cinemática, vectores y estática.', subjects: 'Cinemática, Dinámica, Vectores', duration: '90 min', status: 'Activo', type: 'Focalizado' },
    { name: 'Química Preuniversitaria', desc: 'Evaluación conceptual de compuestos, estequiometría y balance de ecuaciones.', subjects: 'Química Inorgánica, Estequiometría', duration: '90 min', status: 'Activo', type: 'Focalizado' },
    { name: 'Evaluación Mixta STEM', desc: 'Prueba integrada de alta exigencia para carreras tecnológicas y científicas.', subjects: 'Matemática avanzada, Física aplicada', duration: '150 min', status: 'Planificado', type: 'Simulacro' },
    { name: 'Final Preuniversitaria', desc: 'Simulacro final integrador con estructura idéntica a la admisión real.', subjects: 'Todas las áreas curriculares', duration: '180 min', status: 'Planificado', type: 'Simulacro' },
];

export default function CentroEvaluaciones() {
    return (
        <AdminLayout
            title="Centro de Evaluaciones"
            subtitle="Planificación, asignación y seguimiento de exámenes y simulacros institucionales."
            wide
        >
            <Head title="Centro de Evaluaciones" />

            <div className="space-y-8">
                {/* Banner principal con gradiente institucional */}
                <section className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-8 text-white shadow-xl shadow-brand-primary/20">
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:24px_24px]" />
                    <div className="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-accent/15 blur-3xl" />
                    
                    <div className="relative z-10 space-y-4">
                        <div className="inline-flex items-center gap-2 rounded-full bg-brand-accent/10 px-3 py-1 text-xs font-semibold text-brand-accent ring-1 ring-brand-accent/25">
                            <Sparkles className="h-3.5 w-3.5" />
                            Elite Prep Institute
                        </div>
                        
                        <h2 className="text-3xl font-black tracking-tight sm:text-4xl text-white">
                            Centro de Evaluaciones
                        </h2>
                        
                        <p className="max-w-3xl text-sm sm:text-base leading-relaxed text-slate-200">
                            Plataforma central para la calendarización, supervisión y evaluación del desempeño de los postulantes. Estructure reactivos, calibre plantillas de examen y consolide la información psicométrica.
                        </p>
                    </div>
                </section>

                {/* Acceso Rápido */}
                <section className="space-y-3">
                    <h3 className="text-xs font-extrabold uppercase tracking-wider text-text-muted">Enlaces de Acceso Rápido</h3>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Link href="/preguntas" className="group">
                            <Card className="bg-brand-card border border-brand-border rounded-2xl p-5 shadow-sm dark:shadow-black/20 hover:shadow-md hover:scale-[1.01] transition-all duration-300">
                                <CardContent className="p-0 flex items-center gap-4">
                                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-info/10 text-brand-info group-hover:bg-brand-info group-hover:text-white transition-all border border-brand-info/20">
                                        <FileQuestion className="h-5.5 w-5.5" />
                                    </span>
                                    <div className="min-w-0 flex-1">
                                        <p className="text-xs font-bold text-text-main group-hover:text-brand-secondary transition-colors truncate">Banco de Preguntas</p>
                                        <p className="text-[10px] text-text-muted mt-0.5">Gestión de reactivos por área</p>
                                    </div>
                                    <ArrowRight className="h-4 w-4 text-text-muted group-hover:translate-x-1 transition-transform" />
                                </CardContent>
                            </Card>
                        </Link>
                        <Link href="/plantillas-evaluacion" className="group">
                            <Card className="bg-brand-card border border-brand-border rounded-2xl p-5 shadow-sm dark:shadow-black/20 hover:shadow-md hover:scale-[1.01] transition-all duration-300">
                                <CardContent className="p-0 flex items-center gap-4">
                                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-secondary/10 text-brand-secondary group-hover:bg-brand-secondary group-hover:text-white transition-all border border-brand-secondary/20">
                                        <BookCopy className="h-5.5 w-5.5" />
                                    </span>
                                    <div className="min-w-0 flex-1">
                                        <p className="text-xs font-bold text-text-main group-hover:text-brand-secondary transition-colors truncate">Plantillas de Examen</p>
                                        <p className="text-[10px] text-text-muted mt-0.5">Definición de estructuras</p>
                                    </div>
                                    <ArrowRight className="h-4 w-4 text-text-muted group-hover:translate-x-1 transition-transform" />
                                </CardContent>
                            </Card>
                        </Link>
                        <Link href="/evaluaciones-postulante" className="group">
                            <Card className="bg-brand-card border border-brand-border rounded-2xl p-5 shadow-sm dark:shadow-black/20 hover:shadow-md hover:scale-[1.01] transition-all duration-300">
                                <CardContent className="p-0 flex items-center gap-4">
                                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-accent/10 text-brand-accent group-hover:bg-brand-accent group-hover:text-brand-primary transition-all border border-brand-accent/20">
                                        <GraduationCap className="h-5.5 w-5.5" />
                                    </span>
                                    <div className="min-w-0 flex-1">
                                        <p className="text-xs font-bold text-text-main group-hover:text-brand-secondary transition-colors truncate">Portal del Postulante</p>
                                        <p className="text-[10px] text-text-muted mt-0.5">Vista directa del postulante</p>
                                    </div>
                                    <ArrowRight className="h-4 w-4 text-text-muted group-hover:translate-x-1 transition-transform" />
                                </CardContent>
                            </Card>
                        </Link>
                        <Link href="/reportes-academicos" className="group">
                            <Card className="bg-brand-card border border-brand-border rounded-2xl p-5 shadow-sm dark:shadow-black/20 hover:shadow-md hover:scale-[1.01] transition-all duration-300">
                                <CardContent className="p-0 flex items-center gap-4">
                                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-success/10 text-brand-success group-hover:bg-brand-success group-hover:text-white transition-all border border-brand-success/20">
                                        <BarChart3 className="h-5.5 w-5.5" />
                                    </span>
                                    <div className="min-w-0 flex-1">
                                        <p className="text-xs font-bold text-text-main group-hover:text-brand-secondary transition-colors truncate">Reportes Académicos</p>
                                        <p className="text-[10px] text-text-muted mt-0.5">Rendimiento y estadísticas</p>
                                    </div>
                                    <ArrowRight className="h-4 w-4 text-text-muted group-hover:translate-x-1 transition-transform" />
                                </CardContent>
                            </Card>
                        </Link>
                    </div>
                </section>

                {/* Flujo del Proceso Evaluativo */}
                <section className="space-y-3">
                    <h3 className="text-xs font-extrabold uppercase tracking-wider text-text-muted">Ruta de Evaluación Académica</h3>
                    <Card className="bg-brand-card border border-brand-border rounded-2xl p-5 sm:p-6 shadow-sm dark:shadow-black/20">
                        <CardContent className="p-0">
                            <div className="relative flex flex-col lg:flex-row justify-between items-start gap-6 lg:gap-4">
                                <div className="absolute top-[22px] left-6 right-6 h-0.5 bg-brand-border hidden lg:block z-0" />
                                
                                {evaluationFlow.map((flow, index) => (
                                    <div key={index} className="relative z-10 flex lg:flex-col items-start lg:items-center gap-4 lg:gap-2 lg:text-center flex-1">
                                        <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-primary text-white font-extrabold text-xs shadow-md border-4 border-brand-card ring-2 ring-brand-secondary/30">
                                            {index + 1}
                                        </span>
                                        <div>
                                            <p className="text-xs font-bold text-text-main leading-snug">{flow.label}</p>
                                            <p className="text-[10px] text-text-muted mt-1 leading-normal max-w-[160px] lg:mx-auto">
                                                {flow.desc}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {/* Grid de Plantillas / Exámenes Programados */}
                <section className="space-y-3">
                    <h3 className="text-xs font-extrabold uppercase tracking-wider text-text-muted">Plantillas y Exámenes de Nivelación</h3>
                    
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {evaluationCards.map((card, idx) => {
                            const statusColor = card.status === 'Activo' 
                                ? 'bg-brand-success/10 text-brand-success border border-brand-success/20'
                                : 'bg-brand-warning/10 text-brand-warning border border-brand-warning/20';

                            return (
                                <Card key={idx} className="bg-brand-card border border-brand-border rounded-2xl p-5 sm:p-6 shadow-sm dark:shadow-black/20 hover:shadow-md hover:scale-[1.01] transition-all duration-300 flex flex-col justify-between h-full">
                                    <CardContent className="p-0 space-y-4 flex-1 flex flex-col justify-between">
                                        <div>
                                            <div className="flex items-center justify-between gap-3">
                                                <span className="inline-flex items-center rounded-md px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-brand-primary/5 text-text-muted border border-brand-border">
                                                    {card.type}
                                                </span>
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ${statusColor}`}>
                                                    {card.status}
                                                </span>
                                            </div>

                                            <h4 className="text-sm font-bold text-text-main mt-3 leading-snug">{card.name}</h4>
                                            <p className="text-xs text-text-muted mt-1.5 leading-relaxed">
                                                {card.desc}
                                            </p>
                                        </div>

                                        <div className="pt-4 mt-4 border-t border-brand-border/60 space-y-2.5 text-xs text-text-muted">
                                            <div className="flex items-start gap-1.5 leading-snug">
                                                <CheckCircle2 className="h-4 w-4 shrink-0 text-brand-secondary mt-0.5" />
                                                <span>
                                                    <strong className="text-text-main font-semibold">Materias:</strong> {card.subjects}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-1.5 leading-none">
                                                <Clock className="h-4 w-4 text-brand-accent shrink-0" />
                                                <span>
                                                    <strong className="text-text-main font-semibold">Duración:</strong> {card.duration}
                                                </span>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                </section>

                {/* Nota de pie */}
                <div className="rounded-xl border border-brand-border bg-brand-card p-4 text-xs text-text-muted leading-relaxed">
                    <p className="font-semibold text-text-main">Orientación Académica sobre Exámenes:</p>
                    <p className="mt-1">
                        Las evaluaciones parametrizadas en esta vista se derivan de la configuración psicométrica básica del instituto. Para modificar pesos curriculares o el orden temático, acceda al apartado de Plantillas de Examen.
                    </p>
                </div>
            </div>
        </AdminLayout>
    );
}
