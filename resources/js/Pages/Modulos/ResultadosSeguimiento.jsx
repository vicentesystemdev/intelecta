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
import { Head, router } from '@inertiajs/react';
import {
    CheckCircle2,
    ClipboardCheck,
    Clock3,
    FileChartColumn,
    TrendingUp,
} from 'lucide-react';
import { useState } from 'react';

const formatDate = (value) =>
    value
        ? new Date(value).toLocaleString('es-BO', {
              dateStyle: 'medium',
              timeStyle: 'short',
          })
        : 'Sin fecha';

const formatDuration = (seconds) => {
    if (seconds === null || seconds === undefined) return 'No registrado';
    const minutes = Math.floor(Number(seconds) / 60);
    const rest = Number(seconds) % 60;
    return `${minutes} min ${rest} s`;
};

export default function ResultadosSeguimiento({
    resultados,
    plantillas = [],
    filtros = {},
    metricas = {},
    estructuraResultadosDisponible = false,
}) {
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
        id_plantilla: filtros.id_plantilla || '',
        estado_eval_apl: filtros.estado_eval_apl || '',
        fecha_desde: filtros.fecha_desde || '',
        fecha_hasta: filtros.fecha_hasta || '',
    });

    const submit = (event) => {
        event.preventDefault();
        router.get(route('admin.evaluaciones.resultados'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const metrics = [
        [
            'Evaluaciones aplicadas',
            metricas.total || 0,
            'Registros trazables',
            ClipboardCheck,
            'primary',
        ],
        [
            'Evaluaciones finalizadas',
            metricas.finalizadas || 0,
            'Resultados calculados',
            CheckCircle2,
            'success',
        ],
        [
            'En progreso',
            metricas.enProgreso || 0,
            'Aplicaciones abiertas',
            Clock3,
            'info',
        ],
        [
            'Promedio general',
            `${Number(metricas.promedio || 0).toFixed(1)}%`,
            'Sobre resultados finalizados',
            TrendingUp,
            'accent',
        ],
    ];

    return (
        <AdminLayout
            title="Resultados Académicos"
            subtitle="Trazabilidad de evaluaciones aplicadas, respuestas registradas y puntajes calculados."
            wide
        >
            <Head title="Resultados Académicos" />

            <InstitutionalBanner
                eyebrow="Seguimiento académico"
                title="Resultados Académicos Trazables"
                description="Consulta institucional de aplicaciones evaluativas finalizadas y en progreso, vinculadas a postulantes y plantillas reales."
                icon={FileChartColumn}
            />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {metrics.map(([label, value, note, icon, tone]) => (
                    <MetricTile
                        key={label}
                        label={label}
                        value={value}
                        note={note}
                        icon={icon}
                        tone={tone}
                    />
                ))}
            </div>

            {!estructuraResultadosDisponible && (
                <div className="mb-5 rounded-2xl border border-brand-info/25 bg-brand-info/10 p-5 text-sm text-text-muted">
                    La estructura de evaluaciones aplicadas todavía no está
                    habilitada en la base de datos. Ejecute las migraciones para
                    iniciar el registro trazable.
                </div>
            )}

            <form
                onSubmit={submit}
                className={`${cardClass} mb-5 grid gap-3 p-4 lg:grid-cols-[1.4fr_1fr_180px_160px_160px_auto]`}
            >
                <input
                    value={filters.buscar}
                    onChange={(event) =>
                        setFilters({ ...filters, buscar: event.target.value })
                    }
                    placeholder="Postulante o C.I."
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                />
                <select
                    value={filters.id_plantilla}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            id_plantilla: event.target.value,
                        })
                    }
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                >
                    <option value="">Todas las plantillas</option>
                    {plantillas.map((plantilla) => (
                        <option
                            key={plantilla.id_plan}
                            value={plantilla.id_plan}
                        >
                            {plantilla.nombre_plan}
                        </option>
                    ))}
                </select>
                <select
                    value={filters.estado_eval_apl}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            estado_eval_apl: event.target.value,
                        })
                    }
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                >
                    <option value="">Todos los estados</option>
                    <option value="finalizada">Finalizada</option>
                    <option value="en_progreso">En progreso</option>
                    <option value="anulada">Anulada</option>
                </select>
                <input
                    type="date"
                    value={filters.fecha_desde}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            fecha_desde: event.target.value,
                        })
                    }
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                />
                <input
                    type="date"
                    min={filters.fecha_desde || undefined}
                    value={filters.fecha_hasta}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            fecha_hasta: event.target.value,
                        })
                    }
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                />
                <button className={primaryButtonClass}>Aplicar</button>
            </form>

            {resultados.data.length ? (
                <>
                    <div className="hidden overflow-hidden rounded-2xl border border-brand-border bg-brand-card shadow-sm xl:block">
                        <table className="w-full table-fixed">
                            <thead className="border-b border-brand-border bg-brand-bg">
                                <tr>
                                    {[
                                        ['Postulante', 'w-[20%]'],
                                        ['Plantilla', 'w-[25%]'],
                                        ['Resultado', 'w-[12%]'],
                                        ['Respuestas', 'w-[10%]'],
                                        ['Finalización', 'w-[15%]'],
                                        ['Tiempo', 'w-[10%]'],
                                        ['Estado', 'w-[8%]'],
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
                                {resultados.data.map((item) => (
                                    <tr
                                        key={item.id_eval_apl}
                                        className="hover:bg-brand-bg/70"
                                    >
                                        <td className="px-4 py-4 align-top">
                                            <p className="break-words text-sm font-bold leading-snug text-text-main">
                                                {item.postulante?.nombres_post}{' '}
                                                {item.postulante?.apellidos_post}
                                            </p>
                                            <p className="mt-1 text-xs text-text-muted">
                                                C.I.{' '}
                                                {item.postulante?.ci_post ||
                                                    'No registrado'}
                                            </p>
                                        </td>
                                        <td className="px-4 py-4 align-top">
                                            <p className="break-words text-xs font-semibold leading-5 text-text-main">
                                                {item.plantilla?.nombre_plan}
                                            </p>
                                            <p className="mt-1 text-[10px] text-text-muted">
                                                {item.codigo_eval_apl}
                                            </p>
                                        </td>
                                        <td className="px-4 py-4 align-top">
                                            <p className="text-xl font-black text-brand-primary dark:text-slate-100">
                                                {Number(
                                                    item.porcentaje_eval_apl,
                                                ).toFixed(1)}
                                                %
                                            </p>
                                            <p className="mt-1 text-xs text-text-muted">
                                                {Number(
                                                    item.puntaje_total_eval_apl,
                                                ).toFixed(2)}{' '}
                                                /{' '}
                                                {Number(
                                                    item.puntaje_maximo_eval_apl,
                                                ).toFixed(2)}
                                            </p>
                                        </td>
                                        <td className="px-4 py-4 align-top text-xs font-semibold text-text-main">
                                            {
                                                item.respuestas_correctas_count
                                            }{' '}
                                            / {item.respuestas_count}
                                        </td>
                                        <td className="px-4 py-4 align-top text-xs leading-5 text-text-muted">
                                            {formatDate(
                                                item.fecha_fin_eval_apl,
                                            )}
                                        </td>
                                        <td className="px-4 py-4 align-top text-xs text-text-muted">
                                            {formatDuration(
                                                item.tiempo_total_segundos_eval_apl,
                                            )}
                                        </td>
                                        <td className="px-4 py-4 align-top">
                                            <InstitutionalStatus
                                                status={
                                                    item.estado_eval_apl
                                                }
                                            />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="grid gap-4 xl:hidden">
                        {resultados.data.map((item) => (
                            <article
                                key={item.id_eval_apl}
                                className={`${cardClass} p-5`}
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 className="font-black text-text-main">
                                            {item.postulante?.nombres_post}{' '}
                                            {item.postulante?.apellidos_post}
                                        </h3>
                                        <p className="mt-1 text-xs leading-5 text-text-muted">
                                            {item.plantilla?.nombre_plan}
                                        </p>
                                    </div>
                                    <p className="text-2xl font-black text-brand-primary dark:text-slate-100">
                                        {Number(
                                            item.porcentaje_eval_apl,
                                        ).toFixed(1)}
                                        %
                                    </p>
                                </div>
                                <div className="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-brand-bg p-4 text-xs">
                                    <div>
                                        <p className="text-text-muted">
                                            Puntaje
                                        </p>
                                        <p className="mt-1 font-black text-text-main">
                                            {Number(
                                                item.puntaje_total_eval_apl,
                                            ).toFixed(2)}{' '}
                                            /{' '}
                                            {Number(
                                                item.puntaje_maximo_eval_apl,
                                            ).toFixed(2)}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-text-muted">
                                            Respuestas
                                        </p>
                                        <p className="mt-1 font-black text-text-main">
                                            {
                                                item.respuestas_correctas_count
                                            }{' '}
                                            / {item.respuestas_count}
                                        </p>
                                    </div>
                                </div>
                                <div className="mt-4 flex items-center justify-between gap-3">
                                    <p className="text-xs text-text-muted">
                                        {formatDate(
                                            item.fecha_fin_eval_apl,
                                        )}
                                    </p>
                                    <InstitutionalStatus
                                        status={item.estado_eval_apl}
                                    />
                                </div>
                            </article>
                        ))}
                    </div>
                </>
            ) : (
                <EmptyInstitutional
                    title="Sin evaluaciones aplicadas registradas"
                    description="Los resultados aparecerán cuando un postulante finalice una evaluación desde su portal académico."
                />
            )}

            <Pagination links={resultados.links} />
        </AdminLayout>
    );
}
