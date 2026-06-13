import {
    InstitutionalBanner,
    InstitutionalStatus,
    cardClass,
    secondaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    AtSign,
    BookOpenCheck,
    CalendarDays,
    Layers3,
    Phone,
    UserRoundCheck,
} from 'lucide-react';

const formatDate = (value) =>
    value
        ? new Date(`${String(value).slice(0, 10)}T00:00:00`).toLocaleDateString('es-BO')
        : 'Sin fecha definida';

export default function Show({ tutor }) {
    const detail = (label, value) => (
        <div className="rounded-xl border border-brand-border bg-brand-card p-4">
            <p className="text-xs text-text-muted">{label}</p>
            <p className="mt-1 break-words text-sm font-bold text-text-main">
                {value || 'No registrado'}
            </p>
        </div>
    );

    return (
        <AdminLayout
            title="Detalle del Tutor Académico"
            subtitle="Perfil profesional y responsabilidades de acompañamiento institucional."
            wide
        >
            <Head title={`Tutor - ${tutor.nombre_completo}`} />
            <div className="mb-4">
                <Link
                    href={route('admin.institucional.tutores.index')}
                    className={secondaryButtonClass}
                >
                    <ArrowLeft className="h-4 w-4" />
                    Volver a tutores
                </Link>
            </div>
            <InstitutionalBanner
                eyebrow="Acompañamiento académico"
                title={tutor.nombre_completo}
                description="Perfil institucional del personal responsable de orientar el seguimiento de postulantes en programas y grupos académicos."
                icon={UserRoundCheck}
                action={<InstitutionalStatus status={tutor.estado_tutor} />}
            />

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(340px,0.75fr)]">
                <section className={`${cardClass} p-5 sm:p-6`}>
                    <h2 className="text-lg font-black text-text-main">Perfil académico</h2>
                    <p className="mt-1 text-sm text-text-muted">
                        Información profesional y canales institucionales de contacto.
                    </p>
                    <div className="mt-5 grid gap-4 sm:grid-cols-2">
                        {detail('Especialidad', tutor.especialidad_tutor)}
                        {detail('Formación', tutor.formacion_tutor)}
                        {detail('C.I.', tutor.ci_tutor)}
                        {detail('Usuario vinculado', tutor.user?.email)}
                    </div>
                    <div className="mt-5 grid gap-3 sm:grid-cols-2">
                        <p className="flex items-center gap-3 rounded-xl bg-brand-bg p-4 text-sm text-text-main">
                            <AtSign className="h-4 w-4 shrink-0 text-brand-secondary" />
                            <span className="break-all">
                                {tutor.correo_tutor || 'Correo no registrado'}
                            </span>
                        </p>
                        <p className="flex items-center gap-3 rounded-xl bg-brand-bg p-4 text-sm text-text-main">
                            <Phone className="h-4 w-4 shrink-0 text-brand-secondary" />
                            {tutor.celular_tutor || 'Celular no registrado'}
                        </p>
                    </div>
                    <div className="mt-5 rounded-2xl border border-brand-border p-5">
                        <p className="text-xs font-bold uppercase tracking-wider text-text-muted">
                            Experiencia académica
                        </p>
                        <p className="mt-3 whitespace-pre-line text-sm leading-6 text-text-main">
                            {tutor.experiencia_tutor || 'Experiencia pendiente de registro.'}
                        </p>
                    </div>
                    {tutor.observacion_tutor && (
                        <div className="mt-4 rounded-2xl bg-brand-secondary/10 p-5">
                            <p className="text-xs font-bold uppercase tracking-wider text-brand-secondary">
                                Observación institucional
                            </p>
                            <p className="mt-3 text-sm leading-6 text-text-main">
                                {tutor.observacion_tutor}
                            </p>
                        </div>
                    )}
                </section>

                <section className={`${cardClass} p-5 sm:p-6`}>
                    <h2 className="text-lg font-black text-text-main">
                        Asignaciones tutoriales
                    </h2>
                    <p className="mt-1 text-sm text-text-muted">
                        Programas y grupos vinculados al perfil académico.
                    </p>
                    <div className="mt-5 space-y-3">
                        {tutor.asignaciones.map((asignacion) => (
                            <article
                                key={asignacion.id_asig}
                                className="rounded-2xl border border-brand-border p-4"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="break-words text-sm font-black text-text-main">
                                            {asignacion.grupo?.nombre_grupo ||
                                                asignacion.programa?.nombre_prog ||
                                                'Asignación institucional'}
                                        </p>
                                        <p className="mt-1 text-xs text-text-muted">
                                            {asignacion.grupo
                                                ? asignacion.programa?.nombre_prog
                                                : 'Cobertura por programa'}
                                        </p>
                                    </div>
                                    <InstitutionalStatus status={asignacion.estado_asig} />
                                </div>
                                <div className="mt-4 grid gap-2 text-xs text-text-muted">
                                    <p className="flex items-center gap-2">
                                        {asignacion.grupo ? (
                                            <Layers3 className="h-4 w-4 text-brand-secondary" />
                                        ) : (
                                            <BookOpenCheck className="h-4 w-4 text-brand-secondary" />
                                        )}
                                        {asignacion.rol_asig || 'Tutor académico'}
                                        {asignacion.materia_referencia_asig
                                            ? ` · ${asignacion.materia_referencia_asig}`
                                            : ''}
                                    </p>
                                    <p className="flex items-center gap-2">
                                        <CalendarDays className="h-4 w-4 text-brand-secondary" />
                                        {formatDate(asignacion.fecha_inicio_asig)} ·{' '}
                                        {formatDate(asignacion.fecha_fin_asig)}
                                    </p>
                                </div>
                            </article>
                        ))}
                        {!tutor.asignaciones.length && (
                            <p className="rounded-2xl bg-brand-bg p-5 text-center text-sm text-text-muted">
                                Este tutor todavía no tiene programas o grupos asignados.
                            </p>
                        )}
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
