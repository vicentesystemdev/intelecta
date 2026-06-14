import React from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { 
    Users, 
    BookOpen, 
    Layers, 
    GraduationCap, 
    ShieldCheck, 
    UserCheck, 
    Clock, 
    Search, 
    ChevronRight,
    Sparkles
} from 'lucide-react';

const kpis = [
    { title: 'Tutores referenciales', value: '18', detail: 'Equipo tutorial acreditado', icon: Users, tone: 'secondary' },
    { title: 'Materias cubiertas', value: '5', detail: 'Áreas clave preuniversitarias', icon: BookOpen, tone: 'info' },
    { title: 'Grupos asignables', value: '12', detail: 'Aulas de nivelación activa', icon: Layers, tone: 'accent' },
    { title: 'Seguimientos activos', value: '45', detail: 'Postulantes bajo mentoría', icon: ShieldCheck, tone: 'success' },
];

const tutorsData = [
    { id: 1, name: 'Dr. Alejandro Vargas', specialty: 'Matemática Preuniversitaria', mainSubject: 'Álgebra y Cálculo', workload: '20h semanales', groups: 'Grupo A, Grupo B', status: 'Activo' },
    { id: 2, name: 'Msc. Beatriz Gutiérrez', specialty: 'Física Mecánica', mainSubject: 'Cinemática y Dinámica', workload: '16h semanales', groups: 'Grupo C', status: 'Activo' },
    { id: 3, name: 'Ing. Carlos Mendoza', specialty: 'Química General', mainSubject: 'Estequiometría y Gases', workload: '12h semanales', groups: 'Grupo A, Grupo D', status: 'Activo' },
    { id: 4, name: 'Dra. Diana Salvatierra', specialty: 'Razonamiento Académico', mainSubject: 'Lógica Matemática', workload: '18h semanales', groups: 'Grupo B, Grupo C', status: 'Activo' },
    { id: 5, name: 'Lic. Esteban Flores', specialty: 'Nivelación STEM', mainSubject: 'Introducción a la Ingeniería', workload: '14h semanales', groups: 'Grupo D', status: 'Activo' },
];

export default function TutoresAcademicos() {
    return (
        <AdminLayout
            title="Tutores Académicos"
            subtitle="Gestión institucional del personal académico asignado a materias, grupos y seguimiento de postulantes."
            wide
        >
            <Head title="Tutores Académicos" />

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
                            Tutores Académicos
                        </h2>
                        
                        <p className="max-w-3xl text-sm sm:text-base leading-relaxed text-slate-200">
                            Equipo tutorial enfocado en el alto rendimiento preuniversitario. Coordina la asignación de carga horaria, seguimiento de grupos y la calibración psicométrica de reactivos.
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
                            success: 'bg-brand-success/10 text-brand-success border-brand-success/20',
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

                {/* Sección de Datos Referenciales */}
                <Card className="bg-brand-card border border-brand-border rounded-2xl shadow-sm dark:shadow-black/20 overflow-hidden">
                    <CardHeader className="border-b border-brand-border p-5 sm:p-6 bg-brand-primary/[0.01]">
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <CardTitle className="text-base font-bold text-text-main">
                                    Personal Docente Asignado
                                </CardTitle>
                                <p className="text-xs text-text-muted mt-1">
                                    Seguimiento tutorial y carga académica referencial.
                                </p>
                            </div>
                            
                            <div className="relative max-w-xs w-full">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-text-muted" />
                                <input 
                                    type="text" 
                                    placeholder="Buscar tutor..." 
                                    className="w-full text-xs font-medium bg-brand-card border border-brand-border text-text-main rounded-xl pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-secondary focus:border-transparent transition-all"
                                    disabled
                                />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {/* Tabla Desktop */}
                        <div className="hidden md:block overflow-x-auto">
                            <table className="w-full table-fixed">
                                <thead>
                                    <tr>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[25%]">Nombre</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[25%]">Especialidad</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[20%]">Materia Principal</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[15%]">Carga</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[15%]">Grupos</th>
                                        <th className="bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4 w-[10%]">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {tutorsData.map((tutor) => (
                                        <tr key={tutor.id} className="bg-brand-card border-b border-brand-border/60 hover:bg-brand-primary/[0.02] dark:hover:bg-brand-border/20 transition-colors">
                                            <td className="p-4 text-xs font-semibold text-text-main whitespace-normal break-words leading-snug">
                                                <div className="flex items-center gap-2">
                                                    <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-secondary/15 text-xs font-bold text-brand-secondary">
                                                        {tutor.name.split(' ').slice(1).map(n => n[0]).join('') || 'T'}
                                                    </span>
                                                    {tutor.name}
                                                </div>
                                            </td>
                                            <td className="p-4 text-xs font-medium text-text-main whitespace-normal break-words leading-snug">
                                                {tutor.specialty}
                                            </td>
                                            <td className="p-4 text-xs font-medium text-text-main whitespace-normal break-words leading-snug">
                                                {tutor.mainSubject}
                                            </td>
                                            <td className="p-4 text-xs font-medium text-text-muted">
                                                <div className="flex items-center gap-1">
                                                    <Clock className="h-3.5 w-3.5 text-brand-secondary" />
                                                    {tutor.workload}
                                                </div>
                                            </td>
                                            <td className="p-4 text-xs font-medium text-text-main">
                                                <div className="flex flex-wrap gap-1.5">
                                                    {tutor.groups.split(', ').map((g, idx) => (
                                                        <span key={idx} className="bg-brand-primary/5 text-text-muted px-2 py-0.5 rounded-md text-[10px] font-semibold border border-brand-border">
                                                            {g}
                                                        </span>
                                                    ))}
                                                </div>
                                            </td>
                                            <td className="p-4 text-xs font-medium text-text-main">
                                                <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-2xs font-semibold bg-brand-success/10 text-brand-success border border-brand-success/20">
                                                    {tutor.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Tarjetas Mobile */}
                        <div className="block md:hidden divide-y divide-brand-border">
                            {tutorsData.map((tutor) => (
                                <div key={tutor.id} className="p-5 space-y-4 bg-brand-card">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2.5">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-secondary/15 text-xs font-bold text-brand-secondary">
                                                {tutor.name.split(' ').slice(1).map(n => n[0]).join('') || 'T'}
                                            </span>
                                            <div>
                                                <h4 className="text-xs font-bold text-text-main">{tutor.name}</h4>
                                                <p className="text-[10px] text-text-muted">{tutor.specialty}</p>
                                            </div>
                                        </div>
                                        <span className="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-brand-success/10 text-brand-success border border-brand-success/20">
                                            {tutor.status}
                                        </span>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-3 pt-2 text-xs border-t border-brand-border/40">
                                        <div>
                                            <p className="text-[10px] font-semibold text-text-muted uppercase tracking-wider">Materia</p>
                                            <p className="font-medium text-text-main mt-0.5">{tutor.mainSubject}</p>
                                        </div>
                                        <div>
                                            <p className="text-[10px] font-semibold text-text-muted uppercase tracking-wider">Carga</p>
                                            <p className="font-medium text-text-main mt-0.5 flex items-center gap-1">
                                                <Clock className="h-3 w-3 text-brand-secondary" />
                                                {tutor.workload}
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <p className="text-[10px] font-semibold text-text-muted uppercase tracking-wider">Grupos asignados</p>
                                        <div className="flex flex-wrap gap-1.5 mt-1">
                                            {tutor.groups.split(', ').map((g, idx) => (
                                                <span key={idx} className="bg-brand-primary/5 text-text-muted px-2 py-0.5 rounded-md text-[10px] font-semibold border border-brand-border">
                                                    {g}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Nota de pie */}
                <div className="rounded-xl border border-brand-border bg-brand-card p-4 text-xs text-text-muted leading-relaxed">
                    <p className="font-semibold text-text-main">Nota de Gestión Académica:</p>
                    <p className="mt-1">
                        Esta es una vista referencial del equipo tutorial del instituto. La asignación final de horas y la validación de responsabilidades en evaluaciones se consolida desde la coordinación académica en cada periodo preuniversitario.
                    </p>
                </div>
            </div>
        </AdminLayout>
    );
}
