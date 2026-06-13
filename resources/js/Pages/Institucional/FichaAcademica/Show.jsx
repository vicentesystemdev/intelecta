import {
    InstitutionalBanner,
    InstitutionalStatus,
    MetricTile,
    cardClass,
    secondaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Award,
    BookOpenCheck,
    CalendarCheck2,
    GraduationCap,
    School,
    Target,
    UserRoundCheck,
    WalletCards,
} from 'lucide-react';
import { useState } from 'react';

const tabs = [
    ['datos', 'Datos académicos'],
    ['programa', 'Programa y grupo'],
    ['rendimiento', 'Rendimiento'],
    ['administracion', 'Estado administrativo'],
    ['seguimiento', 'Seguimiento'],
];

export default function Show({
    postulante,
    inscripcion,
    rendimiento,
    posicion,
    percentil,
    recomendacion,
    tutorAsignado,
    administracion,
}) {
    const [activeTab, setActiveTab] = useState('datos');
    const fullName = `${postulante.nombres_post} ${postulante.apellidos_post}`;
    const score = (value) => (value === null || value === undefined ? '—' : Number(value).toFixed(1));
    const detail = (label, value) => (
        <div className="rounded-xl border border-brand-border bg-brand-card p-4">
            <p className="text-xs text-text-muted">{label}</p>
            <p className="mt-1 break-words text-sm font-bold text-text-main">{value || 'No registrado'}</p>
        </div>
    );

    return (
        <AdminLayout
            title="Ficha Académica del Postulante"
            subtitle="Consolidación institucional de inscripción, rendimiento y seguimiento."
            wide
        >
            <Head title={`Ficha académica - ${fullName}`} />
            <div className="mb-4">
                <Link href={route('admin.institucional.ranking.index')} className={secondaryButtonClass}>
                    <ArrowLeft className="h-4 w-4" />
                    Volver al ranking
                </Link>
            </div>
            <InstitutionalBanner
                eyebrow="Seguimiento académico"
                title={fullName}
                description="Esta ficha consolida una lectura académica preliminar del postulante, integrando inscripción, grupo, rendimiento referencial y asistencia para apoyar el seguimiento tutorial."
                icon={GraduationCap}
                action={<InstitutionalStatus status={rendimiento?.nivel_riesgo_rend || 'Seguimiento regular'} />}
            />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <MetricTile label="Promedio general" value={score(rendimiento?.promedio_general_rend)} note="Rendimiento preliminar sobre 100" icon={Award} tone="accent" />
                <MetricTile label="Asistencia" value={`${score(rendimiento?.asistencia_porcentaje_rend)}%`} note="Participación en el ciclo" icon={CalendarCheck2} tone="success" />
                <MetricTile label="Posición" value={posicion ? `#${posicion}` : '—'} note="Ranking del programa académico" icon={Target} tone="primary" />
                <MetricTile label="Percentil aproximado" value={percentil !== null ? `${percentil}` : '—'} note="Ubicación relativa referencial" icon={BookOpenCheck} tone="info" />
            </div>

            <div className={`${cardClass} overflow-hidden`}>
                <div className="flex gap-1 overflow-x-auto border-b border-brand-border p-2">
                    {tabs.map(([key, label]) => (
                        <button key={key} onClick={() => setActiveTab(key)} className={`shrink-0 rounded-xl px-4 py-2.5 text-xs font-bold transition ${activeTab === key ? 'bg-brand-secondary text-white' : 'text-text-muted hover:bg-brand-border/30 hover:text-text-main'}`}>
                            {label}
                        </button>
                    ))}
                </div>
                <div className="p-5 sm:p-6">
                    {activeTab === 'datos' && (
                        <div>
                            <h2 className="text-lg font-black text-text-main">Datos académicos del postulante</h2>
                            <p className="mt-1 text-sm text-text-muted">Información básica y objetivo de postulación.</p>
                            <div className="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {detail('Nombres', postulante.nombres_post)}
                                {detail('Apellidos', postulante.apellidos_post)}
                                {detail('Edad', postulante.edad_post ? `${postulante.edad_post} años` : null)}
                                {detail('Colegio de procedencia', postulante.colegio?.nombre_col)}
                                {detail('Universidad objetivo', postulante.carrera?.universidad?.sigla_uni || postulante.carrera?.universidad?.nombre_uni)}
                                {detail('Carrera objetivo', postulante.carrera?.nombre_car)}
                                {detail('Turno de referencia', postulante.turno_post)}
                                {detail('Gestión', postulante.gestion_post)}
                                {detail('Estado del postulante', postulante.estado_post)}
                            </div>
                        </div>
                    )}
                    {activeTab === 'programa' && (
                        <div>
                            <h2 className="text-lg font-black text-text-main">Programa y grupo/paralelo</h2>
                            <p className="mt-1 text-sm text-text-muted">Asignación institucional vigente para el ciclo de nivelación.</p>
                            <div className="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {detail('Programa académico', inscripcion?.programa?.nombre_prog)}
                                {detail('Código del programa', inscripcion?.programa?.codigo_prog)}
                                {detail('Grupo/paralelo', inscripcion?.grupo?.nombre_grupo)}
                                {detail('Código del grupo', inscripcion?.grupo?.codigo_grupo)}
                                {detail(
                                    'Tutor responsable',
                                    tutorAsignado?.nombre_completo ||
                                        (tutorAsignado
                                            ? `${tutorAsignado.nombres_tutor} ${tutorAsignado.apellidos_tutor}`.trim()
                                            : null) ||
                                        inscripcion?.grupo?.tutor_responsable_grupo ||
                                        'Sin tutor asignado',
                                )}
                                {detail('Fecha de inscripción', inscripcion?.fecha_inscripcion ? new Date(`${inscripcion.fecha_inscripcion.slice(0, 10)}T00:00:00`).toLocaleDateString('es-BO') : null)}
                            </div>
                        </div>
                    )}
                    {activeTab === 'rendimiento' && (
                        <div>
                            <h2 className="text-lg font-black text-text-main">Rendimiento por materia</h2>
                            <p className="mt-1 text-sm text-text-muted">Lectura referencial consolidada del ciclo académico.</p>
                            <div className="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                {[['Matemática', rendimiento?.promedio_matematica_rend, 'bg-brand-primary'], ['Física', rendimiento?.promedio_fisica_rend, 'bg-brand-secondary'], ['Química', rendimiento?.promedio_quimica_rend, 'bg-brand-success'], ['Razonamiento académico', rendimiento?.promedio_paa_rend, 'bg-brand-accent']].map(([label, value, color]) => {
                                    const numeric = Number(value || 0);
                                    return <div key={label} className="rounded-2xl border border-brand-border p-5"><p className="text-xs font-bold text-text-muted">{label}</p><p className="mt-3 text-3xl font-black text-text-main">{score(value)}</p><div className="mt-4 h-2 overflow-hidden rounded-full bg-brand-border"><div className={`h-full rounded-full ${color}`} style={{ width: `${Math.min(100, numeric)}%` }} /></div></div>;
                                })}
                            </div>
                        </div>
                    )}
                    {activeTab === 'administracion' && (
                        <div>
                            <h2 className="flex items-center gap-2 text-lg font-black text-text-main">
                                <WalletCards className="h-5 w-5 text-brand-secondary" />
                                Estado administrativo académico
                            </h2>
                            <p className="mt-1 text-sm text-text-muted">
                                Lectura interna de matrícula, cuotas y habilitación vigente.
                            </p>
                            {administracion ? (
                                <>
                                    <div className="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                        {detail('Código de matrícula', administracion.matricula?.codigo_mat)}
                                        <div className="rounded-xl border border-brand-border bg-brand-card p-4">
                                            <p className="text-xs text-text-muted">Estado de matrícula</p>
                                            <div className="mt-2"><InstitutionalStatus status={administracion.matricula?.estado_matricula_mat} /></div>
                                        </div>
                                        <div className="rounded-xl border border-brand-border bg-brand-card p-4">
                                            <p className="text-xs text-text-muted">Estado de cuotas</p>
                                            <div className="mt-2"><InstitutionalStatus status={administracion.estadoCuotas} /></div>
                                        </div>
                                        {detail('Saldo pendiente referencial', `Bs ${Number(administracion.saldoPendiente || 0).toFixed(2)}`)}
                                        <div className="rounded-xl border border-brand-border bg-brand-card p-4">
                                            <p className="text-xs text-text-muted">Habilitación académica</p>
                                            <div className="mt-2"><InstitutionalStatus status={administracion.habilitacion?.estado_hab || 'Sin registro'} /></div>
                                        </div>
                                        {detail('Beneficio', administracion.matricula?.tipo_beneficio_mat)}
                                    </div>
                                    <div className="mt-5 grid gap-4 sm:grid-cols-3">
                                        {[
                                            ['Evaluaciones', administracion.habilitacion?.habilitado_evaluaciones_hab],
                                            ['Simulacros', administracion.habilitacion?.habilitado_simulacros_hab],
                                            ['Reportes', administracion.habilitacion?.habilitado_reportes_hab],
                                        ].map(([label, enabled]) => (
                                            <div key={label} className="rounded-2xl border border-brand-border p-4">
                                                <p className="text-xs font-bold text-text-muted">{label}</p>
                                                <p className={`mt-2 text-sm font-black ${enabled ? 'text-brand-success' : 'text-text-muted'}`}>
                                                    {enabled ? 'Acceso habilitado' : 'Acceso restringido'}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="mt-5 rounded-2xl bg-brand-bg p-5">
                                        <p className="text-xs font-bold uppercase tracking-wider text-text-muted">Observación administrativa</p>
                                        <p className="mt-3 text-sm leading-6 text-text-main">
                                            {administracion.habilitacion?.observacion_hab ||
                                                administracion.matricula?.observacion_mat ||
                                                'Sin observaciones administrativas vigentes.'}
                                        </p>
                                    </div>
                                </>
                            ) : (
                                <div className="mt-5 rounded-2xl border border-dashed border-brand-border p-8 text-center">
                                    <p className="font-bold text-text-main">Sin registro administrativo</p>
                                    <p className="mt-1 text-sm text-text-muted">
                                        No existe una matrícula académica asociada a la inscripción vigente.
                                    </p>
                                </div>
                            )}
                        </div>
                    )}
                    {activeTab === 'seguimiento' && (
                        <div>
                            <h2 className="text-lg font-black text-text-main">Seguimiento académico</h2>
                            <p className="mt-1 text-sm text-text-muted">Observaciones y orientación institucional para tutoría.</p>
                            <div className="mt-5 grid gap-5 lg:grid-cols-2">
                                <div className="rounded-2xl border border-brand-border bg-brand-bg p-5"><p className="text-xs font-bold uppercase tracking-wider text-text-muted">Estado de seguimiento</p><div className="mt-3"><InstitutionalStatus status={rendimiento?.nivel_riesgo_rend || 'Seguimiento regular'} /></div><p className="mt-4 text-sm leading-6 text-text-muted">{rendimiento?.observacion_rend || 'Información pendiente de consolidación académica.'}</p></div>
                                <div className="rounded-2xl border border-brand-secondary/25 bg-brand-secondary/10 p-5"><p className="text-xs font-bold uppercase tracking-wider text-brand-secondary">Recomendación institucional</p><p className="mt-3 text-sm leading-6 text-text-main">{recomendacion}</p></div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
