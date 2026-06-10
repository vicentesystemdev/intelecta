import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import {
    EmptyInstitutional,
    Field,
    FlashMessage,
    InstitutionalBanner,
    InstitutionalStatus,
    SelectField,
    TextareaField,
    cardClass,
    primaryButtonClass,
    secondaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { CalendarDays, Eye, FileCheck2, Pencil, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';

const emptySimulation = {
    id_prog: '',
    id_grupo: '',
    id_plantilla: '',
    titulo_sim: '',
    fecha_sim: '',
    hora_inicio_sim: '',
    hora_fin_sim: '',
    modalidad_sim: '',
    estado_sim: 'programado',
    observacion_sim: '',
};

export default function Index({
    simulacros,
    programas = [],
    grupos = [],
    plantillas = [],
    filtros = {},
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        id_prog: filtros.id_prog || '',
        id_grupo: filtros.id_grupo || '',
        estado_sim: filtros.estado_sim || '',
        fecha_desde: filtros.fecha_desde || '',
        fecha_hasta: filtros.fecha_hasta || '',
    });
    const [formModal, setFormModal] = useState({ open: false, simulacro: null });
    const [detail, setDetail] = useState(null);
    const form = useForm(emptySimulation);
    const formGroups = useMemo(
        () =>
            form.data.id_prog
                ? grupos.filter((grupo) => String(grupo.id_prog) === String(form.data.id_prog))
                : grupos,
        [form.data.id_prog, grupos],
    );
    const filterGroups = useMemo(
        () =>
            filters.id_prog
                ? grupos.filter((grupo) => String(grupo.id_prog) === String(filters.id_prog))
                : grupos,
        [filters.id_prog, grupos],
    );

    const openForm = (simulacro = null) => {
        form.clearErrors();
        form.setData(
            simulacro
                ? {
                      id_prog: String(simulacro.id_prog),
                      id_grupo: simulacro.id_grupo ? String(simulacro.id_grupo) : '',
                      id_plantilla: simulacro.id_plantilla
                          ? String(simulacro.id_plantilla)
                          : '',
                      titulo_sim: simulacro.titulo_sim,
                      fecha_sim: simulacro.fecha_sim?.slice(0, 10) || '',
                      hora_inicio_sim: simulacro.hora_inicio_sim?.slice(0, 5) || '',
                      hora_fin_sim: simulacro.hora_fin_sim?.slice(0, 5) || '',
                      modalidad_sim: simulacro.modalidad_sim || '',
                      estado_sim: simulacro.estado_sim,
                      observacion_sim: simulacro.observacion_sim || '',
                  }
                : emptySimulation,
        );
        setFormModal({ open: true, simulacro });
    };

    const submit = (event) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => setFormModal({ open: false, simulacro: null }),
        };
        formModal.simulacro
            ? form.put(
                  route(
                      'admin.institucional.simulacros.update',
                      formModal.simulacro.id_sim,
                  ),
                  options,
              )
            : form.post(route('admin.institucional.simulacros.store'), options);
    };

    const filter = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.simulacros.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AdminLayout
            title="Calendario de Simulacros"
            subtitle="Programación institucional de evaluaciones por programa y grupo."
            wide
        >
            <Head title="Calendario de Simulacros" />
            <InstitutionalBanner
                eyebrow="Gestión institucional"
                title="Calendario de Simulacros"
                description="Coordine fechas, horarios, modalidad y plantilla académica para cada simulacro programado."
                icon={CalendarDays}
                action={
                    <button className={primaryButtonClass} onClick={() => openForm()}>
                        <Plus className="h-4 w-4" />
                        Programar simulacro
                    </button>
                }
            />
            <FlashMessage message={flash?.success} />
            <form
                onSubmit={filter}
                className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-3 xl:grid-cols-6`}
            >
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.id_prog}
                    onChange={(e) =>
                        setFilters({ ...filters, id_prog: e.target.value, id_grupo: '' })
                    }
                >
                    <option value="">Todos los programas</option>
                    {programas.map((programa) => (
                        <option key={programa.id_prog} value={programa.id_prog}>
                            {programa.nombre_prog}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.id_grupo}
                    onChange={(e) => setFilters({ ...filters, id_grupo: e.target.value })}
                >
                    <option value="">Todos los grupos</option>
                    {filterGroups.map((grupo) => (
                        <option key={grupo.id_grupo} value={grupo.id_grupo}>
                            {grupo.codigo_grupo || grupo.nombre_grupo}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.estado_sim}
                    onChange={(e) =>
                        setFilters({ ...filters, estado_sim: e.target.value })
                    }
                >
                    <option value="">Todos los estados</option>
                    <option value="programado">Programado</option>
                    <option value="en preparación">En preparación</option>
                    <option value="aplicado">Aplicado</option>
                    <option value="cerrado">Cerrado</option>
                </select>
                <input
                    type="date"
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.fecha_desde}
                    onChange={(e) =>
                        setFilters({ ...filters, fecha_desde: e.target.value })
                    }
                />
                <input
                    type="date"
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.fecha_hasta}
                    onChange={(e) =>
                        setFilters({ ...filters, fecha_hasta: e.target.value })
                    }
                />
                <button className={primaryButtonClass}>Filtrar</button>
            </form>

            {simulacros.data.length ? (
                <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    {simulacros.data.map((simulacro) => (
                        <article key={simulacro.id_sim} className={`${cardClass} p-5 sm:p-6`}>
                            <div className="flex items-start justify-between gap-3">
                                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-secondary/10 text-brand-secondary">
                                    <CalendarDays className="h-5 w-5" />
                                </span>
                                <InstitutionalStatus status={simulacro.estado_sim} />
                            </div>
                            <h3 className="mt-5 break-words text-lg font-black leading-snug text-text-main">
                                {simulacro.titulo_sim}
                            </h3>
                            <p className="mt-2 text-xs leading-5 text-text-muted">
                                {simulacro.programa?.nombre_prog}
                            </p>
                            <div className="mt-5 space-y-3 rounded-xl bg-brand-bg p-4 text-xs">
                                <div className="flex items-center justify-between gap-3">
                                    <span className="text-text-muted">Fecha</span>
                                    <span className="font-bold text-text-main">
                                        {simulacro.fecha_sim
                                            ? new Date(
                                                  `${simulacro.fecha_sim.slice(0, 10)}T00:00:00`,
                                              ).toLocaleDateString('es-BO')
                                            : 'Por definir'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <span className="text-text-muted">Horario</span>
                                    <span className="font-bold text-text-main">
                                        {simulacro.hora_inicio_sim?.slice(0, 5) || '—'} a{' '}
                                        {simulacro.hora_fin_sim?.slice(0, 5) || '—'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <span className="text-text-muted">Grupo</span>
                                    <span className="font-bold text-text-main">
                                        {simulacro.grupo?.codigo_grupo || 'Todos los grupos'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <span className="text-text-muted">Modalidad</span>
                                    <span className="font-bold text-text-main">
                                        {simulacro.modalidad_sim || 'Por definir'}
                                    </span>
                                </div>
                            </div>
                            <div className="mt-4 flex items-start gap-2 rounded-xl border border-brand-border p-3">
                                <FileCheck2 className="mt-0.5 h-4 w-4 shrink-0 text-brand-secondary" />
                                <p className="break-words text-xs leading-5 text-text-muted">
                                    {simulacro.plantilla?.nombre_plan ||
                                        'Sin plantilla asociada'}
                                </p>
                            </div>
                            <div className="mt-5 flex justify-end gap-2 border-t border-brand-border pt-4">
                                <button
                                    className="inline-flex items-center gap-2 rounded-xl border border-brand-border px-3 py-2 text-xs font-bold text-text-main hover:bg-brand-border/30"
                                    onClick={() => setDetail(simulacro)}
                                >
                                    <Eye className="h-4 w-4" />
                                    Ver detalle
                                </button>
                                <button
                                    className="inline-flex items-center gap-2 rounded-xl border border-brand-border px-3 py-2 text-xs font-bold text-text-main hover:bg-brand-border/30"
                                    onClick={() => openForm(simulacro)}
                                >
                                    <Pencil className="h-4 w-4" />
                                    Editar
                                </button>
                            </div>
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyInstitutional
                    title="No hay simulacros programados"
                    description="Programe una evaluación y vincúlela opcionalmente con una plantilla existente."
                />
            )}
            <Pagination links={simulacros.links} />

            <ModalInstitucional
                open={formModal.open}
                onOpenChange={(open) =>
                    setFormModal((current) => ({ ...current, open }))
                }
                title={
                    formModal.simulacro ? 'Editar simulacro' : 'Programar simulacro'
                }
                description="Configure el alcance académico, fecha, horario y plantilla."
                size="lg"
            >
                <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                    <SelectField
                        label="Programa académico"
                        value={form.data.id_prog}
                        onChange={(e) =>
                            form.setData({
                                ...form.data,
                                id_prog: e.target.value,
                                id_grupo: '',
                            })
                        }
                        error={form.errors.id_prog}
                    >
                        <option value="">Seleccione un programa</option>
                        {programas.map((programa) => (
                            <option key={programa.id_prog} value={programa.id_prog}>
                                {programa.nombre_prog}
                            </option>
                        ))}
                    </SelectField>
                    <SelectField
                        label="Grupo/paralelo"
                        value={form.data.id_grupo}
                        onChange={(e) => form.setData('id_grupo', e.target.value)}
                        error={form.errors.id_grupo}
                    >
                        <option value="">Todos los grupos</option>
                        {formGroups.map((grupo) => (
                            <option key={grupo.id_grupo} value={grupo.id_grupo}>
                                {grupo.codigo_grupo || grupo.nombre_grupo}
                            </option>
                        ))}
                    </SelectField>
                    <Field
                        label="Título"
                        value={form.data.titulo_sim}
                        onChange={(e) => form.setData('titulo_sim', e.target.value)}
                        error={form.errors.titulo_sim}
                        className="sm:col-span-2"
                    />
                    <SelectField
                        label="Plantilla de evaluación"
                        value={form.data.id_plantilla}
                        onChange={(e) => form.setData('id_plantilla', e.target.value)}
                        error={form.errors.id_plantilla}
                        className="sm:col-span-2"
                    >
                        <option value="">Sin plantilla asociada</option>
                        {plantillas.map((plantilla) => (
                            <option key={plantilla.id_plan} value={plantilla.id_plan}>
                                {plantilla.nombre_plan}
                            </option>
                        ))}
                    </SelectField>
                    <Field type="date" label="Fecha" value={form.data.fecha_sim} onChange={(e) => form.setData('fecha_sim', e.target.value)} error={form.errors.fecha_sim} />
                    <Field label="Modalidad" value={form.data.modalidad_sim} onChange={(e) => form.setData('modalidad_sim', e.target.value)} error={form.errors.modalidad_sim} />
                    <Field type="time" label="Hora de inicio" value={form.data.hora_inicio_sim} onChange={(e) => form.setData('hora_inicio_sim', e.target.value)} error={form.errors.hora_inicio_sim} />
                    <Field type="time" label="Hora de finalización" value={form.data.hora_fin_sim} onChange={(e) => form.setData('hora_fin_sim', e.target.value)} error={form.errors.hora_fin_sim} />
                    <SelectField label="Estado" value={form.data.estado_sim} onChange={(e) => form.setData('estado_sim', e.target.value)} error={form.errors.estado_sim}>
                        <option value="programado">Programado</option>
                        <option value="en preparación">En preparación</option>
                        <option value="aplicado">Aplicado</option>
                        <option value="cerrado">Cerrado</option>
                    </SelectField>
                    <TextareaField label="Observación" value={form.data.observacion_sim} onChange={(e) => form.setData('observacion_sim', e.target.value)} error={form.errors.observacion_sim} className="sm:col-span-2" />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button type="button" className={secondaryButtonClass} onClick={() => setFormModal({ open: false, simulacro: null })}>Cancelar</button>
                        <button className={primaryButtonClass} disabled={form.processing}>{formModal.simulacro ? 'Guardar cambios' : 'Programar simulacro'}</button>
                    </div>
                </form>
            </ModalInstitucional>
            <ModalInstitucional
                open={Boolean(detail)}
                onOpenChange={(open) => !open && setDetail(null)}
                title="Detalle del simulacro programado"
                description="Información académica y operativa de la evaluación."
                size="md"
            >
                {detail && (
                    <div className="space-y-5">
                        <div className="rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary p-5 text-white">
                            <InstitutionalStatus status={detail.estado_sim} />
                            <h3 className="mt-4 text-xl font-black leading-snug">
                                {detail.titulo_sim}
                            </h3>
                            <p className="mt-2 text-sm text-slate-200">
                                {detail.programa?.nombre_prog}
                            </p>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2">
                            {[
                                ['Grupo/paralelo', detail.grupo?.codigo_grupo || 'Todos los grupos'],
                                ['Modalidad', detail.modalidad_sim],
                                ['Fecha', detail.fecha_sim ? new Date(`${detail.fecha_sim.slice(0, 10)}T00:00:00`).toLocaleDateString('es-BO') : null],
                                ['Horario', `${detail.hora_inicio_sim?.slice(0, 5) || '—'} a ${detail.hora_fin_sim?.slice(0, 5) || '—'}`],
                                ['Plantilla', detail.plantilla?.nombre_plan],
                                ['Observación', detail.observacion_sim],
                            ].map(([label, value]) => (
                                <div
                                    key={label}
                                    className="rounded-xl border border-brand-border p-4"
                                >
                                    <p className="text-xs text-text-muted">{label}</p>
                                    <p className="mt-1 break-words text-sm font-bold text-text-main">
                                        {value || 'No registrado'}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </ModalInstitucional>
        </AdminLayout>
    );
}
