import {
    EmptyInstitutional,
    Field,
    FlashMessage,
    InstitutionalBanner,
    InstitutionalStatus,
    MetricTile,
    SelectField,
    TextareaField,
    cardClass,
    primaryButtonClass,
    secondaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    BookOpenCheck,
    ClipboardCheck,
    Clock3,
    Eye,
    FileChartColumn,
    Pencil,
    Search,
    ShieldAlert,
    ShieldCheck,
} from 'lucide-react';
import { useMemo, useState } from 'react';

const emptyHabilitacion = {
    estado_hab: 'habilitado',
    motivo_hab: '',
    fecha_inicio_hab: '',
    fecha_fin_hab: '',
    habilitado_evaluaciones_hab: true,
    habilitado_simulacros_hab: true,
    habilitado_reportes_hab: true,
    observacion_hab: '',
};

const studentName = (postulante) =>
    `${postulante?.nombres_post || ''} ${postulante?.apellidos_post || ''}`.trim();

function AccessFlag({ enabled, icon: Icon, children }) {
    return (
        <span
            className={`inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-bold ${
                enabled
                    ? 'border-brand-success/25 bg-brand-success/10 text-brand-success'
                    : 'border-brand-border bg-brand-border/25 text-text-muted'
            }`}
        >
            <Icon className="h-3.5 w-3.5" />
            {children}
        </span>
    );
}

function AccessToggle({ label, description, checked, onChange }) {
    return (
        <label className="flex cursor-pointer items-start justify-between gap-4 rounded-2xl border border-brand-border p-4">
            <span>
                <span className="block text-sm font-bold text-text-main">{label}</span>
                <span className="mt-1 block text-xs leading-5 text-text-muted">
                    {description}
                </span>
            </span>
            <input
                type="checkbox"
                checked={checked}
                onChange={(event) => onChange(event.target.checked)}
                className="mt-1 h-5 w-5 rounded border-brand-border text-brand-secondary focus:ring-brand-secondary"
            />
        </label>
    );
}

export default function Index({
    habilitaciones,
    metricas = {},
    programas = [],
    grupos = [],
    filtros = {},
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
        id_prog: filtros.id_prog || '',
        id_grupo: filtros.id_grupo || '',
        estado_hab: filtros.estado_hab || '',
    });
    const [modal, setModal] = useState({ open: false, habilitacion: null });
    const form = useForm(emptyHabilitacion);

    const filteredGroups = useMemo(
        () =>
            filters.id_prog
                ? grupos.filter(
                      (grupo) => String(grupo.id_prog) === String(filters.id_prog),
                  )
                : grupos,
        [filters.id_prog, grupos],
    );

    const openForm = (habilitacion) => {
        form.clearErrors();
        form.setData({
            ...emptyHabilitacion,
            ...habilitacion,
            fecha_inicio_hab: habilitacion.fecha_inicio_hab?.slice(0, 10) || '',
            fecha_fin_hab: habilitacion.fecha_fin_hab?.slice(0, 10) || '',
            habilitado_evaluaciones_hab: Boolean(
                habilitacion.habilitado_evaluaciones_hab,
            ),
            habilitado_simulacros_hab: Boolean(
                habilitacion.habilitado_simulacros_hab,
            ),
            habilitado_reportes_hab: Boolean(
                habilitacion.habilitado_reportes_hab,
            ),
        });
        setModal({ open: true, habilitacion });
    };

    const submit = (event) => {
        event.preventDefault();
        form.patch(
            route(
                'admin.institucional.habilitacion.update',
                modal.habilitacion.id_hab,
            ),
            {
                preserveScroll: true,
                onSuccess: () => setModal({ open: false, habilitacion: null }),
            },
        );
    };

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.habilitacion.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const summary = [
        ['Habilitados', metricas.habilitados ?? 0, ShieldCheck, 'success'],
        ['Observados', metricas.observados ?? 0, Eye, 'warning'],
        ['Restringidos', metricas.restringidos ?? 0, ShieldAlert, 'danger'],
        ['Temporales', metricas.temporales ?? 0, Clock3, 'info'],
    ];

    return (
        <AdminLayout
            title="Habilitación Académica"
            subtitle="Control institucional de acceso a evaluaciones, simulacros y reportes académicos."
            wide
        >
            <Head title="Habilitación Académica" />
            <InstitutionalBanner
                eyebrow="Continuidad académica"
                title="Habilitación Académica"
                description="Consolide el estado académico-administrativo de cada postulante y gestione accesos de forma preventiva y no sancionatoria."
                icon={ShieldCheck}
            />
            <FlashMessage message={flash?.success} />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {summary.map(([label, value, icon, tone]) => (
                    <MetricTile
                        key={label}
                        label={label}
                        value={value}
                        icon={icon}
                        tone={tone}
                    />
                ))}
            </div>

            <form
                onSubmit={applyFilters}
                className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-5`}
            >
                <label className="relative">
                    <Search className="pointer-events-none absolute left-3 top-3 h-4 w-4 text-text-muted" />
                    <input
                        className="h-10 w-full rounded-xl border border-brand-border bg-brand-card pl-9 pr-3 text-sm text-text-main"
                        placeholder="Buscar postulante"
                        value={filters.buscar}
                        onChange={(event) =>
                            setFilters({ ...filters, buscar: event.target.value })
                        }
                    />
                </label>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.id_prog}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            id_prog: event.target.value,
                            id_grupo: '',
                        })
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
                    onChange={(event) =>
                        setFilters({ ...filters, id_grupo: event.target.value })
                    }
                >
                    <option value="">Todos los grupos</option>
                    {filteredGroups.map((grupo) => (
                        <option key={grupo.id_grupo} value={grupo.id_grupo}>
                            {grupo.nombre_grupo}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.estado_hab}
                    onChange={(event) =>
                        setFilters({ ...filters, estado_hab: event.target.value })
                    }
                >
                    <option value="">Todos los estados</option>
                    {['habilitado', 'observado', 'restringido', 'temporal'].map(
                        (estado) => (
                            <option key={estado} value={estado}>
                                {estado}
                            </option>
                        ),
                    )}
                </select>
                <button className={primaryButtonClass}>Filtrar</button>
            </form>

            {habilitaciones.data.length ? (
                <div className="grid gap-5 lg:grid-cols-2">
                    {habilitaciones.data.map((habilitacion) => (
                        <article
                            key={habilitacion.id_hab}
                            className={`${cardClass} p-5 sm:p-6`}
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="min-w-0">
                                    <p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary">
                                        {habilitacion.matricula?.codigo_mat ||
                                            'Control académico'}
                                    </p>
                                    <h2 className="mt-2 break-words text-lg font-black text-text-main">
                                        {studentName(habilitacion.postulante)}
                                    </h2>
                                    <p className="mt-1 text-xs leading-5 text-text-muted">
                                        {habilitacion.inscripcion?.programa?.nombre_prog}
                                        {habilitacion.inscripcion?.grupo
                                            ? ` · ${habilitacion.inscripcion.grupo.nombre_grupo}`
                                            : ''}
                                    </p>
                                </div>
                                <InstitutionalStatus status={habilitacion.estado_hab} />
                            </div>

                            <div className="mt-5 flex flex-wrap gap-2">
                                <AccessFlag
                                    enabled={habilitacion.habilitado_evaluaciones_hab}
                                    icon={ClipboardCheck}
                                >
                                    Evaluaciones
                                </AccessFlag>
                                <AccessFlag
                                    enabled={habilitacion.habilitado_simulacros_hab}
                                    icon={BookOpenCheck}
                                >
                                    Simulacros
                                </AccessFlag>
                                <AccessFlag
                                    enabled={habilitacion.habilitado_reportes_hab}
                                    icon={FileChartColumn}
                                >
                                    Reportes
                                </AccessFlag>
                            </div>

                            <div className="mt-5 rounded-2xl bg-brand-bg p-4">
                                <p className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                    Motivo institucional
                                </p>
                                <p className="mt-2 text-sm leading-6 text-text-main">
                                    {habilitacion.motivo_hab ||
                                        'Sin observaciones administrativas vigentes.'}
                                </p>
                            </div>

                            <div className="mt-5 flex items-center justify-between border-t border-brand-border pt-4">
                                <span className="text-xs text-text-muted">
                                    Matrícula:{' '}
                                    {habilitacion.matricula?.estado_matricula_mat ||
                                        'Sin registro'}
                                </span>
                                <button
                                    className={secondaryButtonClass}
                                    onClick={() => openForm(habilitacion)}
                                >
                                    <Pencil className="h-4 w-4" />
                                    Editar acceso
                                </button>
                            </div>
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyInstitutional
                    title="No hay habilitaciones para los filtros seleccionados"
                    description="Las habilitaciones se generan al registrar matrículas académicas."
                />
            )}
            <Pagination links={habilitaciones.links} />

            <ModalInstitucional
                open={modal.open}
                onOpenChange={(open) => setModal((current) => ({ ...current, open }))}
                title="Actualizar habilitación académica"
                description={
                    modal.habilitacion
                        ? studentName(modal.habilitacion.postulante)
                        : 'Control de acceso académico'
                }
                size="lg"
            >
                <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                    <SelectField
                        label="Estado de habilitación"
                        value={form.data.estado_hab}
                        onChange={(event) =>
                            form.setData('estado_hab', event.target.value)
                        }
                        error={form.errors.estado_hab}
                    >
                        {['habilitado', 'observado', 'restringido', 'temporal'].map(
                            (estado) => (
                                <option key={estado} value={estado}>
                                    {estado}
                                </option>
                            ),
                        )}
                    </SelectField>
                    <Field
                        label="Motivo"
                        value={form.data.motivo_hab}
                        onChange={(event) =>
                            form.setData('motivo_hab', event.target.value)
                        }
                        error={form.errors.motivo_hab}
                    />
                    <Field
                        type="date"
                        label="Fecha de inicio"
                        value={form.data.fecha_inicio_hab}
                        onChange={(event) =>
                            form.setData('fecha_inicio_hab', event.target.value)
                        }
                        error={form.errors.fecha_inicio_hab}
                    />
                    <Field
                        type="date"
                        label="Fecha de finalización"
                        min={form.data.fecha_inicio_hab || undefined}
                        value={form.data.fecha_fin_hab}
                        onChange={(event) =>
                            form.setData('fecha_fin_hab', event.target.value)
                        }
                        error={form.errors.fecha_fin_hab}
                    />
                    <div className="space-y-3 sm:col-span-2">
                        <AccessToggle
                            label="Acceso a evaluaciones"
                            description="Permite iniciar instrumentos académicos disponibles."
                            checked={form.data.habilitado_evaluaciones_hab}
                            onChange={(value) =>
                                form.setData('habilitado_evaluaciones_hab', value)
                            }
                        />
                        <AccessToggle
                            label="Acceso a simulacros"
                            description="Permite participar en simulacros programados."
                            checked={form.data.habilitado_simulacros_hab}
                            onChange={(value) =>
                                form.setData('habilitado_simulacros_hab', value)
                            }
                        />
                        <AccessToggle
                            label="Acceso a reportes"
                            description="Permite consultar lecturas y reportes académicos habilitados."
                            checked={form.data.habilitado_reportes_hab}
                            onChange={(value) =>
                                form.setData('habilitado_reportes_hab', value)
                            }
                        />
                    </div>
                    <TextareaField
                        label="Observación administrativa"
                        value={form.data.observacion_hab}
                        onChange={(event) =>
                            form.setData('observacion_hab', event.target.value)
                        }
                        error={form.errors.observacion_hab}
                        className="sm:col-span-2"
                    />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button
                            type="button"
                            className={secondaryButtonClass}
                            onClick={() =>
                                setModal({ open: false, habilitacion: null })
                            }
                        >
                            Cancelar
                        </button>
                        <button className={primaryButtonClass} disabled={form.processing}>
                            <ShieldCheck className="h-4 w-4" />
                            Guardar habilitación
                        </button>
                    </div>
                </form>
            </ModalInstitucional>
        </AdminLayout>
    );
}
