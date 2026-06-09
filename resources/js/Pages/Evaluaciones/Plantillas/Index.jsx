import StatusBadge from '@/Components/StatusBadge';
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
                        <Card key={plantilla.id_plan} className="gap-0 overflow-hidden border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                            <div className="h-1.5 bg-gradient-to-r from-indigo-600 to-cyan-400" />
                            <CardContent className="p-5">
                                <div className="mb-4 flex items-start justify-between gap-3">
                                    <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                                        <FileCheck2 className="h-5 w-5" />
                                    </div>
                                    <StatusBadge status={plantilla.estado_plan === 'activa' ? 'Activa' : 'Inactiva'} />
                                </div>
                                <h2 className="min-h-14 font-semibold leading-6 text-slate-900 dark:text-slate-100">{plantilla.nombre_plan}</h2>
                                <p className="mt-2 line-clamp-2 min-h-10 text-sm text-slate-500 dark:text-slate-400">{plantilla.descripcion_plan || 'Instrumento académico institucional.'}</p>
                                
                                <div className="mt-5 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 dark:bg-slate-900 p-3 text-center">
                                    <div>
                                        <p className="text-lg font-bold text-indigo-700 dark:text-indigo-400">{plantilla.preguntas_count}</p>
                                        <p className="text-[11px] text-slate-500">Preguntas</p>
                                    </div>
                                    <div>
                                        <p className="text-lg font-bold text-indigo-700 dark:text-indigo-400">{totalPuntaje.toFixed(0)}</p>
                                        <p className="text-[11px] text-slate-500">Puntos</p>
                                    </div>
                                    <div>
                                        <p className="flex items-center justify-center gap-1 text-lg font-bold text-indigo-700 dark:text-indigo-400">
                                            <Clock3 className="h-4 w-4" />{plantilla.duracion_minutos_plan || '—'}
                                        </p>
                                        <p className="text-[11px] text-slate-500">Minutos</p>
                                    </div>
                                </div>
                                
                                <div className="mt-4 flex items-center justify-between">
                                    <Badge variant="outline" className="capitalize">{plantilla.dificultad_plan?.replace('-', ' ') || 'No definida'}</Badge>
                                    <div className="flex gap-1">
                                        <Button 
                                            type="button" 
                                            variant="ghost" 
                                            size="icon"
                                            onClick={() => setDetailModal({ open: true, plantilla })}
                                            aria-label="Ver detalle"
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
                                            >
                                                <ToggleLeft className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>

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
