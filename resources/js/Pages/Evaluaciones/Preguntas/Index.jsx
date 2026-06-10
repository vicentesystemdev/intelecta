import StatusBadge from '@/Components/StatusBadge';
import Pagination from '@/Components/Pagination';
import ModalInstitucional from '@/Components/ModalInstitucional';
import ConfirmModal from '@/Components/ConfirmModal';
import PreguntaForm from '@/Components/Evaluaciones/PreguntaForm';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Eye, Pencil, Plus, RotateCcw, Search, ToggleLeft, CheckCircle2 } from 'lucide-react';
import { useMemo, useState } from 'react';

const typeLabel = { 
    opcion_multiple: 'Opción múltiple', 
    verdadero_falso: 'Verdadero / falso', 
    respuesta_corta: 'Respuesta corta' 
};

export default function Index({ preguntas, opciones, filtros = {}, permisos }) {
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '', 
        id_mat: filtros.id_mat || '',
        id_area: filtros.id_area || '', 
        id_tem: filtros.id_tem || '',
        dificultad_preg: filtros.dificultad_preg || '', 
        tipo_preg: filtros.tipo_preg || '', 
        estado_preg: filtros.estado_preg || '',
    });

    const [formModal, setFormModal] = useState({ open: false, pregunta: null });
    const [detailModal, setDetailModal] = useState({ open: false, pregunta: null });
    const [statusModal, setStatusModal] = useState({ open: false, pregunta: null });
    const [changingStatus, setChangingStatus] = useState(false);

    const areas = useMemo(() =>
        opciones.areas.filter((area) => !filters.id_mat || String(area.id_mat) === String(filters.id_mat)),
        [filters.id_mat, opciones.areas]
    );

    const temas = useMemo(() => 
        opciones.temas.filter((tema) => !filters.id_area || String(tema.id_area) === String(filters.id_area)), 
        [filters.id_area, opciones.temas]
    );

    const submit = (event) => { 
        event.preventDefault(); 
        router.get(route('preguntas.index'), filters, { preserveState: true, replace: true }); 
    };

    const reset = () => { 
        setFilters({ buscar: '', id_mat: '', id_area: '', id_tem: '', dificultad_preg: '', tipo_preg: '', estado_preg: '' });
        router.get(route('preguntas.index')); 
    };

    const handleOpenStatusModal = (pregunta) => {
        setStatusModal({ open: true, pregunta });
    };

    const changeStatus = () => {
        if (!statusModal.pregunta) return;
        setChangingStatus(true);
        router.patch(
            route('preguntas.cambiar-estado', statusModal.pregunta.id_preg), 
            {}, 
            { 
                preserveScroll: true,
                onSuccess: () => setStatusModal({ open: false, pregunta: null }),
                onFinish: () => setChangingStatus(false),
            }
        );
    };

    return (
        <AdminLayout title="Banco de Preguntas" subtitle="Repositorio académico multiasignatura para la preparación preuniversitaria en Ingeniería.">
            <Head title="Banco de preguntas" />
            <div className="mb-5 flex items-center justify-between">
                <p className="text-sm text-slate-500">{preguntas.total} preguntas registradas</p>
                {permisos.crear && (
                    <Button 
                        type="button" 
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-secondary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-secondary/90 active:scale-95"
                        onClick={() => setFormModal({ open: true, pregunta: null })}
                    >
                        <Plus className="h-4 w-4 text-white" />Nueva pregunta
                    </Button>
                )}
            </div>
                    <Card className="mb-5 border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                <CardContent className="p-4">
                    <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 w-full">
                        <div className="relative col-span-1 md:col-span-2 xl:col-span-2">
                            <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                            <Input className="pl-9 bg-white dark:bg-slate-950 dark:border-slate-800 text-slate-900 dark:text-slate-100 w-full h-10" value={filters.buscar} onChange={(e) => setFilters({ ...filters, buscar: e.target.value })} placeholder="Buscar por contenido del enunciado" />
                        </div>
                        <select className="h-10 w-full rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-3" value={filters.id_mat} onChange={(e) => setFilters({ ...filters, id_mat: e.target.value, id_area: '', id_tem: '' })}>
                            <option value="">Todas las materias</option>
                            {opciones.materias.map((materia) => <option key={materia.id_mat} value={materia.id_mat}>{materia.codigo_mat} · {materia.nombre_mat}</option>)}
                        </select>
                        <select className="h-10 w-full rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-3" value={filters.id_area} onChange={(e) => setFilters({ ...filters, id_area: e.target.value, id_tem: '' })}>
                            <option value="">Todas las áreas</option>
                            {areas.map((area) => <option key={area.id_area} value={area.id_area}>{area.nombre_area}</option>)}
                        </select>
                        <select className="h-10 w-full rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-3" value={filters.id_tem} onChange={(e) => setFilters({ ...filters, id_tem: e.target.value })}>
                            <option value="">Todos los temas</option>
                            {temas.map((tema) => <option key={tema.id_tem} value={tema.id_tem}>{tema.nombre_tem}</option>)}
                        </select>
                        <select className="h-10 w-full rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-3" value={filters.dificultad_preg} onChange={(e) => setFilters({ ...filters, dificultad_preg: e.target.value })}>
                            <option value="">Toda dificultad</option>
                            <option value="basica">Básica</option>
                            <option value="media">Media</option>
                            <option value="avanzada">Avanzada</option>
                        </select>
                        <select className="h-10 w-full rounded-lg border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-3" value={filters.tipo_preg} onChange={(e) => setFilters({ ...filters, tipo_preg: e.target.value })}>
                            <option value="">Todos los tipos</option>
                            {Object.entries(typeLabel).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                        </select>
                        <select className="h-10 w-full rounded-lg border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 text-sm px-3" value={filters.estado_preg} onChange={(e) => setFilters({ ...filters, estado_preg: e.target.value })}>
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <div className="flex gap-2 w-full col-span-1 md:col-span-1 xl:col-span-2">
                            <Button className="inline-flex items-center justify-center rounded-xl bg-brand-secondary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-secondary/90 active:scale-95 flex-1 h-10">Filtrar</Button>
                            <Button type="button" variant="outline" size="icon" className="h-10 w-10 shrink-0 border-slate-200 dark:border-slate-800 dark:text-slate-200" onClick={reset}><RotateCcw className="h-4 w-4" /></Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card className="border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-slate-50/80 dark:bg-slate-900/80">
                                <TableHead className="pl-5">Enunciado</TableHead>
                                <TableHead>Materia</TableHead>
                                <TableHead>Área / Tema</TableHead>
                                <TableHead>Tipo</TableHead>
                                <TableHead>Dificultad</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="pr-5 text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {preguntas.data.map((pregunta) => (
                                <TableRow key={pregunta.id_preg} className="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                    <TableCell className="max-w-[520px] pl-5 pr-4">
                                        <div className="max-w-xl pr-2">
                                            <p 
                                                className="text-sm font-semibold leading-snug text-slate-900 dark:text-slate-100 whitespace-normal break-words"
                                                style={{
                                                    display: '-webkit-box',
                                                    WebkitLineClamp: 3,
                                                    WebkitBoxOrient: 'vertical',
                                                    overflow: 'hidden',
                                                }}
                                                title={pregunta.enunciado_preg}
                                            >
                                                {pregunta.enunciado_preg}
                                            </p>
                                            <p className="mt-1 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                                Código P-{String(pregunta.id_preg).padStart(4, '0')}
                                            </p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge className="bg-brand-primary/10 text-brand-primary hover:bg-brand-primary/15 dark:bg-brand-primary/20 dark:text-slate-200">
                                            {pregunta.tema?.area?.materia?.codigo_mat || 'General'}
                                        </Badge>
                                        <p className="mt-1 text-xs text-slate-500">
                                            {pregunta.tema?.area?.materia?.nombre_mat || 'Sin materia'}
                                        </p>
                                    </TableCell>
                                    <TableCell>
                                        <p className="text-sm font-medium text-slate-800 dark:text-slate-200">{pregunta.tema?.area?.nombre_area || 'Área general'}</p>
                                        <p className="text-xs text-slate-500">{pregunta.tema?.nombre_tem || 'Sin tema'}</p>
                                    </TableCell>
                                    <TableCell><Badge variant="outline">{typeLabel[pregunta.tipo_preg]}</Badge></TableCell>
                                    <TableCell className="capitalize text-slate-700 dark:text-slate-300">{pregunta.dificultad_preg || 'No definida'}</TableCell>
                                    <TableCell><StatusBadge status={pregunta.estado_preg === 'activo' ? 'Activo' : 'Inactivo'} /></TableCell>
                                    <TableCell className="pr-5">
                                        <div className="flex justify-end gap-1">
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="icon"
                                                onClick={() => setDetailModal({ open: true, pregunta })}
                                                aria-label="Ver pregunta"
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            {permisos.editar && (
                                                <Button 
                                                    type="button" 
                                                    variant="ghost" 
                                                    size="icon"
                                                    onClick={() => setFormModal({ open: true, pregunta })}
                                                    aria-label="Editar pregunta"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                            )}
                                            {permisos.cambiarEstado && (
                                                <Button 
                                                    variant="ghost" 
                                                    size="icon" 
                                                    onClick={() => handleOpenStatusModal(pregunta)}
                                                    aria-label="Cambiar estado"
                                                >
                                                    <ToggleLeft className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
            
            <Pagination links={preguntas.links} />

            {/* Modal para Crear y Editar Preguntas */}
            <ModalInstitucional
                open={formModal.open}
                onOpenChange={(open) => setFormModal((current) => ({ ...current, open }))}
                title={formModal.pregunta ? 'Editar pregunta' : 'Nueva pregunta'}
                description={formModal.pregunta ? 'Actualice el enunciado y las alternativas correspondientes.' : 'Incorpore una pregunta de evaluación al banco de reactivos académico.'}
                size="xl"
            >
                {formModal.open && (
                    <PreguntaForm
                        key={formModal.pregunta ? `edit-preg-${formModal.pregunta.id_preg}` : 'create-preg'}
                        pregunta={formModal.pregunta}
                        opciones={opciones}
                        submitRoute={formModal.pregunta ? route('preguntas.update', formModal.pregunta.id_preg) : route('preguntas.store')}
                        method={formModal.pregunta ? 'put' : 'post'}
                        submitLabel={formModal.pregunta ? 'Guardar cambios' : 'Registrar pregunta'}
                        onCancel={() => setFormModal({ open: false, pregunta: null })}
                    />
                )}
            </ModalInstitucional>

            {/* Modal para Ver Detalle de Pregunta */}
            <ModalInstitucional
                open={detailModal.open}
                onOpenChange={(open) => setDetailModal((current) => ({ ...current, open }))}
                title={`Detalle de Pregunta: Código P-${String(detailModal.pregunta?.id_preg || 0).padStart(4, '0')}`}
                description="Revisión del contenido académico y alternativas correctas."
                size="lg"
            >
                {detailModal.pregunta && (
                    <div className="space-y-5">
                        <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                            <CardHeader className="border-b p-5 dark:border-slate-800">
                                <div className="flex flex-wrap items-center gap-2">
                                    <Badge className="bg-brand-primary">
                                        {detailModal.pregunta.tema?.area?.materia?.nombre_mat || 'Materia general'}
                                    </Badge>
                                    <Badge>{detailModal.pregunta.tema?.area?.nombre_area || 'Área general'}</Badge>
                                    <Badge variant="outline">{detailModal.pregunta.tema?.nombre_tem || 'Sin tema'}</Badge>
                                    <Badge variant="outline" className="capitalize">{detailModal.pregunta.dificultad_preg}</Badge>
                                    <Badge variant="outline" className="capitalize">{Number(detailModal.pregunta.puntaje_preg || 0).toFixed(2)} pts</Badge>
                                    <StatusBadge status={detailModal.pregunta.estado_preg === 'activo' ? 'Activo' : 'Inactivo'} />
                                </div>
                            </CardHeader>
                            <CardContent className="p-6">
                                <p className="text-lg font-semibold leading-8 text-slate-900 dark:text-slate-100">
                                    {detailModal.pregunta.enunciado_preg}
                                </p>
                            </CardContent>
                        </Card>

                        <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                            <CardContent className="grid gap-4 p-5 sm:grid-cols-2">
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">Habilidad evaluada</p>
                                    <p className="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200">{detailModal.pregunta.habilidad_preg || 'Clasificación pendiente'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">Referencia de exigencia</p>
                                    <p className="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200">{detailModal.pregunta.exigencia_preg || 'Contenido común'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">Subtema</p>
                                    <p className="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200">{detailModal.pregunta.subtema_preg || 'No especificado'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">Tiempo estimado</p>
                                    <p className="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200">
                                        {detailModal.pregunta.tiempo_estimado_seg_preg ? `${detailModal.pregunta.tiempo_estimado_seg_preg} segundos` : 'No especificado'}
                                    </p>
                                </div>
                                {detailModal.pregunta.relacion_ingenieria_preg && (
                                    <div className="sm:col-span-2">
                                        <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">Relación con Ingeniería</p>
                                        <p className="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-300">{detailModal.pregunta.relacion_ingenieria_preg}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {detailModal.pregunta.alternativas && detailModal.pregunta.alternativas.length > 0 && (
                            <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/50 dark:ring-slate-800">
                                <CardHeader className="border-b p-5 dark:border-slate-800">
                                    <CardTitle className="text-lg font-bold text-slate-900 dark:text-slate-100">Alternativas</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3 p-5">
                                    {detailModal.pregunta.alternativas.map((alt) => (
                                        <div
                                            key={alt.id_alt || alt.letra_alt}
                                            className={`flex items-center gap-3 rounded-xl border p-4 ${
                                                alt.es_correcta_alt
                                                    ? 'border-emerald-300 bg-emerald-50 dark:bg-emerald-950/30 dark:border-emerald-800'
                                                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900'
                                            }`}
                                        >
                                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-white dark:bg-slate-950 font-bold text-brand-primary dark:text-brand-secondary ring-1 ring-slate-200 dark:ring-slate-800">
                                                {alt.letra_alt}
                                            </span>
                                            <p className="flex-1 text-sm text-slate-800 dark:text-slate-200">{alt.texto_alt}</p>
                                            {alt.es_correcta_alt && <CheckCircle2 className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />}
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        )}

                        {detailModal.pregunta.explicacion_preg && (
                            <Card className="border-brand-secondary/30 bg-brand-secondary/5 dark:bg-brand-secondary/10 dark:border-brand-secondary/40 py-0">
                                <CardContent className="p-5">
                                    <p className="mb-2 text-sm font-semibold text-brand-primary dark:text-slate-200">Fundamento de la respuesta</p>
                                    <p className="text-sm leading-6 text-slate-700 dark:text-slate-300">{detailModal.pregunta.explicacion_preg}</p>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                )}
            </ModalInstitucional>

            {/* Modal para Confirmar Acción de Cambio de Estado */}
            <ConfirmModal
                open={statusModal.open}
                onOpenChange={(open) => setStatusModal((current) => ({ ...current, open }))}
                title={statusModal.pregunta?.estado_preg === 'activo' ? 'Inactivar pregunta' : 'Activar pregunta'}
                message={
                    statusModal.pregunta 
                        ? `¿Confirma que desea ${statusModal.pregunta.estado_preg === 'activo' ? 'inactivar' : 'activar'} la pregunta Código P-${String(statusModal.pregunta.id_preg).padStart(4, '0')}?` 
                        : ''
                }
                confirmLabel={statusModal.pregunta?.estado_preg === 'activo' ? 'Inactivar' : 'Activar'}
                variant={statusModal.pregunta?.estado_preg === 'activo' ? 'danger' : 'normal'}
                processing={changingStatus}
                onConfirm={changeStatus}
            />
        </AdminLayout>
    );
}
