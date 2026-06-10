import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { 
    ArrowLeft, 
    Calendar, 
    Layers, 
    CheckCircle2, 
    Sparkles, 
    ChevronRight,
    BarChart3,
    BookOpen,
    ShieldAlert
} from 'lucide-react';

export default function ModuloPlanificado({ 
    titulo, 
    descripcion, 
    estado = 'Funcionalidad planificada para fase institucional', 
    moduloRelacionado = 'Dashboard General', 
    proximaEvolucion = 'Base de integración operativa y crecimiento progresivo de la plataforma.' 
}) {
    // Determinar si debemos mostrar el botón de reportes
    const mostrarReportes = titulo === 'Learning Analytics' || titulo === 'Riesgo Académico' || titulo === 'Resultados';

    return (
        <AdminLayout
            title={titulo}
            subtitle="Base de integración operativa y planificación de la plataforma INTELECTA."
        >
            <Head title={`Módulo ${titulo}`} />

            <div className="mx-auto max-w-5xl space-y-8">
                {/* Botón de retroceso */}
                <div className="flex items-center justify-between">
                    <Link 
                        href="/dashboard" 
                        className="inline-flex items-center gap-2 text-sm font-semibold text-text-muted transition hover:text-brand-secondary"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Volver al panel principal
                    </Link>
                    
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-brand-secondary/10 px-3 py-1 text-xs font-semibold text-brand-secondary ring-1 ring-brand-secondary/20">
                        <Sparkles className="h-3 w-3 animate-pulse" />
                        INTELECTA Roadmap
                    </span>
                </div>

                {/* Banner principal */}
                <section className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-8 text-white shadow-xl shadow-brand-primary/20">
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:24px_24px]" />
                    <div className="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-accent/10 blur-3xl" />
                    
                    <div className="relative z-10 space-y-4">
                        <div className="inline-flex items-center gap-2 rounded-full bg-brand-accent/10 px-3 py-1 text-xs font-semibold text-brand-accent ring-1 ring-brand-accent/20">
                            <Layers className="h-3.5 w-3.5" />
                            Módulo proyectado
                        </div>
                        
                        <h2 className="text-3xl font-black tracking-tight sm:text-4xl text-white">
                            {titulo}
                        </h2>
                        
                        <p className="max-w-3xl text-sm sm:text-base leading-relaxed text-slate-200">
                            {descripcion}
                        </p>
                        
                        <div className="flex flex-wrap gap-4 pt-2">
                            <div className="flex items-center gap-2 rounded-xl bg-white/5 px-4 py-2 text-xs font-medium backdrop-blur-sm ring-1 ring-white/10">
                                <span className="h-2.5 w-2.5 rounded-full bg-brand-accent" />
                                <span>{estado}</span>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Panel de detalles y vista conceptual */}
                <div className="grid gap-6 md:grid-cols-5">
                    {/* Detalles */}
                    <div className="md:col-span-2 space-y-6">
                        <Card className="bg-brand-card border border-brand-border rounded-2xl shadow-sm">
                            <CardHeader className="border-b border-brand-border pb-4">
                                <CardTitle className="text-sm font-bold uppercase tracking-wider text-text-muted">
                                    Información del Módulo
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 pt-6">
                                <div className="space-y-1">
                                    <span className="text-xs font-semibold text-text-muted">Estado de la funcionalidad</span>
                                    <p className="text-sm font-medium text-text-main">
                                        Módulo proyectado dentro del mapa de evolución de INTELECTA.
                                    </p>
                                </div>
                                <div className="space-y-1">
                                    <span className="text-xs font-semibold text-text-muted">Frecuencia de integración</span>
                                    <p className="text-sm font-medium text-text-main">
                                        Este apartado forma parte del crecimiento progresivo del sistema.
                                    </p>
                                </div>
                                <div className="space-y-1">
                                    <span className="text-xs font-semibold text-text-muted">Módulo Relacionado</span>
                                    <p className="text-sm font-medium text-brand-secondary">
                                        {moduloRelacionado}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="bg-brand-card border border-brand-border rounded-2xl shadow-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-sm font-bold text-text-main">
                                    <Calendar className="h-4 w-4 text-brand-secondary" />
                                    Fase Institucional Proyectada
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="pt-0">
                                <p className="text-xs leading-relaxed text-text-muted">
                                    {proximaEvolucion}
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Representación visual de la estructura académica */}
                    <div className="md:col-span-3">
                        <Card className="flex h-full flex-col justify-between overflow-hidden bg-brand-card border border-brand-border rounded-2xl shadow-sm">
                            <CardHeader className="border-b border-brand-border bg-brand-primary/[0.01]">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-sm font-bold text-text-main">
                                            Estructura Operativa
                                        </CardTitle>
                                        <CardDescription className="text-xs text-text-muted">
                                            Base institucional preparada para futuras operaciones académicas.
                                        </CardDescription>
                                    </div>
                                    <span className="inline-flex items-center gap-1 rounded-full bg-brand-success/10 px-2 py-1 text-[10px] font-bold text-brand-success ring-1 ring-brand-success/20">
                                        <span className="h-1.5 w-1.5 rounded-full bg-brand-success" />
                                        Diseño Estructurado
                                    </span>
                                </div>
                            </CardHeader>
                            <CardContent className="p-6 flex-1 flex flex-col justify-center space-y-6">
                                {/* Representación de flujo */}
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between rounded-2xl border border-brand-border bg-brand-primary/5 p-4">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-secondary/15 text-brand-secondary">
                                                <BookOpen className="h-4 w-4" />
                                            </span>
                                            <div>
                                                <p className="text-xs font-semibold text-text-main">Entrada de datos</p>
                                                <p className="text-[10px] text-text-muted">Postulantes y plantillas académicas</p>
                                            </div>
                                        </div>
                                        <ChevronRight className="h-4 w-4 text-text-muted" />
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-accent/15 text-brand-accent">
                                                <Layers className="h-4 w-4" />
                                            </span>
                                            <div>
                                                <p className="text-xs font-semibold text-text-main">{titulo}</p>
                                                <p className="text-[10px] text-text-muted">Procesamiento y análisis</p>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Gráfico conceptual o esqueleto */}
                                    <div className="space-y-3 rounded-2xl border border-brand-border bg-brand-primary/[0.02] p-4">
                                        <div className="flex justify-between items-center text-[10px] text-text-muted uppercase tracking-wider font-semibold">
                                            <span>Cobertura metodológica</span>
                                            <span>100% planificado</span>
                                        </div>
                                        <div className="h-2 w-full bg-brand-border/40 rounded-full overflow-hidden">
                                            <div className="h-full w-full bg-gradient-to-r from-brand-primary via-brand-secondary to-brand-accent rounded-full animate-pulse" />
                                        </div>
                                        <p className="text-[10px] leading-normal text-text-muted">
                                            La arquitectura de servicios de INTELECTA cuenta con la reserva operacional para la automatización total de este apartado académico.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex flex-col sm:flex-row gap-3 justify-end">
                                    {mostrarReportes && (
                                        <Link href={route('reportes-academicos.index')}>
                                            <Button variant="outline" className="inline-flex h-10 w-full items-center gap-2 rounded-xl border-brand-border text-xs font-semibold bg-brand-card text-text-main hover:bg-brand-border/30 sm:w-auto">
                                                <BarChart3 className="h-4 w-4 text-brand-secondary" />
                                                Ver Reportes Académicos
                                            </Button>
                                        </Link>
                                    )}
                                    <Link href="/dashboard">
                                        <Button className="w-full sm:w-auto inline-flex items-center gap-2 rounded-xl text-xs font-semibold h-10 bg-brand-secondary hover:bg-brand-secondary/90 text-white shadow-md">
                                            Ir al panel principal
                                        </Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
