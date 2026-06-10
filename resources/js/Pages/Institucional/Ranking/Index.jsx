import Pagination from '@/Components/Pagination';
import {
    EmptyInstitutional,
    InstitutionalBanner,
    InstitutionalStatus,
    MetricTile,
    cardClass,
    primaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowRight,
    Award,
    BarChart3,
    CalendarCheck2,
    GraduationCap,
    Medal,
    ShieldCheck,
    TrendingUp,
    Users,
} from 'lucide-react';
import { useMemo, useState } from 'react';

export default function Index({
    ranking,
    metricas = {},
    programas = [],
    grupos = [],
    filtros = {},
}) {
    const [filters, setFilters] = useState({
        id_prog: filtros.id_prog || '',
        id_grupo: filtros.id_grupo || '',
        nivel_riesgo_rend: filtros.nivel_riesgo_rend || '',
    });
    const filteredGroups = useMemo(
        () =>
            filters.id_prog
                ? grupos.filter((grupo) => String(grupo.id_prog) === String(filters.id_prog))
                : grupos,
        [filters.id_prog, grupos],
    );

    const submit = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.ranking.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const formatScore = (value) => Number(value || 0).toFixed(1);
    const fullName = (item) =>
        `${item.postulante?.nombres_post || ''} ${item.postulante?.apellidos_post || ''}`.trim();

    const metrics = [
        ['Promedio institucional', formatScore(metricas.promedioInstitucional), 'Lectura general del rendimiento', TrendingUp, 'primary'],
        ['Alto rendimiento', metricas.altoRendimiento || 0, 'Postulantes con promedio ≥ 80', Award, 'accent'],
        ['En seguimiento', metricas.seguimiento || 0, 'Rendimiento entre 65 y 79,99', ShieldCheck, 'info'],
        ['Asistencia promedio', `${formatScore(metricas.asistenciaPromedio)}%`, 'Participación institucional', CalendarCheck2, 'success'],
        ['Mejor promedio', formatScore(metricas.mejorPromedio), 'Puntaje más alto registrado', Medal, 'accent'],
        ['Grupo destacado', metricas.grupoDestacado?.nombre || 'Sin datos', metricas.grupoDestacado ? `Promedio ${formatScore(metricas.grupoDestacado.promedio)}` : 'Pendiente de consolidación', Users, 'secondary'],
    ];

    return (
        <AdminLayout
            title="Ranking Académico"
            subtitle="Lectura institucional referencial del rendimiento por programa y grupo."
            wide
        >
            <Head title="Ranking Académico" />
            <InstitutionalBanner
                eyebrow="Seguimiento académico"
                title="Ranking Académico Institucional"
                description="Ordenamiento referencial por promedio general para apoyar la tutoría, la nivelación y el seguimiento académico."
                icon={BarChart3}
            />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                {metrics.map(([label, value, note, icon, tone]) => (
                    <MetricTile key={label} label={label} value={value} note={note} icon={icon} tone={tone} />
                ))}
            </div>

            <form
                onSubmit={submit}
                className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-[1fr_1fr_220px_auto]`}
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
                    onChange={(event) =>
                        setFilters({ ...filters, id_grupo: event.target.value })
                    }
                >
                    <option value="">Todos los grupos</option>
                    {filteredGroups.map((grupo) => (
                        <option key={grupo.id_grupo} value={grupo.id_grupo}>
                            {grupo.codigo_grupo || grupo.nombre_grupo}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.nivel_riesgo_rend}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            nivel_riesgo_rend: event.target.value,
                        })
                    }
                >
                    <option value="">Todos los seguimientos</option>
                    <option value="Alto rendimiento">Alto rendimiento</option>
                    <option value="Seguimiento regular">Seguimiento regular</option>
                    <option value="Atención prioritaria">Atención prioritaria</option>
                </select>
                <button className={primaryButtonClass}>Aplicar filtros</button>
            </form>

            {ranking.data.length ? (
                <>
                    <div className="hidden overflow-hidden rounded-2xl border border-brand-border bg-brand-card shadow-sm xl:block">
                        <table className="w-full table-fixed">
                            <thead className="border-b border-brand-border bg-brand-bg">
                                <tr>
                                    {[
                                        ['Pos.', 'w-[6%]'],
                                        ['Postulante', 'w-[20%]'],
                                        ['Programa / grupo', 'w-[18%]'],
                                        ['Promedio', 'w-[10%]'],
                                        ['Materias', 'w-[22%]'],
                                        ['Asistencia', 'w-[9%]'],
                                        ['Seguimiento', 'w-[10%]'],
                                        ['Ficha', 'w-[5%]'],
                                    ].map(([label, width]) => (
                                        <th key={label} className={`${width} px-3 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-text-muted`}>
                                            {label}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-brand-border">
                                {ranking.data.map((item) => (
                                    <tr key={item.id_rend} className="hover:bg-brand-bg/70">
                                        <td className="px-3 py-4 align-top">
                                            <span className={`inline-flex h-9 w-9 items-center justify-center rounded-xl font-black ${item.posicion <= 3 ? 'bg-brand-accent/20 text-brand-primary dark:text-brand-accent' : 'bg-brand-bg text-text-main'}`}>
                                                {item.posicion}
                                            </span>
                                        </td>
                                        <td className="px-3 py-4 align-top">
                                            <p className="break-words text-sm font-bold leading-snug text-text-main">{fullName(item)}</p>
                                            <p className="mt-1 text-xs text-text-muted">{item.postulante?.colegio?.nombre_col || 'Colegio no registrado'}</p>
                                            <p className="mt-1 text-xs text-brand-secondary">{item.postulante?.carrera?.nombre_car || 'Carrera no registrada'}</p>
                                        </td>
                                        <td className="px-3 py-4 align-top">
                                            <p className="break-words text-xs font-semibold leading-5 text-text-main">{item.programa?.nombre_prog || 'Sin programa'}</p>
                                            <p className="mt-1 text-xs text-text-muted">{item.grupo?.codigo_grupo || 'Sin grupo'}</p>
                                        </td>
                                        <td className="px-3 py-4 align-top">
                                            <p className="text-xl font-black text-brand-primary dark:text-slate-100">{formatScore(item.promedio_general_rend)}</p>
                                            <p className="mt-1 text-[10px] text-text-muted">Percentil {item.percentil}</p>
                                        </td>
                                        <td className="px-3 py-4 align-top">
                                            <div className="grid grid-cols-2 gap-x-3 gap-y-1 text-xs">
                                                <span className="text-text-muted">MAT <b className="text-text-main">{formatScore(item.promedio_matematica_rend)}</b></span>
                                                <span className="text-text-muted">FIS <b className="text-text-main">{formatScore(item.promedio_fisica_rend)}</b></span>
                                                <span className="text-text-muted">QMC <b className="text-text-main">{formatScore(item.promedio_quimica_rend)}</b></span>
                                                <span className="text-text-muted">PAA <b className="text-text-main">{formatScore(item.promedio_paa_rend)}</b></span>
                                            </div>
                                        </td>
                                        <td className="px-3 py-4 align-top text-sm font-bold text-text-main">{formatScore(item.asistencia_porcentaje_rend)}%</td>
                                        <td className="px-3 py-4 align-top"><InstitutionalStatus status={item.nivel_riesgo_rend} /><p className="mt-2 text-[10px] leading-4 text-text-muted">{item.recomendacion}</p></td>
                                        <td className="px-3 py-4 align-top">
                                            <Link href={route('admin.institucional.ficha.postulante', item.id_post)} className="inline-flex rounded-lg p-2 text-brand-secondary hover:bg-brand-secondary/10" aria-label="Ver ficha académica"><ArrowRight className="h-4 w-4" /></Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="grid gap-4 md:grid-cols-2 xl:hidden">
                        {ranking.data.map((item) => (
                            <article key={item.id_rend} className={`${cardClass} p-5`}>
                                <div className="flex items-start justify-between gap-3">
                                    <div className="flex min-w-0 items-start gap-3">
                                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-accent/20 font-black text-brand-primary dark:text-brand-accent">{item.posicion}</span>
                                        <div className="min-w-0"><h3 className="break-words text-sm font-black text-text-main">{fullName(item)}</h3><p className="mt-1 text-xs text-text-muted">{item.grupo?.codigo_grupo || 'Sin grupo'} · Percentil {item.percentil}</p></div>
                                    </div>
                                    <p className="text-2xl font-black text-brand-primary dark:text-slate-100">{formatScore(item.promedio_general_rend)}</p>
                                </div>
                                <div className="mt-4 grid grid-cols-4 gap-2 rounded-xl bg-brand-bg p-3 text-center text-xs">{[['MAT', item.promedio_matematica_rend], ['FIS', item.promedio_fisica_rend], ['QMC', item.promedio_quimica_rend], ['PAA', item.promedio_paa_rend]].map(([label, value]) => <div key={label}><p className="text-[10px] text-text-muted">{label}</p><p className="mt-1 font-black text-text-main">{formatScore(value)}</p></div>)}</div>
                                <div className="mt-4 flex items-center justify-between gap-3"><InstitutionalStatus status={item.nivel_riesgo_rend} /><Link href={route('admin.institucional.ficha.postulante', item.id_post)} className="inline-flex items-center gap-1 text-xs font-bold text-brand-secondary">Ver ficha <ArrowRight className="h-3.5 w-3.5" /></Link></div>
                            </article>
                        ))}
                    </div>
                </>
            ) : (
                <EmptyInstitutional title="Sin rendimiento para mostrar" description="El ranking se habilita con los rendimientos académicos referenciales asociados a programas y grupos." />
            )}
            <Pagination links={ranking.links} />

            <div className="mt-5 rounded-2xl border border-brand-info/25 bg-brand-info/10 p-4 text-xs leading-5 text-text-muted">
                El ranking institucional constituye una lectura académica referencial y una base para seguimiento tutorial. No representa garantía de admisión ni resultado oficial.
            </div>
        </AdminLayout>
    );
}
