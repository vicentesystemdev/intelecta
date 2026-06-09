import StatusBadge from '@/Components/StatusBadge';
import Pagination from '@/Components/Pagination';
import ModalInstitucional from '@/Components/ModalInstitucional';
import ConfirmModal from '@/Components/ConfirmModal';
import PlantillaEvaluacionForm from '@/Components/Evaluaciones/PlantillaEvaluacionForm';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Progress } from '@/Components/ui/progress';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Clock3, Eye, FileCheck2, Pencil, Plus, Search, ToggleLeft } from 'lucide-react';
import { useState } from 'react';

export default function Index({ plantillas, preguntasDisponibles = [], filtros = {}, permisos }) {
    const [filters, setFilters] = useState({ 
        buscar: filtros.buscar || '', 
        estado_plan: filtros.estado_plan || '' 
    });

    const [formModal, setFormModal] = useState({ open: false, plantilla: null });
    const [detailModal, setDetailModal] = useState({ open: false, plantilla: null });
    const [statusModal, setStatusModal] = useState({ open: false, plantilla: null });
    const [changingStatus, setChangingStatus] = useState(false);

    const submit = (event) => { 
        event.preventDefault(); 
        router.get(route('plantillas-evaluacion.index'), filters, { preserveState: true, replace: true }); 
    };

    const handleOpenStatusModal = (plantilla) => {
        setStatusModal({ open: true, plantilla });
    };

    const changeStatus = () => {
        if (!statusModal.plantilla) return;
        setChangingStatus(true);
        router.patch(
            route('plantillas-evaluacion.cambiar-estado', statusModal.plantilla.id_plan), 
            {}, 
            { 
                preserveScroll: true,
                onSuccess: () => setStatusModal({ open: false, plantilla: null }),
                onFinish: () => setChangingStatus(false),
            }
        );
    };

    // Calcular el puntaje total acumulado de una plantilla
    const calculateTotalScore = (plantilla) => {
        if (!plantilla || !plantilla.preguntas) return 0;
        return plantilla.preguntas.reduce((sum, pregunta) => sum + Number(pregunta.pivot?.puntaje_pp || 0), 0);
    };

    return (
        <AdminLayout title="Plantillas de Evaluación" subtitle="Instrumentos académicos reutilizables construidos desde el banco institucional.">
            <Head title="Plantillas de evaluación" />
            <div className="mb-5 flex items-center justify-between">
                <p className="text-sm text-slate-500">{plantillas.total} plantillas configuradas</p>
                {permisos.crear && (
                    <Button 
                        type="button" 
                        className="bg-indigo-700 hover:bg-indigo-800"
                        onClick={() => setFormModal({ open: true, plantilla: null })}
                    >
                        <Plus className="h-4 w-4" />Nueva plantilla
                    </Button>
                )}
            </div>

            <Card className="mb-5 border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                <CardContent className="p-4">
                    <form onSubmit={submit} className="flex flex-col gap-3 sm:flex-row">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                            <Input className="pl-9 bg-white dark:bg-slate-950 dark:border-slate-800 text-slate-900 dark:text-slate-100" value={filters.buscar} onChange={(e) => setFilters({ ...filters, buscar: e.target.value })} placeholder="Buscar plantilla por nombre" />
                        </div>
                        <select className="h-10 rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm sm:w-48" value={filters.estado_plan} onChange={(e) => setFilters({ ...filters, estado_plan: e.target.value })}>
                            <option value="">Todos los estados</option>
                            <option value="activa">Activas</option>
                            <option value="inactiva">Inactivas</option>
                        </select>
                        <Button className="bg-indigo-700 hover:bg-indigo-800">Filtrar</Button>
                    </form>
                </CardContent>
            </Card>

            <div className="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
                {plantillas.data.map((plantilla) => {
                    const totalPuntaje = Number(plantilla.puntaje_total || 0);
                    return (
                        <Card key={plantilla.id_plan} className="h-full min-h-[230px] flex flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                            <div className="h-1.5 bg-gradient-to-r from-indigo-600 to-cyan-400 shrink-0" />
                            
                            {/* Header: title (left) + difficulty badge (right) */}
                            <div className="flex items-start justify-between gap-4 p-5 pb-4">
                                <h3 
                                    className="flex-1 text-base font-semibold leading-snug text-slate-900 dark:text-slate-100 min-h-[44px] pr-2 break-words" 
                                    title={plantilla.nombre_plan}
                                    style={{ display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}
                                >
                                    {plantilla.nombre_plan}
                                </h3>
                                <Badge variant="outline" className="capitalize shrink-0 mt-0.5 border-indigo-100 bg-indigo-50 dark:border-indigo-900 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300">
                                    {plantilla.dificultad_plan?.replace('-', ' ') || 'No definida'}
                                </Badge>
                            </div>

                            {/* Body: description, statistics grid, and progress bar */}
                            <div className="px-5 py-4 space-y-4 flex-1 flex flex-col">
                                <p 
                                    className="text-xs text-slate-500 dark:text-slate-400 min-h-[32px] leading-relaxed break-words"
                                    style={{ display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}
                                >
                                    {plantilla.descripcion_plan || 'Instrumento académico institucional.'}
                                </p>
                                
                                <div className="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 dark:bg-slate-950 p-3 text-center">
                                    <div>
                                        <p className="text-base font-bold text-slate-900 dark:text-slate-100">{plantilla.preguntas_count}</p>
                                        <p className="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">Preguntas</p>
                                    </div>
                                    <div>
                                        <p className="text-base font-bold text-slate-900 dark:text-slate-100">{totalPuntaje.toFixed(0)}</p>
                                        <p className="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">Puntos</p>
                                    </div>
                                    <div>
                                        <p className="flex items-center justify-center gap-1 text-base font-bold text-slate-900 dark:text-slate-100">
                                            <Clock3 className="h-3.5 w-3.5 text-slate-400" />{plantilla.duracion_minutos_plan || '—'}
                                        </p>
                                        <p className="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">Minutos</p>
                                    </div>
                                </div>
                                
                                {/* Progress bar */}
                                <div className="space-y-1">
                                    <div className="flex justify-between text-xs font-medium text-slate-500 dark:text-slate-400">
                                        <span>Progreso de ponderación</span>
                                        <span className={Math.abs(totalPuntaje - 100) < 0.01 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500 dark:text-amber-400'}>
                                            {totalPuntaje.toFixed(0)}% / 100%
                                        </span>
                                    </div>
                                    <Progress value={Math.min(totalPuntaje, 100)} className="h-1.5" />
                                </div>
                            </div>
                            
                            {/* Footer: status + actions */}
                            <div className="mt-auto flex items-center justify-between gap-3 border-t border-slate-200 dark:border-slate-800 px-5 py-4 bg-slate-50/20 dark:bg-slate-900/60">
                                <span className="font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider text-[10px] truncate shrink-0">
                                    <span className="hidden sm:inline">TODAS SOBRE </span>100 PTS
                                </span>
                                <div className="flex items-center gap-2 sm:gap-3 shrink-0">
                                    <StatusBadge status={plantilla.estado_plan === 'activa' ? 'Activa' : 'Inactiva'} />
                                    <div className="flex items-center gap-1 border-l border-slate-200 dark:border-slate-800 pl-2">
                                        <Button 
                                            type="button" 
                                            variant="ghost" 
                                            size="icon"
                                            onClick={() => setDetailModal({ open: true, plantilla })}
                                            aria-label="Ver detalle"
                                            className="h-8 w-8 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                                        >
                                            <Eye className="h-4 w-4" />
                                        </Button>
                                        {permisos.editar && (
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="icon"
                                                onClick={() => setFormModal({ open: true, plantilla })}
                                                aria-label="Editar plantilla"
                                                className="h-8 w-8 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                                            >
                                                <Pencil className="h-4 w-4" />
                                            </Button>
                                        )}
                                        {permisos.cambiarEstado && (
                                            <Button 
                                                variant="ghost" 
                                                size="icon" 
                                                onClick={() => handleOpenStatusModal(plantilla)}
                                                aria-label="Cambiar estado"
                                                className="h-8 w-8 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                                            >
                                                <ToggleLeft className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </Card>
                    );
                })}
            </div>
            
            <Pagination links={plantillas.links} />

            {/* Modal para Crear y Editar Plantillas */}
            <ModalInstitucional
                open={formModal.open}
                onOpenChange={(open) => setFormModal((current) => ({ ...current, open }))}
                title={formModal.plantilla ? 'Editar plantilla de evaluación' : 'Nueva plantilla de evaluación'}
                description={formModal.plantilla ? 'Actualice la configuración general y la ponderación de preguntas asociadas.' : 'Configure la duración, dificultad y asocie las preguntas desde el banco de reactivos.'}
                size="xl"
            >
                {formModal.open && (
                    <PlantillaEvaluacionForm
                        key={formModal.plantilla ? `edit-plan-${formModal.plantilla.id_plan}` : 'create-plan'}
                        plantilla={formModal.plantilla}
                        preguntasDisponibles={preguntasDisponibles}
                        submitRoute={formModal.plantilla ? route('plantillas-evaluacion.update', formModal.plantilla.id_plan) : route('plantillas-evaluacion.store')}
                        method={formModal.plantilla ? 'put' : 'post'}
                        submitLabel={formModal.plantilla ? 'Guardar cambios' : 'Crear plantilla'}
                        onCancel={() => setFormModal({ open: false, plantilla: null })}
                    />
                )}
            </ModalInstitucional>

            {/* Modal para Ver Detalle de Plantilla */}
            <ModalInstitucional
                open={detailModal.open}
                onOpenChange={(open) => setDetailModal((current) => ({ ...current, open }))}
                title="Detalle de Plantilla de Evaluación"
                description="Estructura, ponderación y reactivos del instrumento académico."
                size="xl"
            >
                {detailModal.plantilla && (() => {
                    const total = calculateTotalScore(detailModal.plantilla);
                    return (
                        <div className="space-y-5">
                            <Card className="gap-0 overflow-hidden border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                                <div className="h-2 bg-gradient-to-r from-indigo-700 via-blue-500 to-cyan-400" />
                                <CardContent className="p-6">
                                    <div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                        <div className="max-w-3xl">
                                            <div className="mb-3 flex flex-wrap gap-2">
                                                <StatusBadge status={detailModal.plantilla.estado_plan === 'activa' ? 'Activa' : 'Inactiva'} />
                                                <Badge variant="outline" className="capitalize">{detailModal.plantilla.dificultad_plan?.replace('-', ' ')}</Badge>
                                            </div>
                                            <h3 className="text-xl font-bold text-slate-900 dark:text-slate-100">{detailModal.plantilla.nombre_plan}</h3>
                                            <p className="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{detailModal.plantilla.descripcion_plan || 'Sin descripción detallada.'}</p>
                                            
                                            {detailModal.plantilla.objetivo_plan && (
                                                <div className="mt-4 rounded-xl bg-indigo-50 dark:bg-indigo-950/30 p-4">
                                                    <p className="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Objetivo académico</p>
                                                    <p className="mt-1 text-sm text-indigo-900 dark:text-indigo-200">{detailModal.plantilla.objetivo_plan}</p>
                                                </div>
                                            )}
                                        </div>
                                        <div className="grid min-w-64 grid-cols-3 gap-3 text-center">
                                            <div className="rounded-xl bg-slate-50 dark:bg-slate-900 p-3">
                                                <p className="text-xl font-bold text-indigo-700 dark:text-indigo-400">{detailModal.plantilla.preguntas?.length || 0}</p>
                                                <p className="text-xs text-slate-500">Preguntas</p>
                                            </div>
                                            <div className="rounded-xl bg-slate-50 dark:bg-slate-900 p-3">
                                                <p className="text-xl font-bold text-indigo-700 dark:text-indigo-400">{total.toFixed(0)}</p>
                                                <p className="text-xs text-slate-500">Puntos</p>
                                            </div>
                                            <div className="rounded-xl bg-slate-50 dark:bg-slate-900 p-3">
                                                <p className="flex items-center justify-center gap-1 text-xl font-bold text-indigo-700 dark:text-indigo-400">
                                                    <Clock3 className="h-4 w-4" />{detailModal.plantilla.duracion_minutos_plan}
                                                </p>
                                                <p className="text-xs text-slate-500">Minutos</p>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                                <CardHeader className="border-b p-5 dark:border-slate-800 flex flex-row items-center justify-between">
                                    <CardTitle className="text-base font-bold text-slate-900 dark:text-slate-100">Composición académica</CardTitle>
                                    <div className="w-52">
                                        <div className="mb-1 flex justify-between text-xs font-semibold">
                                            <span>Ponderación</span>
                                            <span className={Math.abs(total - 100) < 0.01 ? 'text-emerald-600' : 'text-amber-600'}>
                                                {total.toFixed(2)} / 100
                                            </span>
                                        </div>
                                        <Progress value={Math.min(total, 100)} />
                                    </div>
                                </CardHeader>
                                <CardContent className="divide-y divide-slate-100 dark:divide-slate-800 p-0">
                                    {detailModal.plantilla.preguntas && detailModal.plantilla.preguntas.map((pregunta, index) => (
                                        <div key={pregunta.id_preg} className="grid gap-3 p-5 md:grid-cols-[40px_1fr_100px] md:items-center">
                                            <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-950 font-bold text-indigo-700 dark:text-indigo-300">
                                                {index + 1}
                                            </span>
                                            <div>
                                                <p className="font-medium leading-6 text-slate-900 dark:text-slate-100">{pregunta.enunciado_preg}</p>
                                                <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                    {pregunta.tema?.area?.nombre_area || 'Área general'} · {pregunta.tema?.nombre_tem || 'Sin tema'}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-lg font-bold text-indigo-700 dark:text-indigo-400">{Number(pregunta.pivot?.puntaje_pp || 0).toFixed(2)}</p>
                                                <p className="text-xs text-slate-500">puntos</p>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        </div>
                    );
                })()}
            </ModalInstitucional>

            {/* Modal para Confirmar Acción de Cambio de Estado */}
            <ConfirmModal
                open={statusModal.open}
                onOpenChange={(open) => setStatusModal((current) => ({ ...current, open }))}
                title={statusModal.plantilla?.estado_plan === 'activa' ? 'Inactivar plantilla' : 'Activar plantilla'}
                message={
                    statusModal.plantilla 
                        ? `¿Confirma que desea ${statusModal.plantilla.estado_plan === 'activa' ? 'inactivar' : 'activar'} la plantilla "${statusModal.plantilla.nombre_plan}"?` 
                        : ''
                }
                confirmLabel={statusModal.plantilla?.estado_plan === 'activa' ? 'Inactivar' : 'Activar'}
                variant={statusModal.plantilla?.estado_plan === 'activa' ? 'danger' : 'normal'}
                processing={changingStatus}
                onConfirm={changeStatus}
            />
        </AdminLayout>
    );
}
