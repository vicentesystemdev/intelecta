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
    estado = 'Módulo planificado para la siguiente iteración académica', 
    moduloRelacionado = 'Dashboard General', 
    proximaEvolucion = 'Integración modular progresiva dentro del sistema.' 
}) {
    // Determinar si debemos mostrar el botón de reportes
    const mostrarReportes = titulo === 'Learning Analytics' || titulo === 'Riesgo Académico' || titulo === 'Resultados';

    return (
        <AdminLayout
            title={titulo}
            subtitle="Planificación del desarrollo evolutivo de la plataforma INTELECTA."
        >
            <Head title={`Módulo ${titulo}`} />

            <div className="mx-auto max-w-5xl space-y-8">
                {/* Botón de retroceso */}
                <div className="flex items-center justify-between">
                    <Link 
                        href="/dashboard" 
                        className="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-indigo-600 transition"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Volver al panel principal
                    </Link>
                    
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-700/10">
                        <Sparkles className="h-3 w-3 animate-pulse" />
                        INTELECTA Roadmap
                    </span>
                </div>

                {/* Banner principal */}
                <section className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-950 via-indigo-900 to-blue-950 p-8 text-white shadow-xl shadow-indigo-950/20">
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:24px_24px]" />
                    <div className="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-500/10 blur-3xl" />
                    
                    <div className="relative z-10 space-y-4">
                        <div className="inline-flex items-center gap-2 rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-semibold text-cyan-300 ring-1 ring-cyan-500/20">
                            <Layers className="h-3.5 w-3.5" />
                            Planificación del Sistema
                        </div>
                        
                        <h2 className="text-3xl font-black tracking-tight sm:text-4xl">
                            {titulo}
                        </h2>
                        
                        <p className="max-w-3xl text-base leading-relaxed text-indigo-200">
                            {descripcion}
                        </p>
                        
                        <div className="flex flex-wrap gap-4 pt-2">
                            <div className="flex items-center gap-2 rounded-xl bg-white/5 px-4 py-2 text-xs font-medium backdrop-blur-sm ring-1 ring-white/10">
                                <span className="h-2.5 w-2.5 rounded-full bg-cyan-400" />
                                <span>{estado}</span>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Panel de detalles y Mockup Visual */}
                <div className="grid gap-6 md:grid-cols-5">
                    {/* Detalles */}
                    <div className="md:col-span-2 space-y-6">
                        <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                            <CardHeader className="border-b border-slate-100 pb-4">
                                <CardTitle className="text-sm font-bold uppercase tracking-wider text-slate-500">
                                    Información del Módulo
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4 pt-6">
                                <div className="space-y-1">
                                    <span className="text-xs font-semibold text-slate-400">Estado de la funcionalidad</span>
                                    <p className="text-sm font-medium text-slate-700">
                                        Funcionalidad preparada dentro del mapa de evolución de INTELECTA.
                                    </p>
                                </div>
                                <div className="space-y-1">
                                    <span className="text-xs font-semibold text-slate-400">Frecuencia de integración</span>
                                    <p className="text-sm font-medium text-slate-700">
                                        Este apartado forma parte del crecimiento progresivo del sistema.
                                    </p>
                                </div>
                                <div className="space-y-1">
                                    <span className="text-xs font-semibold text-slate-400">Modulo Relacionado</span>
                                    <p className="text-sm font-medium text-indigo-600">
                                        {moduloRelacionado}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 bg-gradient-to-br from-indigo-50/50 to-cyan-50/30 shadow-sm ring-1 ring-slate-200/85">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-sm font-bold text-slate-900">
                                    <Calendar className="h-4 w-4 text-indigo-600" />
                                    Próxima Evolución
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="pt-0">
                                <p className="text-xs leading-relaxed text-slate-600">
                                    {proximaEvolucion}
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Simulación Visual de Estructura Académica (Mockup de Fidelidad Alta) */}
                    <div className="md:col-span-3">
                        <Card className="h-full border-0 bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden flex flex-col justify-between">
                            <CardHeader className="border-b border-slate-100 bg-slate-50/50">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-sm font-bold text-slate-900">
                                            Estructura Operativa
                                        </CardTitle>
                                        <CardDescription className="text-xs text-slate-500">
                                            Base institucional preparada para futuras operaciones académicas.
                                        </CardDescription>
                                    </div>
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-700/10">
                                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                        Diseño Estructurado
                                    </span>
                                </div>
                            </CardHeader>
                            <CardContent className="p-6 flex-1 flex flex-col justify-center space-y-6">
                                {/* Representación de flujo */}
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                                <BookOpen className="h-4 w-4" />
                                            </span>
                                            <div>
                                                <p className="text-xs font-semibold text-slate-900">Entrada de datos</p>
                                                <p className="text-[10px] text-slate-400">Postulantes y plantillas académicas</p>
                                            </div>
                                        </div>
                                        <ChevronRight className="h-4 w-4 text-slate-400" />
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-100 text-cyan-700">
                                                <Layers className="h-4 w-4" />
                                            </span>
                                            <div>
                                                <p className="text-xs font-semibold text-slate-900">{titulo}</p>
                                                <p className="text-[10px] text-slate-400">Procesamiento y análisis</p>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Gráfico conceptual o esqueleto */}
                                    <div className="rounded-2xl border border-slate-100 bg-slate-50/30 p-4 space-y-3">
                                        <div className="flex justify-between items-center text-[10px] text-slate-400 uppercase tracking-wider font-semibold">
                                            <span>Cobertura metodológica</span>
                                            <span>100% planificado</span>
                                        </div>
                                        <div className="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                            <div className="h-full w-full bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-500 rounded-full animate-pulse" />
                                        </div>
                                        <p className="text-[10px] text-slate-500 leading-normal">
                                            La arquitectura de servicios de INTELECTA cuenta con la reserva operacional para la automatización total de este apartado académico.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex flex-col sm:flex-row gap-3 justify-end">
                                    {mostrarReportes && (
                                        <Link href={route('reportes-academicos.index')}>
                                            <Button variant="outline" className="w-full sm:w-auto inline-flex items-center gap-2 rounded-xl text-xs font-semibold h-10 border-slate-200">
                                                <BarChart3 className="h-4 w-4 text-indigo-600" />
                                                Ver Reportes Académicos
                                            </Button>
                                        </Link>
                                    )}
                                    <Link href="/dashboard">
                                        <Button className="w-full sm:w-auto inline-flex items-center gap-2 rounded-xl text-xs font-semibold h-10 bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/10">
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
