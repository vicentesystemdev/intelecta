import {
    EmptyInstitutional,
    FlashMessage,
    InstitutionalBanner,
    InstitutionalStatus,
    MetricTile,
    SelectField,
    Field,
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
    CalendarCheck2,
    CalendarDays,
    CheckCircle2,
    Clock3,
    Pencil,
    Plus,
    Save,
    UserRoundCheck,
    UserRoundX,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

const states = [
    ['presente', 'Presente'],
    ['ausente', 'Ausente'],
    ['retraso', 'Retraso'],
    ['justificado', 'Justificado'],
];

const stateButton = {
    presente: 'border-brand-success/30 bg-brand-success/10 text-brand-success',
    ausente: 'border-brand-danger/30 bg-brand-danger/10 text-brand-danger',
    retraso: 'border-brand-warning/30 bg-brand-warning/10 text-brand-warning',
    justificado: 'border-brand-info/30 bg-brand-info/10 text-brand-info',
};

const emptyAttendance = {
    id_prog: '',
    id_grupo: '',
    id_post: '',
    id_tutor: '',
    fecha_asist: '',
    sesion_asist: 'General',
    estado_asist: 'presente',
    observacion_asist: '',
};

const localToday = () => {
    const date = new Date();
    const offset = date.getTimezoneOffset();
    return new Date(date.getTime() - offset * 60_000).toISOString().slice(0, 10);
};

const formatDate = (value) =>
    value
        ? new Date(`${String(value).slice(0, 10)}T00:00:00`).toLocaleDateString(
              'es-BO',
          )
        : 'Sin fecha';

const tutorName = (tutor) =>
    `${tutor?.nombres_tutor || ''} ${tutor?.apellidos_tutor || ''}`.trim();

const studentName = (postulante) =>
    `${postulante?.nombres_post || ''} ${postulante?.apellidos_post || ''}`.trim();

export default function Index({
    asistencias,
    metricas = {},
    programas = [],
    grupos = [],
    tutores = [],
    inscripciones = [],
    listaGrupo = [],
    sesionSeleccionada = 'General',
    filtros = {},
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        id_prog: filtros.id_prog || '',
        id_grupo: filtros.id_grupo || '',
        fecha_asist: filtros.fecha_asist || '',
        sesion_asist: filtros.sesion_asist || sesionSeleccionada,
        estado_asist: filtros.estado_asist || '',
        id_tutor: filtros.id_tutor || '',
    });
    const [modal, setModal] = useState({ open: false, asistencia: null });
    const individual = useForm(emptyAttendance);
    const groupForm = useForm({
        id_prog: filtros.id_prog || '',
        id_grupo: filtros.id_grupo || '',
        id_tutor: filtros.id_tutor || '',
        fecha_asist: filtros.fecha_asist || '',
        sesion_asist: sesionSeleccionada,
        registros: [],
    });

    const filterGroups = useMemo(
        () =>
            filters.id_prog
                ? grupos.filter(
                      (group) => String(group.id_prog) === String(filters.id_prog),
                  )
                : grupos,
        [filters.id_prog, grupos],
    );

    const individualGroups = useMemo(
        () =>
            individual.data.id_prog
                ? grupos.filter(
                      (group) =>
                          String(group.id_prog) === String(individual.data.id_prog),
                  )
                : grupos,
        [individual.data.id_prog, grupos],
    );

    const individualStudents = useMemo(() => {
        const enrolled = individual.data.id_grupo
            ? inscripciones.filter(
                  (item) =>
                      String(item.id_grupo) === String(individual.data.id_grupo),
              )
            : inscripciones;

        return enrolled.filter(
            (item, index, collection) =>
                collection.findIndex(
                    (candidate) => candidate.id_post === item.id_post,
                ) === index,
        );
    }, [individual.data.id_grupo, inscripciones]);

    useEffect(() => {
        groupForm.setData({
            id_prog: filtros.id_prog || '',
            id_grupo: filtros.id_grupo || '',
            id_tutor: filtros.id_tutor || '',
            fecha_asist: filtros.fecha_asist || '',
            sesion_asist: sesionSeleccionada,
            registros: listaGrupo.map((item) => ({
                id_post: item.id_post,
                estado_asist: item.estado_asist || 'presente',
                observacion_asist: item.observacion_asist || '',
            })),
        });
    }, [listaGrupo, filtros, sesionSeleccionada]);

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.asistencia.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const setGroupStatus = (postulanteId, estado) => {
        groupForm.setData(
            'registros',
            groupForm.data.registros.map((record) =>
                record.id_post === postulanteId
                    ? { ...record, estado_asist: estado }
                    : record,
            ),
        );
    };

    const markAll = (estado) => {
        groupForm.setData(
            'registros',
            groupForm.data.registros.map((record) => ({
                ...record,
                estado_asist: estado,
            })),
        );
    };

    const submitGroup = (event) => {
        event.preventDefault();
        groupForm.post(route('admin.institucional.asistencia.store-grupo'), {
            preserveScroll: true,
        });
    };

    const openIndividual = (asistencia = null) => {
        individual.clearErrors();
        individual.setData(
            asistencia
                ? {
                      id_prog: String(asistencia.id_prog || ''),
                      id_grupo: String(asistencia.id_grupo),
                      id_post: String(asistencia.id_post),
                      id_tutor: String(asistencia.id_tutor || ''),
                      fecha_asist: asistencia.fecha_asist?.slice(0, 10) || '',
                      sesion_asist: asistencia.sesion_asist || 'General',
                      estado_asist: asistencia.estado_asist,
                      observacion_asist: asistencia.observacion_asist || '',
                  }
                : {
                      ...emptyAttendance,
                      id_prog: filters.id_prog,
                      id_grupo: filters.id_grupo,
                      id_tutor: filters.id_tutor,
                      fecha_asist: filters.fecha_asist,
                      sesion_asist: filters.sesion_asist || 'General',
                  },
        );
        setModal({ open: true, asistencia });
    };

    const submitIndividual = (event) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => setModal({ open: false, asistencia: null }),
        };

        modal.asistencia
            ? individual.patch(
                  route(
                      'admin.institucional.asistencia.update',
                      modal.asistencia.id_asist,
                  ),
                  options,
              )
            : individual.post(
                  route('admin.institucional.asistencia.store'),
                  options,
              );
    };

    const metrics = [
        ['Asistencias registradas', metricas.registradas || 0, CalendarDays, 'primary'],
        ['Presentes', metricas.presentes || 0, CheckCircle2, 'success'],
        ['Ausentes', metricas.ausentes || 0, UserRoundX, 'danger'],
        ['Retrasos', metricas.retrasos || 0, Clock3, 'warning'],
        ['Justificados', metricas.justificados || 0, UserRoundCheck, 'info'],
        ['Asistencia promedio', `${metricas.promedio || 0}%`, CalendarCheck2, 'accent'],
    ];

    return (
        <AdminLayout
            title="Control de Asistencia"
            subtitle="Registro de asistencia por programa, grupo, fecha y sesión académica."
            wide
        >
            <Head title="Control de Asistencia" />
            <InstitutionalBanner
                eyebrow="Gestión institucional"
                title="Control de Asistencia"
                description="Consolide la participación de postulantes por sesión y disponga de una variable complementaria para seguimiento académico."
                icon={CalendarCheck2}
                action={
                    <button
                        type="button"
                        className={primaryButtonClass}
                        onClick={() => openIndividual()}
                    >
                        <Plus className="h-4 w-4" />
                        Registro individual
                    </button>
                }
            />
            <FlashMessage message={flash?.success} />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {metrics.map(([label, value, icon, tone]) => (
                    <MetricTile
                        key={label}
                        label={label}
                        value={value}
                        note="Periodo y filtros seleccionados"
                        icon={icon}
                        tone={tone}
                    />
                ))}
            </div>

            <form
                onSubmit={applyFilters}
                className={`${cardClass} mb-6 grid gap-3 p-5 sm:p-6 md:grid-cols-2 xl:grid-cols-7`}
            >
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
                    onChange={(event) => {
                        const group = grupos.find(
                            (item) => String(item.id_grupo) === event.target.value,
                        );
                        setFilters({
                            ...filters,
                            id_grupo: event.target.value,
                            id_prog:
                                filters.id_prog ||
                                (group ? String(group.id_prog) : ''),
                        });
                    }}
                >
                    <option value="">Todos los grupos</option>
                    {filterGroups.map((grupo) => (
                        <option key={grupo.id_grupo} value={grupo.id_grupo}>
                            {grupo.nombre_grupo}
                        </option>
                    ))}
                </select>
                <input
                    type="date"
                    max={localToday()}
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.fecha_asist}
                    onChange={(event) =>
                        setFilters({ ...filters, fecha_asist: event.target.value })
                    }
                />
                <input
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    placeholder="Sesión"
                    value={filters.sesion_asist}
                    onChange={(event) =>
                        setFilters({ ...filters, sesion_asist: event.target.value })
                    }
                />
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.estado_asist}
                    onChange={(event) =>
                        setFilters({ ...filters, estado_asist: event.target.value })
                    }
                >
                    <option value="">Todos los estados</option>
                    {states.map(([value, label]) => (
                        <option key={value} value={value}>
                            {label}
                        </option>
                    ))}
                </select>
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
                <button className={primaryButtonClass}>Aplicar filtros</button>
            </form>

            <section className={`${cardClass} mb-6 p-5 sm:p-6`}>
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-wider text-brand-secondary">
                            Registro rápido por grupo
                        </p>
                        <h2 className="mt-1 text-xl font-black text-text-main">
                            Consolidación de la sesión
                        </h2>
                        <p className="mt-1 text-sm leading-6 text-text-muted">
                            Seleccione grupo y fecha en los filtros para cargar a
                            los postulantes inscritos.
                        </p>
                    </div>
                    {listaGrupo.length > 0 && (
                        <div className="flex flex-wrap gap-2">
                            {states.map(([value, label]) => (
                                <button
                                    key={value}
                                    type="button"
                                    onClick={() => markAll(value)}
                                    className={`rounded-xl border px-3 py-2 text-xs font-bold ${stateButton[value]}`}
                                >
                                    Todos: {label}
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                                {(groupForm.errors.id_grupo ||
                                    groupForm.errors.fecha_asist ||
                                    groupForm.errors.sesion_asist ||
                                    groupForm.errors.registros) && (
                                    <div className="mb-4 rounded-xl border border-brand-danger/30 bg-brand-danger/10 px-4 py-3 text-sm font-medium text-brand-danger">
                                        {groupForm.errors.id_grupo ||
                                            groupForm.errors.fecha_asist ||
                                            groupForm.errors.sesion_asist ||
                                            groupForm.errors.registros}
                                    </div>
                                )}

                                {listaGrupo.length > 0 ? (
                    <form onSubmit={submitGroup} className="mt-5">
                        <div className="grid gap-3">
                            {listaGrupo.map((item) => {
                                const record = groupForm.data.registros.find(
                                    (candidate) =>
                                        candidate.id_post === item.id_post,
                                );

                                return (
                                    <article
                                        key={item.id_post}
                                        className="grid gap-3 rounded-2xl border border-brand-border bg-brand-bg p-4 lg:grid-cols-[minmax(0,1fr)_auto]"
                                    >
                                        <div className="min-w-0">
                                            <p className="break-words text-sm font-black text-text-main">
                                                {studentName(item.postulante)}
                                            </p>
                                            <p className="mt-1 text-xs text-text-muted">
                                                C.I. {item.postulante?.ci_post || 'no registrada'}
                                            </p>
                                        </div>
                                        <div className="flex flex-wrap gap-2">
                                            {states.map(([value, label]) => (
                                                <button
                                                    key={value}
                                                    type="button"
                                                    onClick={() =>
                                                        setGroupStatus(
                                                            item.id_post,
                                                            value,
                                                        )
                                                    }
                                                    className={`rounded-xl border px-3 py-2 text-xs font-bold transition ${
                                                        record?.estado_asist === value
                                                            ? stateButton[value]
                                                            : 'border-brand-border bg-brand-card text-text-muted hover:bg-brand-border/30'
                                                    }`}
                                                >
                                                    {label}
                                                </button>
                                            ))}
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                        {groupForm.errors.registros && (
                            <p className="mt-3 text-xs font-bold text-brand-danger">
                                {groupForm.errors.registros}
                            </p>
                        )}
                        <div className="mt-5 flex justify-end">
                            <button
                                className={primaryButtonClass}
                                disabled={groupForm.processing}
                            >
                                <Save className="h-4 w-4" />
                                Guardar asistencia del grupo
                            </button>
                        </div>
                    </form>
                ) : (
                    <div className="mt-5">
                        <EmptyInstitutional
                            title="Seleccione un grupo y una fecha"
                            description="La lista de postulantes inscritos aparecerá aquí para registrar la sesión de forma consolidada."
                        />
                    </div>
                )}
            </section>

            <section className={`${cardClass} overflow-hidden`}>
                <div className="border-b border-brand-border p-5 sm:p-6">
                    <p className="text-xs font-bold uppercase tracking-wider text-brand-secondary">
                        Historial institucional
                    </p>
                    <h2 className="mt-1 text-xl font-black text-text-main">
                        Asistencias registradas
                    </h2>
                </div>

                {asistencias.data.length > 0 ? (
                    <>
                        <div className="hidden lg:block">
                            <table className="w-full table-fixed">
                                <thead className="bg-brand-bg text-left text-[10px] font-bold uppercase tracking-wider text-text-muted">
                                    <tr>
                                        <th className="w-[10%] px-4 py-3">Fecha</th>
                                        <th className="w-[12%] px-4 py-3">Sesión</th>
                                        <th className="w-[19%] px-4 py-3">Postulante</th>
                                        <th className="w-[18%] px-4 py-3">Programa / grupo</th>
                                        <th className="w-[15%] px-4 py-3">Tutor</th>
                                        <th className="w-[11%] px-4 py-3">Estado</th>
                                        <th className="w-[10%] px-4 py-3">Observación</th>
                                        <th className="w-[5%] px-4 py-3" />
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-brand-border">
                                    {asistencias.data.map((item) => (
                                        <tr key={item.id_asist}>
                                            <td className="px-4 py-4 align-top text-xs font-bold text-text-main">
                                                {formatDate(item.fecha_asist)}
                                            </td>
                                            <td className="break-words px-4 py-4 align-top text-xs text-text-muted">
                                                {item.sesion_asist}
                                            </td>
                                            <td className="break-words px-4 py-4 align-top text-sm font-bold leading-snug text-text-main">
                                                {studentName(item.postulante)}
                                            </td>
                                            <td className="break-words px-4 py-4 align-top">
                                                <p className="text-xs font-bold leading-snug text-text-main">
                                                    {item.programa?.nombre_prog || 'Sin programa'}
                                                </p>
                                                <p className="mt-1 text-xs leading-snug text-text-muted">
                                                    {item.grupo?.nombre_grupo}
                                                </p>
                                            </td>
                                            <td className="break-words px-4 py-4 align-top text-xs leading-snug text-text-muted">
                                                {item.tutor
                                                    ? tutorName(item.tutor)
                                                    : 'Sin tutor registrado'}
                                            </td>
                                            <td className="px-4 py-4 align-top">
                                                <InstitutionalStatus
                                                    status={item.estado_asist}
                                                />
                                            </td>
                                            <td className="break-words px-4 py-4 align-top text-xs leading-snug text-text-muted">
                                                {item.observacion_asist || 'Sin observación'}
                                            </td>
                                            <td className="px-4 py-4 align-top">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        openIndividual(item)
                                                    }
                                                    className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30"
                                                    aria-label="Editar asistencia"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <div className="grid gap-4 p-4 lg:hidden">
                            {asistencias.data.map((item) => (
                                <article
                                    key={item.id_asist}
                                    className="rounded-2xl border border-brand-border p-4"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="break-words font-black text-text-main">
                                                {studentName(item.postulante)}
                                            </p>
                                            <p className="mt-1 text-xs text-text-muted">
                                                {formatDate(item.fecha_asist)} ·{' '}
                                                {item.sesion_asist}
                                            </p>
                                        </div>
                                        <InstitutionalStatus
                                            status={item.estado_asist}
                                        />
                                    </div>
                                    <div className="mt-4 grid gap-3 text-xs sm:grid-cols-2">
                                        <div>
                                            <p className="font-bold text-text-muted">
                                                Programa / grupo
                                            </p>
                                            <p className="mt-1 break-words text-text-main">
                                                {item.programa?.nombre_prog || 'Sin programa'} ·{' '}
                                                {item.grupo?.nombre_grupo}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="font-bold text-text-muted">Tutor</p>
                                            <p className="mt-1 break-words text-text-main">
                                                {item.tutor
                                                    ? tutorName(item.tutor)
                                                    : 'Sin tutor registrado'}
                                            </p>
                                        </div>
                                    </div>
                                    <p className="mt-3 break-words text-xs leading-5 text-text-muted">
                                        {item.observacion_asist || 'Sin observación'}
                                    </p>
                                    <div className="mt-4 flex justify-end">
                                        <button
                                            type="button"
                                            onClick={() => openIndividual(item)}
                                            className={secondaryButtonClass}
                                        >
                                            <Pencil className="h-4 w-4" />
                                            Editar
                                        </button>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </>
                ) : (
                    <div className="p-5 sm:p-6">
                        <EmptyInstitutional
                            title="Sin registros de asistencia"
                            description="No existen asistencias para los filtros seleccionados."
                        />
                    </div>
                )}
            </section>
            <Pagination links={asistencias.links} />

            <ModalInstitucional
                open={modal.open}
                onOpenChange={(open) =>
                    setModal((current) => ({ ...current, open }))
                }
                title={
                    modal.asistencia
                        ? 'Editar asistencia académica'
                        : 'Registrar asistencia individual'
                }
                description="El postulante debe tener una inscripción activa en el grupo seleccionado."
                size="lg"
            >
                <form onSubmit={submitIndividual} className="grid gap-4 sm:grid-cols-2">
                    <SelectField
                        label="Programa académico"
                        value={individual.data.id_prog}
                        onChange={(event) =>
                            individual.setData({
                                ...individual.data,
                                id_prog: event.target.value,
                                id_grupo: '',
                                id_post: '',
                            })
                        }
                        error={individual.errors.id_prog}
                    >
                        <option value="">Seleccione un programa</option>
                        {programas.map((programa) => (
                            <option key={programa.id_prog} value={programa.id_prog}>
                                {programa.nombre_prog}
                            </option>
                        ))}
                    </SelectField>
                    <SelectField
                        label="Grupo / paralelo"
                        value={individual.data.id_grupo}
                        onChange={(event) => {
                            const group = grupos.find(
                                (item) =>
                                    String(item.id_grupo) === event.target.value,
                            );
                            individual.setData({
                                ...individual.data,
                                id_grupo: event.target.value,
                                id_prog:
                                    individual.data.id_prog ||
                                    String(group?.id_prog || ''),
                                id_post: '',
                            });
                        }}
                        error={individual.errors.id_grupo}
                    >
                        <option value="">Seleccione un grupo</option>
                        {individualGroups.map((grupo) => (
                            <option key={grupo.id_grupo} value={grupo.id_grupo}>
                                {grupo.nombre_grupo}
                            </option>
                        ))}
                    </SelectField>
                    <SelectField
                        label="Postulante inscrito"
                        value={individual.data.id_post}
                        onChange={(event) =>
                            individual.setData('id_post', event.target.value)
                        }
                        error={individual.errors.id_post}
                        className="sm:col-span-2"
                    >
                        <option value="">Seleccione un postulante</option>
                        {individualStudents.map((item) => (
                            <option key={item.id_post} value={item.id_post}>
                                {studentName(item.postulante)}
                            </option>
                        ))}
                    </SelectField>
                    <Field
                        type="date"
                        max={localToday()}
                        label="Fecha"
                        value={individual.data.fecha_asist}
                        onChange={(event) =>
                            individual.setData('fecha_asist', event.target.value)
                        }
                        error={individual.errors.fecha_asist}
                    />
                    <Field
                        label="Sesión académica"
                        value={individual.data.sesion_asist}
                        onChange={(event) =>
                            individual.setData('sesion_asist', event.target.value)
                        }
                        error={individual.errors.sesion_asist}
                    />
                    <SelectField
                        label="Tutor responsable"
                        value={individual.data.id_tutor}
                        onChange={(event) =>
                            individual.setData('id_tutor', event.target.value)
                        }
                        error={individual.errors.id_tutor}
                    >
                        <option value="">Sin tutor registrado</option>
                        {tutores.map((tutor) => (
                            <option key={tutor.id_tutor} value={tutor.id_tutor}>
                                {tutorName(tutor)}
                            </option>
                        ))}
                    </SelectField>
                    <SelectField
                        label="Estado"
                        value={individual.data.estado_asist}
                        onChange={(event) =>
                            individual.setData('estado_asist', event.target.value)
                        }
                        error={individual.errors.estado_asist}
                    >
                        {states.map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </SelectField>
                    <TextareaField
                        label="Observación"
                        value={individual.data.observacion_asist}
                        onChange={(event) =>
                            individual.setData(
                                'observacion_asist',
                                event.target.value,
                            )
                        }
                        error={individual.errors.observacion_asist}
                        className="sm:col-span-2"
                    />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button
                            type="button"
                            className={secondaryButtonClass}
                            onClick={() =>
                                setModal({ open: false, asistencia: null })
                            }
                        >
                            Cancelar
                        </button>
                        <button
                            className={primaryButtonClass}
                            disabled={individual.processing}
                        >
                            <Save className="h-4 w-4" />
                            Guardar asistencia
                        </button>
                    </div>
                </form>
            </ModalInstitucional>
        </AdminLayout>
    );
}
