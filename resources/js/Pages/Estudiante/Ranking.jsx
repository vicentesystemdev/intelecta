import Pagination from '@/Components/Pagination';
import StudentTrackingNav from '@/Components/Estudiante/StudentTrackingNav';
import {
    InstitutionalStatus,
    MetricTile,
    cardClass,
} from '@/Components/Institucional/InstitutionalUi';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Award, GraduationCap, Medal, TrendingUp } from 'lucide-react';

const score = (value) => Number(value || 0).toFixed(1);

export default function Ranking({
    postulanteVinculado = false,
    ranking = { data: [], links: [] },
    metricas = {},
    miPosicion = null,
    miRendimiento = null,
}) {
    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-black text-text-main">Ranking Académico</h2>}
        >
            <Head title="Ranking Académico" />
            <main className="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <StudentTrackingNav />

                <section className="overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-7 text-white shadow-xl sm:p-9">
                    <p className="text-xs font-black uppercase tracking-[0.18em] text-brand-accent">
                        Lectura académica referencial
                    </p>
                    <h1 className="mt-3 text-3xl font-black">Ranking Académico Institucional</h1>
                    <p className="mt-3 max-w-3xl text-sm leading-6 text-slate-200">
                        Ranking académico referencial basado en evaluaciones aplicadas y rendimiento registrado. Su finalidad es orientar el seguimiento académico.
                    </p>
                </section>

                {!postulanteVinculado && (
                    <div className="rounded-2xl border border-brand-warning/25 bg-brand-warning/10 p-6">
                        <p className="font-black text-text-main">Cuenta pendiente de vinculación académica</p>
                        <p className="mt-2 text-sm text-text-muted">
                            No se encontró una ficha académica asociada a tu cuenta. Comunícate con administración académica.
                        </p>
                    </div>
                )}

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <MetricTile label="Mi posición" value={miPosicion ? `#${miPosicion}` : '—'} note="Ubicación global referencial" icon={Medal} tone="accent" />
                    <MetricTile label="Mi promedio" value={miRendimiento ? score(miRendimiento.promedio) : '—'} note={miRendimiento?.estado || 'Sin rendimiento consolidado'} icon={GraduationCap} tone="primary" />
                    <MetricTile label="Promedio institucional" value={score(metricas.promedioInstitucional)} note="Resultados registrados" icon={TrendingUp} tone="info" />
                    <MetricTile label="Mejor promedio" value={score(metricas.mejorPromedio)} note="Referencia institucional" icon={Award} tone="success" />
                </div>

                <section className={cardClass}>
                    <div className="mb-5">
                        <h2 className="text-lg font-black text-text-main">Posiciones académicas</h2>
                        <p className="mt-1 text-sm text-text-muted">
                            Los nombres de otros postulantes se muestran de forma abreviada para proteger su privacidad.
                        </p>
                    </div>

                    {ranking.data.length ? (
                        <div className="grid gap-3">
                            {ranking.data.map((item) => (
                                <article
                                    key={item.id_rend}
                                    className={`grid gap-4 rounded-2xl border p-4 sm:grid-cols-[70px_1fr_130px_150px] sm:items-center ${
                                        item.es_actual
                                            ? 'border-brand-secondary bg-brand-secondary/10 shadow-sm'
                                            : 'border-brand-border bg-brand-bg'
                                    }`}
                                >
                                    <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-accent/20 font-black text-brand-primary dark:text-brand-accent">
                                        {item.posicion}
                                    </div>
                                    <div className="min-w-0">
                                        <p className="break-words font-black text-text-main">
                                            {item.nombre}
                                            {item.es_actual && (
                                                <span className="ml-2 rounded-full bg-brand-secondary px-2 py-0.5 text-[10px] text-white">
                                                    Tu posición
                                                </span>
                                            )}
                                        </p>
                                        <p className="mt-1 break-words text-xs leading-5 text-text-muted">
                                            {item.carrera || 'Carrera no registrada'} · {item.grupo || item.programa || 'Sin grupo'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-bold uppercase tracking-wider text-text-muted">Promedio</p>
                                        <p className="mt-1 text-2xl font-black text-text-main">{score(item.promedio)}</p>
                                    </div>
                                    <div>
                                        <InstitutionalStatus status={item.estado} />
                                        <p className="mt-2 text-xs text-text-muted">Percentil {item.percentil}</p>
                                    </div>
                                </article>
                            ))}
                        </div>
                    ) : (
                        <div className="rounded-2xl border border-dashed border-brand-border p-8 text-center">
                            <p className="font-black text-text-main">Sin rendimientos consolidados</p>
                            <p className="mt-1 text-sm text-text-muted">
                                Las posiciones aparecerán cuando existan evaluaciones aplicadas finalizadas.
                            </p>
                        </div>
                    )}
                </section>

                <Pagination links={ranking.links || []} />

                <div className="rounded-2xl border border-brand-info/25 bg-brand-info/10 p-5 text-sm leading-6 text-text-muted">
                    Esta clasificación es una lectura académica referencial para seguimiento tutorial. No representa una predicción de ingreso, garantía de aprobación ni resultado oficial.
                </div>
            </main>
        </AuthenticatedLayout>
    );
}
