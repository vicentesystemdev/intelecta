import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import {
    EmptyInstitutional,
    FlashMessage,
    InstitutionalBanner,
    InstitutionalStatus,
    SelectField,
    TextareaField,
    Field,
    cardClass,
    primaryButtonClass,
    secondaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowRight, ClipboardList, Pencil, Plus, Search, Users } from 'lucide-react';
import { useMemo, useState } from 'react';

const emptyEnrollment = {
    id_prog: '',
    id_grupo: '',
    id_post: '',
    fecha_inscripcion: new Date().toISOString().slice(0, 10),
    estado_inscripcion: 'activo',
    observacion_inscripcion: '',
};

export default function Index({
    inscripciones,
    programas = [],
    grupos = [],
    postulantes = [],
    filtros = {},
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
        id_prog: filtros.id_prog || '',
        id_grupo: filtros.id_grupo || '',
        estado_inscripcion: filtros.estado_inscripcion || '',
    });
    const [formModal, setFormModal] = useState({ open: false, inscripcion: null });
    const form = useForm(emptyEnrollment);

    const filteredGroups = useMemo(
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

    const openForm = (inscripcion = null) => {
        form.clearErrors();
        form.setData(
            inscripcion
                ? {
                      id_prog: String(inscripcion.id_prog),
                      id_grupo: inscripcion.id_grupo ? String(inscripcion.id_grupo) : '',
                      id_post: String(inscripcion.id_post),
                      fecha_inscripcion: inscripcion.fecha_inscripcion?.slice(0, 10) || '',
                      estado_inscripcion: inscripcion.estado_inscripcion,
                      observacion_inscripcion: inscripcion.observacion_inscripcion || '',
                  }
                : emptyEnrollment,
        );
        setFormModal({ open: true, inscripcion });
    };

    const submit = (event) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => setFormModal({ open: false, inscripcion: null }),
        };
        formModal.inscripcion
            ? form.patch(
                  route('admin.institucional.inscripciones.update', formModal.inscripcion.id_insc),
                  options,
              )
            : form.post(route('admin.institucional.inscripciones.store'), options);
    };

    const submitFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.inscripciones.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const fullName = (postulante) =>
        `${postulante?.nombres_post || ''} ${postulante?.apellidos_post || ''}`.trim();

    return (
        <AdminLayout
            title="Inscripciones Académicas"
            subtitle="Asignación de postulantes a programas y grupos de nivelación."
            wide
        >
            <Head title="Inscripciones Académicas" />
            <InstitutionalBanner
                eyebrow="Gestión institucional"
                title="Inscripciones Académicas"
                description="Vincule postulantes existentes con un programa académico y el grupo/paralelo responsable de su seguimiento."
                icon={ClipboardList}
                action={
                    <button className={primaryButtonClass} onClick={() => openForm()}>
                        <Plus className="h-4 w-4" />
                        Nueva inscripción
                    </button>
                }
            />
            <FlashMessage message={flash?.success} />

            <form
                onSubmit={submitFilters}
                className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-[minmax(220px,1fr)_220px_200px_170px_auto]`}
            >
                <div className="relative">
                    <Search className="absolute left-3 top-3 h-4 w-4 text-text-muted" />
                    <input
                        className="h-10 w-full rounded-xl border border-brand-border bg-brand-card pl-9 pr-3 text-sm text-text-main"
                        placeholder="Buscar postulante"
                        value={filters.buscar}
                        onChange={(event) =>
                            setFilters({ ...filters, buscar: event.target.value })
                        }
                    />
                </div>
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
                            {grupo.codigo_grupo || grupo.nombre_grupo}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.estado_inscripcion}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            estado_inscripcion: event.target.value,
                        })
                    }
                >
                    <option value="">Todos los estados</option>
                    <option value="activo">Activas</option>
                    <option value="inactivo">Inactivas</option>
                </select>
                <button className={primaryButtonClass}>Filtrar</button>
            </form>

            {inscripciones.data.length ? (
                <>
                    <div className="hidden overflow-hidden rounded-2xl border border-brand-border bg-brand-card shadow-sm lg:block">
                        <table className="w-full table-fixed">
                            <thead className="border-b border-brand-border bg-brand-bg">
                                <tr>
                                    {[
                                        ['Postulante', 'w-[25%]'],
                                        ['Programa', 'w-[24%]'],
                                        ['Grupo / tutor', 'w-[20%]'],
                                        ['Inscripción', 'w-[15%]'],
                                        ['Estado', 'w-[9%]'],
                                        ['Acción', 'w-[7%]'],
                                    ].map(([label, width]) => (
                                        <th
                                            key={label}
                                            className={`${width} px-4 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-text-muted`}
                                        >
                                            {label}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-brand-border">
                                {inscripciones.data.map((inscripcion) => (
                                    <tr key={inscripcion.id_insc}>
                                        <td className="px-4 py-4 align-top">
                                            <p className="break-words text-sm font-bold leading-snug text-text-main">
                                                {fullName(inscripcion.postulante)}
                                            </p>
                                            <p className="mt-1 text-xs text-text-muted">
                                                {inscripcion.postulante?.colegio?.nombre_col ||
                                                    'Colegio no registrado'}
                                            </p>
                                        </td>
                                        <td className="px-4 py-4 align-top">
                                            <p className="break-words text-sm font-semibold leading-snug text-text-main">
                                                {inscripcion.programa?.nombre_prog}
                                            </p>
                                            <p className="mt-1 text-xs text-text-muted">
                                                {inscripcion.programa?.codigo_prog}
                                            </p>
                                        </td>
                                        <td className="px-4 py-4 align-top">
                                            <p className="text-sm font-semibold text-text-main">
                                                {inscripcion.grupo?.codigo_grupo ||
                                                    'Sin grupo asignado'}
                                            </p>
                                            <p className="mt-1 break-words text-xs text-text-muted">
                                                {inscripcion.grupo?.tutor_responsable_grupo ||
                                                    'Tutor por asignar'}
                                            </p>
                                        </td>
                                        <td className="px-4 py-4 align-top text-xs text-text-muted">
                                            {inscripcion.fecha_inscripcion
                                                ? new Date(
                                                      `${inscripcion.fecha_inscripcion.slice(0, 10)}T00:00:00`,
                                                  ).toLocaleDateString('es-BO')
                                                : 'Sin fecha'}
                                        </td>
                                        <td className="px-4 py-4 align-top">
                                            <InstitutionalStatus
                                                status={inscripcion.estado_inscripcion}
                                            />
                                        </td>
                                        <td className="px-4 py-4 align-top">
                                            <div className="flex items-center gap-1">
                                                <Link
                                                    href={route(
                                                        'admin.institucional.ficha.postulante',
                                                        inscripcion.id_post,
                                                    )}
                                                    className="rounded-lg p-2 text-brand-secondary hover:bg-brand-secondary/10"
                                                    aria-label="Ver ficha académica"
                                                >
                                                    <ArrowRight className="h-4 w-4" />
                                                </Link>
                                                <button
                                                    className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30"
                                                    onClick={() => openForm(inscripcion)}
                                                    aria-label="Editar inscripción"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2 lg:hidden">
                        {inscripciones.data.map((inscripcion) => (
                            <article
                                key={inscripcion.id_insc}
                                className={`${cardClass} p-5`}
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <h3 className="break-words text-sm font-black text-text-main">
                                            {fullName(inscripcion.postulante)}
                                        </h3>
                                        <p className="mt-1 text-xs text-text-muted">
                                            {inscripcion.programa?.nombre_prog}
                                        </p>
                                    </div>
                                    <InstitutionalStatus
                                        status={inscripcion.estado_inscripcion}
                                    />
                                </div>
                                <div className="mt-4 rounded-xl bg-brand-bg p-3 text-xs">
                                    <p className="text-text-muted">Grupo/paralelo</p>
                                    <p className="mt-1 font-bold text-text-main">
                                        {inscripcion.grupo?.codigo_grupo ||
                                            'Sin grupo asignado'}
                                    </p>
                                </div>
                                <div className="mt-4 flex items-center justify-between">
                                    <Link
                                        href={route(
                                            'admin.institucional.ficha.postulante',
                                            inscripcion.id_post,
                                        )}
                                        className="inline-flex items-center gap-1 text-xs font-bold text-brand-secondary"
                                    >
                                        Ver ficha
                                        <ArrowRight className="h-3.5 w-3.5" />
                                    </Link>
                                    <button
                                        className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30"
                                        onClick={() => openForm(inscripcion)}
                                    >
                                        <Pencil className="h-4 w-4" />
                                    </button>
                                </div>
                            </article>
                        ))}
                    </div>
                </>
            ) : (
                <EmptyInstitutional
                    title="No hay inscripciones registradas"
                    description="Seleccione un programa y asigne postulantes existentes a sus grupos académicos."
                />
            )}
            <Pagination links={inscripciones.links} />

            <ModalInstitucional
                open={formModal.open}
                onOpenChange={(open) =>
                    setFormModal((current) => ({ ...current, open }))
                }
                title={
                    formModal.inscripcion
                        ? 'Actualizar inscripción académica'
                        : 'Nueva inscripción académica'
                }
                description="Asigne un postulante a un programa y grupo/paralelo."
                size="lg"
            >
                <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
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
                        onChange={(event) => form.setData('id_grupo', event.target.value)}
                        error={form.errors.id_grupo}
                    >
                        <option value="">Sin grupo asignado</option>
                        {filteredGroups.map((grupo) => (
                            <option key={grupo.id_grupo} value={grupo.id_grupo}>
                                {grupo.codigo_grupo || grupo.nombre_grupo} ·{' '}
                                {grupo.inscritos_count}/{grupo.capacidad_grupo}
                            </option>
                        ))}
                    </SelectField>
                    <SelectField
                        label="Postulante"
                        value={form.data.id_post}
                        onChange={(event) => form.setData('id_post', event.target.value)}
                        error={form.errors.id_post}
                        className="sm:col-span-2"
                    >
                        <option value="">Seleccione un postulante</option>
                        {postulantes.map((postulante) => (
                            <option key={postulante.id_post} value={postulante.id_post}>
                                {fullName(postulante)} ·{' '}
                                {postulante.carrera?.nombre_car || 'Sin carrera'}
                            </option>
                        ))}
                    </SelectField>
                    <Field
                        type="date"
                        label="Fecha de inscripción"
                        value={form.data.fecha_inscripcion}
                        onChange={(event) =>
                            form.setData('fecha_inscripcion', event.target.value)
                        }
                        error={form.errors.fecha_inscripcion}
                    />
                    <SelectField
                        label="Estado"
                        value={form.data.estado_inscripcion}
                        onChange={(event) =>
                            form.setData('estado_inscripcion', event.target.value)
                        }
                        error={form.errors.estado_inscripcion}
                    >
                        <option value="activo">Activa</option>
                        <option value="inactivo">Inactiva</option>
                    </SelectField>
                    <TextareaField
                        label="Observación"
                        value={form.data.observacion_inscripcion}
                        onChange={(event) =>
                            form.setData('observacion_inscripcion', event.target.value)
                        }
                        error={form.errors.observacion_inscripcion}
                        className="sm:col-span-2"
                    />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button
                            type="button"
                            className={secondaryButtonClass}
                            onClick={() =>
                                setFormModal({ open: false, inscripcion: null })
                            }
                        >
                            Cancelar
                        </button>
                        <button className={primaryButtonClass} disabled={form.processing}>
                            {formModal.inscripcion
                                ? 'Guardar cambios'
                                : 'Registrar inscripción'}
                        </button>
                    </div>
                </form>
            </ModalInstitucional>
        </AdminLayout>
    );
}
