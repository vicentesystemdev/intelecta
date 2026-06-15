import StudentTrackingNav from '@/Components/Estudiante/StudentTrackingNav';
import {
    InstitutionalStatus,
    MetricTile,
    cardClass,
} from '@/Components/Institucional/InstitutionalUi';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import {
    Award,
    BookOpenCheck,
    CalendarCheck2,
    GraduationCap,
    School,
    ShieldCheck,
    UserRoundCheck,
    WalletCards,
} from 'lucide-react';

const score = (value) =>
    value === null || value === undefined ? '—' : Number(value).toFixed(1);

const Detail = ({ label, value }) => (
    <div className="rounded-xl border border-brand-border bg-brand-bg p-4">
        <p className="text-xs font-semibold text-text-muted">{label}</p>
        <p className="mt-1 break-words text-sm font-black leading-snug text-text-main">
            {value || 'No registrado'}
        </p>
    </div>
);

export default function MiFicha({
    postulanteVinculado = false,
    postulante,
    inscripcion,
    rendimiento,
    posicion,
    percentil,
    recomendacion,
    tutorAsignado,
    administracion,
    asistencia,
    evaluacionesAplicadas = [],
}) {
    if (!postulanteVinculado) {
        return (
            <AuthenticatedLayout
                header={<h2 className="text-xl font-black text-text-main">Mi Ficha Académica</h2>}
            >
                <Head title="Mi Ficha Académica" />
                <main className="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                    <StudentTrackingNav />
                    <div className="rounded-2xl border border-brand-warning/25 bg-brand-warning/10 p-6">
                        <h1 className="font-black text-text-main">Ficha académica no vinculada</h1>
                        <p className="mt-2 text-sm leading-6 text-text-muted">
                            No se encontró una ficha académica asociada a tu cuenta. Comunícate con administración académica.
                        </p>
                    </div>
                </main>
            </AuthenticatedLayout>
        );
    }

    const fullName = `${postulante.nombres_post} ${postulante.apellidos_post}`;
    const tutorName =
        tutorAsignado?.nombre_completo ||
        (tutorAsignado
            ? `${tutorAsignado.nombres_tutor} ${tutorAsignado.apellidos_tutor}`.trim()
            : null) ||
        inscripcion?.grupo?.tutor_responsable_grupo;

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-black text-text-main">Mi Ficha Académica</h2>}
        >
            <Head title="Mi Ficha Académica" />
            <main className="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <StudentTrackingNav />

                <section className="overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-7 text-white shadow-xl sm:p-9">
                    <p className="text-xs font-black uppercase tracking-[0.18em] text-brand-accent">
                        Seguimiento académico personal
                    </p>
                    <h1 className="mt-3 text-3xl font-black">{fullName}</h1>
                    <p className="mt-3 max-w-3xl text-sm leading-6 text-slate-200">
                        Esta ficha consolida tu inscripción, rendimiento, asistencia y evaluaciones aplicadas para orientar el acompañamiento tutorial.
                    </p>
                </section>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <MetricTile label="Promedio general" value={score(rendimiento?.promedio_general_rend)} note="Resultados registrados" icon={Award} tone="accent" />
                    <MetricTile label="Asistencia" value={`${score(asistencia?.porcentaje ?? rendimiento?.asistencia_porcentaje_rend)}%`} note="Participación académica" icon={CalendarCheck2} tone="success" />
                    <MetricTile label="Posición" value={posicion ? `#${posicion}` : '—'} note="Dentro de tu programa" icon={BookOpenCheck} tone="primary" />
                    <MetricTile label="Percentil" value={percentil ?? '—'} note="Ubicación relativa referencial" icon={GraduationCap} tone="info" />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <section className={cardClass}>
                        <h2 className="flex items-center gap-2 text-lg font-black text-text-main">
                            <School className="h-5 w-5 text-brand-secondary" />
                            Datos académicos
                        </h2>
                        <div className="mt-5 grid gap-3 sm:grid-cols-2">
                            <Detail label="Colegio de procedencia" value={postulante.colegio?.nombre_col} />
                            <Detail label="Universidad objetivo" value={postulante.carrera?.universidad?.sigla_uni || postulante.carrera?.universidad?.nombre_uni} />
                            <Detail label="Carrera objetivo" value={postulante.carrera?.nombre_car} />
                            <Detail label="Gestión" value={postulante.gestion_post} />
                        </div>
                    </section>

                    <section className={cardClass}>
                        <h2 className="flex items-center gap-2 text-lg font-black text-text-main">
                            <UserRoundCheck className="h-5 w-5 text-brand-secondary" />
                            Programa y acompañamiento
                        </h2>
                        <div className="mt-5 grid gap-3 sm:grid-cols-2">
                            <Detail label="Programa académico" value={inscripcion?.programa?.nombre_prog} />
                            <Detail label="Grupo / paralelo" value={inscripcion?.grupo?.codigo_grupo || inscripcion?.grupo?.nombre_grupo} />
                            <Detail label="Tutor asignado" value={tutorName || 'Sin tutor asignado'} />
                            <Detail label="Estado de inscripción" value={inscripcion?.estado_inscripcion} />
                        </div>
                    </section>
                </div>

                <section className={cardClass}>
                    <h2 className="text-lg font-black text-text-main">Rendimiento por materia</h2>
                    <div className="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            ['Matemática', rendimiento?.promedio_matematica_rend],
                            ['Física', rendimiento?.promedio_fisica_rend],
                            ['Química', rendimiento?.promedio_quimica_rend],
                            ['Razonamiento académico', rendimiento?.promedio_paa_rend],
                        ].map(([label, value]) => (
                            <div key={label} className="rounded-2xl border border-brand-border bg-brand-bg p-5">
                                <p className="text-xs font-bold text-text-muted">{label}</p>
                                <p className="mt-2 text-3xl font-black text-text-main">{score(value)}</p>
                            </div>
                        ))}
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-2">
                    <section className={cardClass}>
                        <h2 className="flex items-center gap-2 text-lg font-black text-text-main">
                            <WalletCards className="h-5 w-5 text-brand-secondary" />
                            Estado administrativo
                        </h2>
                        {administracion ? (
                            <div className="mt-5 grid gap-3 sm:grid-cols-2">
                                <div className="rounded-xl border border-brand-border bg-brand-bg p-4">
                                    <p className="text-xs text-text-muted">Matrícula</p>
                                    <div className="mt-2"><InstitutionalStatus status={administracion.matricula?.estado_matricula_mat} /></div>
                                </div>
                                <div className="rounded-xl border border-brand-border bg-brand-bg p-4">
                                    <p className="text-xs text-text-muted">Habilitación</p>
                                    <div className="mt-2"><InstitutionalStatus status={administracion.habilitacion?.estado_hab || 'Sin registro'} /></div>
                                </div>
                                <Detail label="Estado de cuotas" value={administracion.estadoCuotas} />
                                <Detail label="Saldo pendiente referencial" value={`Bs ${Number(administracion.saldoPendiente || 0).toFixed(2)}`} />
                            </div>
                        ) : (
                            <p className="mt-4 text-sm text-text-muted">Sin registro administrativo.</p>
                        )}
                    </section>

                    <section className={cardClass}>
                        <h2 className="flex items-center gap-2 text-lg font-black text-text-main">
                            <ShieldCheck className="h-5 w-5 text-brand-secondary" />
                            Seguimiento institucional
                        </h2>
                        <div className="mt-4">
                            <InstitutionalStatus status={rendimiento?.nivel_riesgo_rend || 'Seguimiento regular'} />
                        </div>
                        <p className="mt-4 text-sm leading-6 text-text-muted">
                            {rendimiento?.observacion_rend || 'Información pendiente de consolidación académica.'}
                        </p>
                        <div className="mt-4 rounded-xl border border-brand-secondary/20 bg-brand-secondary/10 p-4">
                            <p className="text-xs font-black uppercase tracking-wider text-brand-secondary">Recomendación</p>
                            <p className="mt-2 text-sm leading-6 text-text-main">{recomendacion}</p>
                        </div>
                    </section>
                </div>

                <section className={cardClass}>
                    <h2 className="text-lg font-black text-text-main">Evaluaciones aplicadas</h2>
                    {evaluacionesAplicadas.length ? (
                        <div className="mt-5 grid gap-3">
                            {evaluacionesAplicadas.map((evaluation) => (
                                <article key={evaluation.id_eval_apl} className="grid gap-3 rounded-xl border border-brand-border bg-brand-bg p-4 sm:grid-cols-[1fr_auto_auto] sm:items-center">
                                    <div>
                                        <p className="font-black text-text-main">{evaluation.plantilla?.nombre_plan}</p>
                                        <p className="mt-1 text-xs text-text-muted">
                                            {evaluation.fecha_fin_eval_apl
                                                ? new Date(evaluation.fecha_fin_eval_apl).toLocaleDateString('es-BO')
                                                : 'En progreso'}
                                        </p>
                                    </div>
                                    <p className="text-sm font-bold text-text-main">
                                        {Number(evaluation.puntaje_total_eval_apl || 0).toFixed(1)} / {Number(evaluation.puntaje_maximo_eval_apl || 0).toFixed(1)}
                                    </p>
                                    <p className="text-xl font-black text-brand-secondary">
                                        {Number(evaluation.porcentaje_eval_apl || 0).toFixed(1)}%
                                    </p>
                                </article>
                            ))}
                        </div>
                    ) : (
                        <p className="mt-4 text-sm text-text-muted">Sin evaluaciones aplicadas registradas.</p>
                    )}
                </section>
            </main>
        </AuthenticatedLayout>
    );
}
