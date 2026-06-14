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
    AtSign,
    BookOpenCheck,
    CalendarDays,
    Eye,
    GraduationCap,
    Layers3,
    Link2,
    Pencil,
    Phone,
    Plus,
    Search,
    UserRoundCheck,
} from 'lucide-react';
import { useState } from 'react';

const emptyTutor = {
    user_id: '',
    nombres_tutor: '',
    apellidos_tutor: '',
    ci_tutor: '',
    celular_tutor: '',
    correo_tutor: '',
    especialidad_tutor: '',
    formacion_tutor: '',
    experiencia_tutor: '',
    estado_tutor: 'activo',
    observacion_tutor: '',
};

export default function Index({
    tutores,
    especialidades = [],
    usuarios = [],
    filtros = {},
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
        especialidad_tutor: filtros.especialidad_tutor || '',
        estado_tutor: filtros.estado_tutor || '',
    });
    const [modal, setModal] = useState({ open: false, tutor: null });
    const [detail, setDetail] = useState(null);
    const form = useForm(emptyTutor);

    const openForm = (tutor = null) => {
        form.clearErrors();
        form.setData(
            tutor
                ? {
                      ...emptyTutor,
                      ...tutor,
                      user_id: tutor.user_id ? String(tutor.user_id) : '',
                  }
                : emptyTutor,
        );
        setModal({ open: true, tutor });
    };

    const submit = (event) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => setModal({ open: false, tutor: null }),
        };

        modal.tutor
            ? form.patch(
                  route('admin.institucional.tutores.update', modal.tutor.id_tutor),
                  options,
              )
            : form.post(route('admin.institucional.tutores.store'), options);
    };

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.tutores.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AdminLayout
            title="Tutores Académicos"
            subtitle="Perfiles del personal responsable del acompañamiento y seguimiento académico."
            wide
        >
            <Head title="Tutores Académicos" />
            <InstitutionalBanner
                eyebrow="Gestión institucional"
                title="Tutores Académicos"
                description="Administre la especialidad, formación y disponibilidad del equipo tutorial sin duplicar las funciones de acceso, roles o permisos."
                icon={UserRoundCheck}
                action={
                    <button className={primaryButtonClass} onClick={() => openForm()}>
                        <Plus className="h-4 w-4" />
                        Nuevo tutor
                    </button>
                }
            />
            <FlashMessage message={flash?.success} />

            <form
                onSubmit={applyFilters}
                className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-[minmax(0,1.5fr)_1fr_0.8fr_auto]`}
            >
                <label className="relative">
                    <Search className="pointer-events-none absolute left-3 top-3 h-4 w-4 text-text-muted" />
                    <input
                        className="h-10 w-full rounded-xl border border-brand-border bg-brand-card pl-9 pr-3 text-sm text-text-main outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary/15"
                        placeholder="Buscar por nombre, C.I. o correo"
                        value={filters.buscar}
                        onChange={(event) =>
                            setFilters({ ...filters, buscar: event.target.value })
                        }
                    />
                </label>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.especialidad_tutor}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            especialidad_tutor: event.target.value,
                        })
                    }
                >
                    <option value="">Todas las especialidades</option>
                    {especialidades.map((especialidad) => (
                        <option key={especialidad} value={especialidad}>
                            {especialidad}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.estado_tutor}
                    onChange={(event) =>
                        setFilters({ ...filters, estado_tutor: event.target.value })
                    }
                >
                    <option value="">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
                <button className={primaryButtonClass}>Filtrar</button>
            </form>

            {tutores.data.length ? (
                <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    {tutores.data.map((tutor) => (
                        <article key={tutor.id_tutor} className={`${cardClass} p-5 sm:p-6`}>
                            <div className="flex items-start justify-between gap-3">
                                <div className="min-w-0">
                                    <p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary">
                                        {tutor.especialidad_tutor || 'Acompañamiento académico'}
                                    </p>
                                    <h2 className="mt-2 break-words text-lg font-black text-text-main">
                                        {tutor.nombre_completo}
                                    </h2>
                                    <p className="mt-1 text-xs leading-5 text-text-muted">
                                        {tutor.formacion_tutor || 'Formación por registrar'}
                                    </p>
                                </div>
                                <InstitutionalStatus status={tutor.estado_tutor} />
                            </div>

                            <div className="mt-5 space-y-3 text-xs">
                                <p className="flex items-center gap-2 text-text-muted">
                                    <AtSign className="h-4 w-4 shrink-0 text-brand-secondary" />
                                    <span className="break-all">
                                        {tutor.correo_tutor || 'Correo no registrado'}
                                    </span>
                                </p>
                                <p className="flex items-center gap-2 text-text-muted">
                                    <Phone className="h-4 w-4 shrink-0 text-brand-secondary" />
                                    {tutor.celular_tutor || 'Celular no registrado'}
                                </p>
                                <p className="flex items-center gap-2 text-text-muted">
                                    <Link2 className="h-4 w-4 shrink-0 text-brand-secondary" />
                                    {tutor.user
                                        ? `Acceso vinculado a ${tutor.user.email}`
                                        : 'Sin cuenta de acceso vinculada'}
                                </p>
                            </div>

                            <div className="mt-5 flex items-center justify-between border-t border-brand-border pt-4">
                                <span className="text-xs font-bold text-text-muted">
                                    {tutor.asignaciones_activas_count} asignaciones activas
                                </span>
                                <div className="flex gap-1">
                                    <button
                                        type="button"
                                        onClick={() => setDetail(tutor)}
                                        className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30"
                                        aria-label={`Ver a ${tutor.nombre_completo}`}
                                    >
                                        <Eye className="h-4 w-4" />
                                    </button>
                                    <button
                                        className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30"
                                        onClick={() => openForm(tutor)}
                                        aria-label={`Editar a ${tutor.nombre_completo}`}
                                    >
                                        <Pencil className="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyInstitutional
                    title="No hay tutores para los filtros seleccionados"
                    description="Registre perfiles académicos para comenzar la asignación tutorial por programa y grupo."
                />
            )}
            <Pagination links={tutores.links} />

            <ModalInstitucional
                open={modal.open}
                onOpenChange={(open) => setModal((current) => ({ ...current, open }))}
                title={modal.tutor ? 'Editar tutor académico' : 'Nuevo tutor académico'}
                description="El vínculo con un usuario es opcional y solo habilita la relación con una cuenta de acceso existente."
                size="xl"
            >
                <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                    <Field
                        label="Nombres"
                        value={form.data.nombres_tutor}
                        onChange={(event) =>
                            form.setData('nombres_tutor', event.target.value)
                        }
                        error={form.errors.nombres_tutor}
                    />
                    <Field
                        label="Apellidos"
                        value={form.data.apellidos_tutor}
                        onChange={(event) =>
                            form.setData('apellidos_tutor', event.target.value)
                        }
                        error={form.errors.apellidos_tutor}
                    />
                    <Field
                        label="C.I."
                        value={form.data.ci_tutor}
                        onChange={(event) => form.setData('ci_tutor', event.target.value)}
                        error={form.errors.ci_tutor}
                    />
                    <Field
                        label="Celular"
                        value={form.data.celular_tutor}
                        onChange={(event) =>
                            form.setData('celular_tutor', event.target.value)
                        }
                        error={form.errors.celular_tutor}
                    />
                    <Field
                        type="email"
                        label="Correo de contacto"
                        value={form.data.correo_tutor}
                        onChange={(event) =>
                            form.setData('correo_tutor', event.target.value)
                        }
                        error={form.errors.correo_tutor}
                    />
                    <Field
                        label="Especialidad"
                        value={form.data.especialidad_tutor}
                        onChange={(event) =>
                            form.setData('especialidad_tutor', event.target.value)
                        }
                        error={form.errors.especialidad_tutor}
                    />
                    <Field
                        label="Formación profesional"
                        value={form.data.formacion_tutor}
                        onChange={(event) =>
                            form.setData('formacion_tutor', event.target.value)
                        }
                        error={form.errors.formacion_tutor}
                        className="sm:col-span-2"
                    />
                    <SelectField
                        label="Cuenta de acceso vinculada"
                        value={form.data.user_id}
                        onChange={(event) => form.setData('user_id', event.target.value)}
                        error={form.errors.user_id}
                    >
                        <option value="">Sin usuario vinculado</option>
                        {usuarios.map((usuario) => (
                            <option key={usuario.id} value={usuario.id}>
                                {usuario.name} · {usuario.email}
                            </option>
                        ))}
                    </SelectField>
                    <SelectField
                        label="Estado"
                        value={form.data.estado_tutor}
                        onChange={(event) =>
                            form.setData('estado_tutor', event.target.value)
                        }
                        error={form.errors.estado_tutor}
                    >
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </SelectField>
                    <TextareaField
                        label="Experiencia académica"
                        value={form.data.experiencia_tutor}
                        onChange={(event) =>
                            form.setData('experiencia_tutor', event.target.value)
                        }
                        error={form.errors.experiencia_tutor}
                    />
                    <TextareaField
                        label="Observación institucional"
                        value={form.data.observacion_tutor}
                        onChange={(event) =>
                            form.setData('observacion_tutor', event.target.value)
                        }
                        error={form.errors.observacion_tutor}
                    />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button
                            type="button"
                            className={secondaryButtonClass}
                            onClick={() => setModal({ open: false, tutor: null })}
                        >
                            Cancelar
                        </button>
                        <button className={primaryButtonClass} disabled={form.processing}>
                            <GraduationCap className="h-4 w-4" />
                            {modal.tutor ? 'Guardar cambios' : 'Registrar tutor'}
                        </button>
                    </div>
                </form>
            </ModalInstitucional>

            <ModalInstitucional
                open={Boolean(detail)}
                onOpenChange={(open) => !open && setDetail(null)}
                title="Detalle del Tutor Académico"
                description="Perfil profesional y responsabilidades activas de acompañamiento institucional."
                size="xl"
            >
                {detail && (
                    <div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(300px,0.8fr)]">
                        <section className="space-y-5">
                            <div className="rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary p-5 text-white">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-bold uppercase tracking-wider text-brand-accent">
                                            {detail.especialidad_tutor || 'Acompañamiento académico'}
                                        </p>
                                        <h3 className="mt-2 text-xl font-black">{detail.nombre_completo}</h3>
                                    </div>
                                    <InstitutionalStatus status={detail.estado_tutor} />
                                </div>
                                <p className="mt-3 text-sm leading-6 text-slate-200">
                                    {detail.formacion_tutor || 'Formación profesional pendiente de registro.'}
                                </p>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-2">
                                {[
                                    ['C.I.', detail.ci_tutor],
                                    ['Celular', detail.celular_tutor],
                                    ['Correo de contacto', detail.correo_tutor],
                                    ['Usuario asociado', detail.user?.email],
                                ].map(([label, value]) => (
                                    <div key={label} className="rounded-xl border border-brand-border bg-brand-card p-4">
                                        <p className="text-xs text-text-muted">{label}</p>
                                        <p className="mt-1 break-words text-sm font-bold text-text-main">{value || 'No registrado'}</p>
                                    </div>
                                ))}
                            </div>
                            <div className="rounded-2xl border border-brand-border p-5">
                                <p className="text-xs font-bold uppercase tracking-wider text-text-muted">Experiencia académica</p>
                                <p className="mt-3 whitespace-pre-line text-sm leading-6 text-text-main">
                                    {detail.experiencia_tutor || 'Experiencia pendiente de registro.'}
                                </p>
                            </div>
                            {detail.observacion_tutor && (
                                <div className="rounded-2xl border border-brand-info/25 bg-brand-info/10 p-5">
                                    <p className="text-xs font-bold uppercase tracking-wider text-brand-info">Observación institucional</p>
                                    <p className="mt-3 text-sm leading-6 text-text-main">{detail.observacion_tutor}</p>
                                </div>
                            )}
                        </section>
                        <section className="rounded-2xl border border-brand-border bg-brand-bg p-5">
                            <h3 className="text-base font-black text-text-main">Asignaciones activas</h3>
                            <p className="mt-1 text-xs leading-5 text-text-muted">Programas y grupos vinculados al tutor académico.</p>
                            <div className="mt-4 space-y-3">
                                {(detail.asignaciones || []).map((assignment) => (
                                    <article key={assignment.id_asig} className="rounded-2xl border border-brand-border bg-brand-card p-4">
                                        <div className="flex items-start gap-3">
                                            {assignment.grupo ? (
                                                <Layers3 className="mt-0.5 h-4 w-4 shrink-0 text-brand-secondary" />
                                            ) : (
                                                <BookOpenCheck className="mt-0.5 h-4 w-4 shrink-0 text-brand-secondary" />
                                            )}
                                            <div className="min-w-0">
                                                <p className="break-words text-sm font-black text-text-main">
                                                    {assignment.grupo?.nombre_grupo || assignment.programa?.nombre_prog || 'Asignación institucional'}
                                                </p>
                                                <p className="mt-1 text-xs leading-5 text-text-muted">
                                                    {assignment.grupo ? assignment.programa?.nombre_prog : 'Cobertura por programa'}
                                                </p>
                                                <p className="mt-2 flex items-center gap-1.5 text-xs text-text-muted">
                                                    <CalendarDays className="h-3.5 w-3.5" />
                                                    {assignment.fecha_inicio_asig
                                                        ? new Date(`${assignment.fecha_inicio_asig.slice(0, 10)}T00:00:00`).toLocaleDateString('es-BO')
                                                        : 'Sin fecha de inicio'}
                                                </p>
                                            </div>
                                        </div>
                                    </article>
                                ))}
                                {!detail.asignaciones?.length && (
                                    <div className="rounded-2xl border border-dashed border-brand-border p-6 text-center text-sm text-text-muted">
                                        Sin asignaciones activas.
                                    </div>
                                )}
                            </div>
                            <button type="button" className={`${secondaryButtonClass} mt-5 w-full`} onClick={() => setDetail(null)}>
                                Cerrar detalle
                            </button>
                        </section>
                    </div>
                )}
            </ModalInstitucional>
        </AdminLayout>
    );
}
