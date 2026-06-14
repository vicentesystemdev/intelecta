import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { 
    FileChartColumn, 
    TrendingUp, 
    Users, 
    AlertTriangle,
    GraduationCap,
    Sparkles,
    Briefcase,
    Lightbulb,
    FileText
} from 'lucide-react';

const kpis = [
    { title: 'Resultados referenciales', value: '742', detail: 'Registros evaluativos locales', icon: FileText, tone: 'secondary' },
    { title: 'Promedio general', value: '68.4 / 100', detail: 'Desempeño medio de cohorte', icon: TrendingUp, tone: 'info' },
    { title: 'Postulantes en seguimiento', value: '39', detail: 'Casos asignados a tutorías', icon: Users, tone: 'accent' },
    { title: 'Materias críticas', value: '1', detail: 'Matemática con mayor brecha', icon: AlertTriangle, tone: 'danger' },
];

const applicantsFollowUp = [
    { id: 1, name: 'Camila Quispe Mamani', career: 'Ingeniería Civil (UMSA)', average: 58, criticalSubject: 'Matemática', status: 'Atención prioritaria', recommendation: 'Asignar nivelación de álgebra básica y seguimiento semanal.' },
    { id: 2, name: 'Diego Choque Condori', career: 'Ingeniería de Sistemas (EMI)', average: 62, criticalSubject: 'Física', status: 'Atención prioritaria', recommendation: 'Reforzar resolución gráfica de problemas físicos.' },
    { id: 3, name: 'Valeria Flores Apaza', career: 'Economía (UCB)', average: 65, criticalSubject: 'Matemática', status: 'Seguimiento regular', recommendation: 'Secuencia corta de funciones y análisis gráfico.' },
    { id: 4, name: 'Luis Fernando Huanca', career: 'Ingeniería de Sistemas (UMSA)', average: 67, criticalSubject: 'Química', status: 'Seguimiento regular', recommendation: 'Programar práctica en relaciones molares.' },
    { id: 5, name: 'Andrea Nina Callisaya', career: 'Ingeniería Comercial (UNIFRANZ)', average: 69, criticalSubject: 'Matemática', status: 'Seguimiento regular', recommendation: 'Reforzar identidades básicas bajo tiempo controlado.' },
    { id: 6, name: 'Mateo Quispe Flores', career: 'Medicina (UMSA)', average: 85, criticalSubject: 'Ninguna', status: 'Alto rendimiento', recommendation: 'Promover al grupo de simulacros avanzados.' },
];

export default function ResultadosSeguimiento() {
    return (
        <AdminLayout
            title="Resultados y Seguimiento"
            subtitle="Lectura académica preliminar y priorización del seguimiento de rendimiento preuniversitario."
            wide
        >
            <Head title="Resultados y Seguimiento" />

            <div className="space-y-8">
                {/* Banner principal */}
                <section className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-8 text-white shadow-xl shadow-brand-primary/20">
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:24px_24px]" />
                    <div className="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-accent/15 blur-3xl" />
                    
                    <div className="relative z-10 space-y-4">
                        <div className="inline-flex items-center gap-2 rounded-full bg-brand-accent/10 px-3 py-1 text-xs font-semibold text-brand-accent ring-1 ring-brand-accent/25">
                            <Sparkles className="h-3.5 w-3.5" />
                            Elite Prep Institute
                        </div>
                        
                        <h2 className="text-3xl font-black tracking-tight sm:text-4xl text-white">
                            Resultados y Seguimiento
                        </h2>
                        
                        <p className="max-w-3xl text-sm sm:text-base leading-relaxed text-slate-200">
                            Analice la dispersión de calificaciones, identifique materias críticas y priorice las tutorías académicas. Utilice los filtros integrados para enfocar los planes de nivelación.
                        </p>
                    </div>
                </section>

                {/* Grid de KPIs */}
                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {kpis.map((kpi, idx) => {
                        const Icon = kpi.icon;
                        const toneClasses = {
                            secondary: 'bg-brand-secondary/10 text-brand-secondary border-brand-secondary/20',
                            info: 'bg-brand-info/10 text-brand-info border-brand-info/20',
                            accent: 'bg-brand-accent/10 text-brand-accent border-brand-accent/20',
                            danger: 'bg-brand-danger/10 text-brand-danger border-brand-danger/20',
                        }[kpi.tone];

                        return (
                            <Card key={idx} className="bg-brand-card border border-brand-border rounded-2xl p-5 sm:p-6 shadow-sm dark:shadow-black/20 transition-all duration-300 hover:shadow-md hover:scale-[1.01]">
                                <CardContent className="p-0 flex items-center justify-between">
                                    <div className="space-y-1">
                                        <p className="text-xs font-semibold text-text-muted uppercase tracking-wider">{kpi.title}</p>
                                        <p className="text-3xl font-extrabold text-text-main">{kpi.value}</p>
                                        <p className="text-[11px] text-text-muted">{kpi.detail}</p>
                                    </div>
                                    <span className={`flex h-12 w-12 items-center justify-center rounded-xl border ${toneClasses}`}>
                                        <Icon className="h-6 w-6" />
                                    </span>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Tabla de Postulantes en Seguimiento */}
                <Card className="bg-brand-card border border-brand-border rounded-2xl shadow-sm dark:shadow-black/20 overflow-hidden">
                    <CardHeader className="border-b border-brand-border p-5 sm:p-6 bg-brand-primary/[0.01]">
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <CardTitle className="text-base font-bold text-text-main">
                                    Seguimiento del Rendimiento de Postulantes
                                </CardTitle>
                                <p className="text-xs text-text-muted mt-1">
                                    Calificaciones promedio y nivelación recomendada por postulante.
                                </p>
                            </div>
                            
                            <div className="max-w-xl rounded-xl border border-brand-info/20 bg-brand-info/10 px-4 py-3">
                                <p className="text-xs leading-5 text-text-muted">
                                    Esta vista presenta una lectura referencial del seguimiento académico. La integración completa con evaluaciones aplicadas será consolidada en la etapa de resultados trazables.
                                </p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {/* Tabla Desktop */}
                        <div className="hidden md:block overflow-x-auto">
                            <table className="w-full table-fixed">
                                <thead>
                                    <tr>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[22%]">Postulante</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[20%]">Carrera Objetivo</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[12%]">Promedio</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[14%]">Materia Crítica</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[16%]">Estado Alerta</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[16%]">Recomendación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {applicantsFollowUp.map((applicant) => {
                                        const statusClasses = {
                                            'Alto rendimiento': 'bg-brand-success/10 text-brand-success border border-brand-success/20',
                                            'Seguimiento regular': 'bg-brand-warning/10 text-brand-warning border border-brand-warning/20',
                                            'Atención prioritaria': 'bg-brand-danger/10 text-brand-danger border border-brand-danger/20',
                                        }[applicant.status];

                                        const progressClasses = applicant.average >= 80 
                                            ? 'text-brand-success' 
                                            : applicant.average >= 60 
                                                ? 'text-brand-warning' 
                                                : 'text-brand-danger';

                                        return (
                                            <tr key={applicant.id} className="bg-brand-card border-b border-brand-border/60 hover:bg-brand-primary/[0.02] dark:hover:bg-brand-border/20 transition-colors">
                                                <td className="p-4 text-xs font-semibold text-text-main whitespace-normal break-words leading-snug">
                                                    <div className="flex items-center gap-2">
                                                        <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-primary/5 text-2xs font-black text-text-muted border border-brand-border">
                                                            {applicant.name.split(' ').slice(0, 2).map(n => n[0]).join('')}
                                                        </span>
                                                        {applicant.name}
                                                    </div>
                                                </td>
                                                <td className="p-4 text-xs font-medium text-text-muted whitespace-normal break-words leading-snug">
                                                    {applicant.career}
                                                </td>
                                                <td className="p-4 text-xs font-extrabold leading-none">
                                                    <span className={progressClasses}>
                                                        {applicant.average}%
                                                    </span>
                                                </td>
                                                <td className="p-4 text-xs font-semibold text-text-main">
                                                    {applicant.criticalSubject === 'Ninguna' ? (
                                                        <span className="text-text-muted font-normal">—</span>
                                                    ) : (
                                                        <span className="text-brand-danger">{applicant.criticalSubject}</span>
                                                    )}
                                                </td>
                                                <td className="p-4 text-xs font-medium text-text-main">
                                                    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-2xs font-semibold ${statusClasses}`}>
                                                        {applicant.status}
                                                    </span>
                                                </td>
                                                <td className="p-4 text-xs font-medium text-text-muted whitespace-normal break-words leading-snug">
                                                    {applicant.recommendation}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>

                        {/* Tarjetas Mobile */}
                        <div className="block md:hidden divide-y divide-brand-border">
                            {applicantsFollowUp.map((applicant) => {
                                const statusClasses = {
                                    'Alto rendimiento': 'bg-brand-success/10 text-brand-success border border-brand-success/20',
                                    'Seguimiento regular': 'bg-brand-warning/10 text-brand-warning border border-brand-warning/20',
                                    'Atención prioritaria': 'bg-brand-danger/10 text-brand-danger border border-brand-danger/20',
                                }[applicant.status];

                                return (
                                    <div key={applicant.id} className="p-5 space-y-4 bg-brand-card">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2.5">
                                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-primary/5 text-2xs font-black text-text-muted border border-brand-border">
                                                    {applicant.name.split(' ').slice(0, 2).map(n => n[0]).join('')}
                                                </span>
                                                <div>
                                                    <h4 className="text-xs font-bold text-text-main">{applicant.name}</h4>
                                                    <p className="text-[10px] text-text-muted">{applicant.career}</p>
                                                </div>
                                            </div>
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ${statusClasses}`}>
                                                {applicant.status}
                                            </span>
                                        </div>
                                        
                                        <div className="grid grid-cols-2 gap-3 pt-2 text-xs border-t border-brand-border/40">
                                            <div>
                                                <p className="text-[10px] font-semibold text-text-muted uppercase tracking-wider">Promedio</p>
                                                <p className="font-extrabold text-text-main mt-0.5">{applicant.average}%</p>
                                            </div>
                                            <div>
                                                <p className="text-[10px] font-semibold text-text-muted uppercase tracking-wider">Materia Crítica</p>
                                                <p className="font-semibold text-brand-danger mt-0.5">{applicant.criticalSubject}</p>
                                            </div>
                                        </div>

                                        <div className="rounded-xl border border-brand-border bg-brand-primary/[0.01] p-3 text-xs leading-relaxed">
                                            <div className="flex items-start gap-1.5">
                                                <Lightbulb className="h-4 w-4 text-brand-secondary shrink-0 mt-0.5" />
                                                <div>
                                                    <strong className="text-text-main font-semibold">Recomendación: </strong>
                                                    <span className="text-text-muted">{applicant.recommendation}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>

                {/* Mensaje institucional de interpretación académica */}
                <div className="rounded-xl border border-brand-border bg-brand-card p-5 text-xs text-text-muted leading-relaxed">
                    <p className="font-bold text-text-main uppercase tracking-wider flex items-center gap-1.5">
                        <Sparkles className="h-4 w-4 text-brand-accent" />
                        Mensaje institucional de interpretación académica:
                    </p>
                    <p className="mt-2 text-text-muted">
                        Esta vista consolida una lectura referencial del seguimiento académico, tomando como base la estructura evaluativa del sistema. Los resultados deben interpretarse como apoyo a la coordinación académica y no como predicción definitiva.
                    </p>
                </div>
            </div>
        </AdminLayout>
    );
}
