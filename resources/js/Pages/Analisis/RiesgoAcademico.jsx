import {
    EmptyInstitutional,
    InstitutionalBanner,
    InstitutionalStatus,
    MetricTile,
    cardClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Activity, BarChart3, GraduationCap, ShieldAlert, UserRoundCheck } from 'lucide-react';

const name = (postulante) =>
    `${postulante?.nombres_post || ''} ${postulante?.apellidos_post || ''}`.trim();

export default function RiesgoAcademico({ registros = [], metricas = {} }) {
    return (
        <AdminLayout title="Indicadores de Desempeño" subtitle="Lectura referencial para priorizar seguimiento académico y apoyo tutorial." wide>
            <Head title="Indicadores de Desempeño" />
            <InstitutionalBanner
                eyebrow="Reportes y análisis"
                title="Seguimiento de riesgo académico"
                description="Organice la atención tutorial según rendimientos registrados. Esta lectura es referencial y no constituye una predicción definitiva."
                icon={ShieldAlert}
            />
            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <MetricTile label="Perfiles analizados" value={metricas.total || 0} icon={GraduationCap} tone="primary" />
                <MetricTile label="Atención prioritaria" value={metricas.prioritarios || 0} icon={ShieldAlert} tone="warning" />
                <MetricTile label="Seguimiento regular" value={metricas.seguimiento || 0} icon={Activity} tone="info" />
                <MetricTile label="Rendimiento favorable" value={metricas.favorables || 0} icon={UserRoundCheck} tone="success" />
            </div>
            {registros.length ? (
                <section className={`${cardClass} overflow-hidden`}>
                    <div className="border-b border-brand-border p-5 sm:p-6">
                        <h2 className="text-lg font-black text-text-main">Perfiles de seguimiento académico</h2>
                        <p className="mt-1 text-sm text-text-muted">Priorización basada en el rendimiento preliminar registrado.</p>
                    </div>
                    <div className="grid gap-4 p-4 md:grid-cols-2 xl:grid-cols-3 sm:p-6">
                        {registros.map((registro) => (
                            <article key={registro.id_rend} className="rounded-2xl border border-brand-border bg-brand-card p-5">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <h3 className="break-words font-black text-text-main">{name(registro.postulante)}</h3>
                                        <p className="mt-1 text-xs leading-5 text-text-muted">{registro.postulante?.carrera?.nombre_car || 'Carrera no registrada'}</p>
                                    </div>
                                    <InstitutionalStatus status={registro.nivel_riesgo_rend} />
                                </div>
                                <div className="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-brand-bg p-4 text-xs">
                                    <div><p className="text-text-muted">Promedio</p><p className="mt-1 text-xl font-black text-text-main">{Number(registro.promedio_general_rend || 0).toFixed(1)}</p></div>
                                    <div><p className="text-text-muted">Grupo</p><p className="mt-1 break-words font-bold text-text-main">{registro.grupo?.nombre_grupo || 'Sin grupo'}</p></div>
                                </div>
                                <p className="mt-4 text-xs leading-5 text-text-muted">{registro.observacion_rend || 'Revisar evolución por materia y coordinar seguimiento tutorial.'}</p>
                                <Link href={route('admin.institucional.ficha.postulante', registro.id_post)} className="mt-4 inline-flex items-center gap-2 text-xs font-bold text-brand-secondary hover:underline">
                                    <BarChart3 className="h-4 w-4" />
                                    Ver ficha académica
                                </Link>
                            </article>
                        ))}
                    </div>
                </section>
            ) : (
                <EmptyInstitutional title="Sin indicadores de desempeño" description="Los perfiles aparecerán cuando existan rendimientos académicos registrados." />
            )}
        </AdminLayout>
    );
}
