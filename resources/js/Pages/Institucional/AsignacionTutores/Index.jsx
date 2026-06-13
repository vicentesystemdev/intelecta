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
import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    BookOpenCheck,
    CalendarDays,
    Layers3,
    Link2,
    Pencil,
    Plus,
    UserRoundCheck,
} from 'lucide-react';
import { useMemo, useState } from 'react';

const emptyAssignment = {
    id_tutor: '',
    id_prog: '',
    id_grupo: '',
    materia_referencia_asig: '',
    rol_asig: 'Tutor académico',
    fecha_inicio_asig: '',
    fecha_fin_asig: '',
    estado_asig: 'activo',
    observacion_asig: '',
};

const tutorName = (tutor) =>
    tutor?.nombre_completo ||
    `${tutor?.nombres_tutor || ''} ${tutor?.apellidos_tutor || ''}`.trim();

const formatDate = (value) =>
    value
        ? new Date(`${String(value).slice(0, 10)}T00:00:00`).toLocaleDateString('es-BO')
        : 'Sin fecha definida';

export default function Index({
    asignaciones,
    tutores = [],
    programas = [],
    grupos = [],
    filtros = {},
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        id_tutor: filtros.id_tutor || '',
        id_prog: filtros.id_prog || '',
        id_grupo: filtros.id_grupo || '',
        estado_asig: filtros.estado_asig || '',
    });
    const [modal, setModal] = useState({ open: false, asignacion: null });
    const form = useForm(emptyAssignment);

    const formGroups = useMemo(
        () =>
            form.data.id_prog
                ? grupos.filter(
                      (grupo) => String(grupo.id_prog) === String(form.data.id_prog),
                  )
                : grupos,
        [form.data.id_prog, grupos],
    );

    const filterGroups = useMemo(
        () =>
            filters.id_prog
                ? grupos.filter(
                      (grupo) => String(grupo.id_prog) === String(filters.id_prog),
                  )
                : grupos,
        [filters.id_prog, grupos],
    );

    const openForm = (asignacion = null) => {
        form.clearErrors();
        form.setData(
            asignacion
                ? {
                      ...emptyAssignment,
                      ...asignacion,
                      id_tutor: String(asignacion.id_tutor),
                      id_prog: asignacion.id_prog ? String(asignacion.id_prog) : '',
                      id_grupo: asignacion.id_grupo
                          ? String(asignacion.id_grupo)
                          : '',
                      fecha_inicio_asig:
                          asignacion.fecha_inicio_asig?.slice(0, 10) || '',
                      fecha_fin_asig: asignacion.fecha_fin_asig?.slice(0, 10) || '',
                  }
                : emptyAssignment,
        );
        setModal({ open: true, asignacion });
    };

    const submit = (event) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => setModal({ open: false, asignacion: null }),
        };

        modal.asignacion
            ? form.patch(
                  route(
                      'admin.institucional.asignacion-tutores.update',
                      modal.asignacion.id_asig,
                  ),
                  options,
              )
            : form.post(
                  route('admin.institucional.asignacion-tutores.store'),
                  options,
              );
    };

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(
            route('admin.institucional.asignacion-tutores.index'),
            filters,
            { preserveState: true, replace: true },
        );
    };

    return (
        <AdminLayout
            title="Asignación de Tutores"
            subtitle="Vinculación de responsables académicos con programas y grupos institucionales."
            wide
        >
            <Head title="Asignación de Tutores" />
            <InstitutionalBanner
                eyebrow="Seguimiento tutorial"
                title="Asignación de Tutores"
                description="Organice responsabilidades tutoriales por ciclo de nivelación, paralelo y materia de referencia."
                icon={Link2}
                action={
                    <button className={primaryButtonClass} onClick={() => openForm()}>
                        <Plus className="h-4 w-4" />
                        Nueva asignación
                    </button>
                }
            />
            <FlashMessage message={flash?.success} />

            <form
                onSubmit={applyFilters}
                className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-5`}
            >
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.id_tutor}
                    onChange={(event) =>
                        setFilters({ ...filters, id_tutor: event.target.value })
                    }
                >
                    <option value="">Todos los tutores</option>
                    {tutores.map((tutor) => (
                        <option key={tutor.id_tutor} value={tutor.id_tutor}>
                            {tutorName(tutor)}
                        </option>
                    ))}
                </select>
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
                    {filterGroups.map((grupo) => (
                        <option key={grupo.id_grupo} value={grupo.id_grupo}>
                            {grupo.nombre_grupo}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.estado_asig}
                    onChange={(event) =>
                        setFilters({ ...filters, estado_asig: event.target.value })
                    }
                >
                    <option value="">Todos los estados</option>
                    <option value="activo">Activas</option>
                    <option value="inactivo">Inactivas</option>
                </select>
                <button className={primaryButtonClass}>Filtrar</button>
            </form>

            {asignaciones.data.length ? (
                <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    {asignaciones.data.map((asignacion) => (
                        <article
                            key={asignacion.id_asig}
                            className={`${cardClass} p-5 sm:p-6`}
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="min-w-0">
                                    <p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary">
                                        {asignacion.rol_asig || 'Tutor académico'}
                                    </p>
                                    <h2 className="mt-2 break-words text-lg font-black text-text-main">
                                        {tutorName(asignacion.tutor)}
                                    </h2>
                                    <p className="mt-1 text-xs text-text-muted">
                                        {asignacion.tutor?.especialidad_tutor ||
                                            'Acompañamiento institucional'}
                                    </p>
                                </div>
                                <InstitutionalStatus status={asignacion.estado_asig} />
                            </div>

                            <div className="mt-5 space-y-3">
                                <div className="flex gap-3 rounded-xl bg-brand-bg p-3">
                                    <BookOpenCheck className="mt-0.5 h-4 w-4 shrink-0 text-brand-secondary" />
                                    <div className="min-w-0">
                                        <p className="text-[10px] font-bold uppercase tracking-wider text-text-muted">
                                            Programa
                                        </p>
                                        <p className="mt-1 break-words text-sm font-bold text-text-main">
                                            {asignacion.programa?.nombre_prog ||
                                                'Cobertura definida por grupo'}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex gap-3 rounded-xl bg-brand-bg p-3">
                                    <Layers3 className="mt-0.5 h-4 w-4 shrink-0 text-brand-secondary" />
                                    <div className="min-w-0">
                                        <p className="text-[10px] font-bold uppercase tracking-wider text-text-muted">
                                            Grupo / paralelo
                                        </p>
                                        <p className="mt-1 break-words text-sm font-bold text-text-main">
                                            {asignacion.grupo?.nombre_grupo ||
                                                'Todos los grupos del programa'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-4 flex flex-wrap gap-2 text-xs text-text-muted">
                                {asignacion.materia_referencia_asig && (
                                    <span className="rounded-lg border border-brand-border px-2.5 py-1.5">
                                        {asignacion.materia_referencia_asig}
                                    </span>
                                )}
                                <span className="flex items-center gap-1.5 rounded-lg border border-brand-border px-2.5 py-1.5">
                                    <CalendarDays className="h-3.5 w-3.5" />
                                    {formatDate(asignacion.fecha_inicio_asig)}
                                </span>
                            </div>

                            <div className="mt-5 flex items-center justify-between border-t border-brand-border pt-4">
                                <span className="text-xs text-text-muted">
                                    {asignacion.fecha_fin_asig
                                        ? `Hasta ${formatDate(asignacion.fecha_fin_asig)}`
                                        : 'Sin fecha de cierre'}
                                </span>
                                <button
                                    className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30"
                                    onClick={() => openForm(asignacion)}
                                    aria-label="Editar asignación"
                                >
                                    <Pencil className="h-4 w-4" />
                                </button>
                            </div>
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyInstitutional
                    title="No hay asignaciones para los filtros seleccionados"
                    description="Vincule un tutor activo con un programa académico, un grupo o ambos."
                />
            )}
            <Pagination links={asignaciones.links} />

            <ModalInstitucional
                open={modal.open}
                onOpenChange={(open) => setModal((current) => ({ ...current, open }))}
                title={
                    modal.asignacion
                        ? 'Editar asignación tutorial'
                        : 'Nueva asignación tutorial'
                }
                description="Debe seleccionar al menos un programa o un grupo. Si indica ambos, el grupo debe pertenecer al programa."
                size="lg"
            >
                <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                    <SelectField
                        label="Tutor académico"
                        value={form.data.id_tutor}
                        onChange={(event) => form.setData('id_tutor', event.target.value)}
                        error={form.errors.id_tutor}
                        className="sm:col-span-2"
                    >
                        <option value="">Seleccione un tutor</option>
                        {tutores.map((tutor) => (
                            <option key={tutor.id_tutor} value={tutor.id_tutor}>
                                {tutorName(tutor)}
                                {tutor.especialidad_tutor
                                    ? ` · ${tutor.especialidad_tutor}`
                                    : ''}
                            </option>
                        ))}
                    </SelectField>
                    <SelectField
                        label="Programa académico"
                        value={form.data.id_prog}
                        onChange={(event) => {
                            form.setData({
                                ...form.data,
                                id_prog: event.target.value,
                                id_grupo: '',
                            });
                        }}
                        error={form.errors.id_prog}
                    >
                        <option value="">Sin programa específico</option>
                        {programas.map((programa) => (
                            <option key={programa.id_prog} value={programa.id_prog}>
                                {programa.nombre_prog}
                            </option>
                        ))}
                    </SelectField>
                    <SelectField
                        label="Grupo / paralelo"
                        value={form.data.id_grupo}
                        onChange={(event) => {
                            const group = grupos.find(
                                (item) => String(item.id_grupo) === event.target.value,
                            );
                            form.setData({
                                ...form.data,
                                id_grupo: event.target.value,
                                id_prog:
                                    form.data.id_prog ||
                                    (group ? String(group.id_prog) : ''),
                            });
                        }}
                        error={form.errors.id_grupo}
                    >
                        <option value="">Sin grupo específico</option>
                        {formGroups.map((grupo) => (
                            <option key={grupo.id_grupo} value={grupo.id_grupo}>
                                {grupo.nombre_grupo}
                            </option>
                        ))}
                    </SelectField>
                    <Field
                        label="Materia de referencia"
                        value={form.data.materia_referencia_asig}
                        onChange={(event) =>
                            form.setData(
                                'materia_referencia_asig',
                                event.target.value,
                            )
                        }
                        error={form.errors.materia_referencia_asig}
                    />
                    <Field
                        label="Rol tutorial"
                        value={form.data.rol_asig}
                        onChange={(event) =>
                            form.setData('rol_asig', event.target.value)
                        }
                        error={form.errors.rol_asig}
                    />
                    <Field
                        type="date"
                        label="Fecha de inicio"
                        value={form.data.fecha_inicio_asig}
                        onChange={(event) =>
                            form.setData('fecha_inicio_asig', event.target.value)
                        }
                        error={form.errors.fecha_inicio_asig}
                    />
                    <Field
                        type="date"
                        label="Fecha de finalización"
                        value={form.data.fecha_fin_asig}
                        onChange={(event) =>
                            form.setData('fecha_fin_asig', event.target.value)
                        }
                        error={form.errors.fecha_fin_asig}
                    />
                    <SelectField
                        label="Estado"
                        value={form.data.estado_asig}
                        onChange={(event) =>
                            form.setData('estado_asig', event.target.value)
                        }
                        error={form.errors.estado_asig}
                    >
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </SelectField>
                    <TextareaField
                        label="Observación"
                        value={form.data.observacion_asig}
                        onChange={(event) =>
                            form.setData('observacion_asig', event.target.value)
                        }
                        error={form.errors.observacion_asig}
                        className="sm:col-span-2"
                    />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button
                            type="button"
                            className={secondaryButtonClass}
                            onClick={() =>
                                setModal({ open: false, asignacion: null })
                            }
                        >
                            Cancelar
                        </button>
                        <button className={primaryButtonClass} disabled={form.processing}>
                            <UserRoundCheck className="h-4 w-4" />
                            {modal.asignacion
                                ? 'Guardar cambios'
                                : 'Registrar asignación'}
                        </button>
                    </div>
                </form>
            </ModalInstitucional>
        </AdminLayout>
    );
}
