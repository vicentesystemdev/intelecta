import Pagination from '@/Components/Pagination';
import {
    EmptyInstitutional,
    InstitutionalBanner,
    InstitutionalStatus,
    cardClass,
    primaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, FileChartColumn, GraduationCap, Search, School } from 'lucide-react';
import { useMemo, useState } from 'react';

const fullName = (postulante) =>
    `${postulante.nombres_post || ''} ${postulante.apellidos_post || ''}`.trim();

export default function Index({ fichas, programas = [], grupos = [], filtros = {} }) {
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
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
        router.get(route('admin.institucional.ficha.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AdminLayout
            title="Ficha Académica"
            subtitle="Consulta institucional consolidada de postulantes, inscripción y seguimiento."
            wide
        >
            <Head title="Ficha Académica" />
            <InstitutionalBanner
                eyebrow="Seguimiento institucional"
                title="Fichas Académicas de Postulantes"
                description="Consulte la trayectoria académica consolidada de cada postulante, su programa, grupo y estado de seguimiento."
                icon={FileChartColumn}
            />

            <form
                onSubmit={submit}
                className={`${cardClass} mb-5 grid gap-3 p-4 lg:grid-cols-[minmax(240px,1fr)_1fr_1fr_220px_auto]`}
            >
                <label className="relative block">
                    <span className="sr-only">Buscar postulante</span>
                    <Search className="absolute left-3 top-3 h-4 w-4 text-text-muted" />
                    <input
                        className="h-10 w-full rounded-xl border border-brand-border bg-brand-card pl-9 pr-3 text-sm text-text-main outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary/15"
                        placeholder="Nombre, apellido, C.I. o correo"
                        value={filters.buscar}
                        onChange={(event) => setFilters({ ...filters, buscar: event.target.value })}
                    />
                </label>
                <select
                    className="h-10 rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.id_prog}
                    onChange={(event) =>
                        setFilters({ ...filters, id_prog: event.target.value, id_grupo: '' })
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
                    className="h-10 rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.id_grupo}
                    onChange={(event) => setFilters({ ...filters, id_grupo: event.target.value })}
                >
                    <option value="">Todos los grupos</option>
                    {filteredGroups.map((grupo) => (
                        <option key={grupo.id_grupo} value={grupo.id_grupo}>
                            {grupo.codigo_grupo || grupo.nombre_grupo}
                        </option>
                    ))}
                </select>
                <select
                    className="h-10 rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.nivel_riesgo_rend}
                    onChange={(event) =>
                        setFilters({ ...filters, nivel_riesgo_rend: event.target.value })
                    }
                >
                    <option value="">Todos los seguimientos</option>
                    <option value="Alto rendimiento">Alto rendimiento</option>
                    <option value="Seguimiento regular">Seguimiento regular</option>
                    <option value="Atención prioritaria">Atención prioritaria</option>
                </select>
                <button className={primaryButtonClass}>Buscar</button>
            </form>

            {fichas.data.length ? (
                <>
                    <div className="hidden overflow-hidden rounded-2xl border border-brand-border bg-brand-card shadow-sm lg:block">
                        <table className="w-full table-fixed">
                            <thead className="border-b border-brand-border bg-brand-bg">
                                <tr>
                                    {[
                                        ['Postulante', 'w-[24%]'],
                                        ['Colegio', 'w-[18%]'],
                                        ['Carrera objetivo', 'w-[20%]'],
                                        ['Programa / grupo', 'w-[22%]'],
                                        ['Seguimiento', 'w-[11%]'],
                                        ['Ficha', 'w-[5%]'],
                                    ].map(([label, width]) => (
                                        <th
                                            key={label}
                                            className={`${width} px-5 py-4 text-left text-[10px] font-bold uppercase tracking-wider text-text-muted`}
                                        >
                                            {label}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-brand-border">
                                {fichas.data.map((postulante) => (
                                    <tr key={postulante.id_post} className="transition hover:bg-brand-bg/70">
                                        <td className="px-5 py-4 align-top">
                                            <p className="break-words text-sm font-bold leading-snug text-text-main">
                                                {fullName(postulante)}
                                            </p>
                                            <p className="mt-1 break-words text-xs text-text-muted">
                                                {postulante.ci_post
                                                    ? `C.I. ${postulante.ci_post}`
                                                    : postulante.email_post || 'Sin identificación registrada'}
                                            </p>
                                        </td>
                                        <td className="px-5 py-4 align-top text-xs leading-5 text-text-muted">
                                            {postulante.colegio?.nombre_col || 'Colegio no registrado'}
                                        </td>
                                        <td className="px-5 py-4 align-top">
                                            <p className="break-words text-xs font-semibold leading-5 text-text-main">
                                                {postulante.carrera?.nombre_car || 'Carrera no registrada'}
                                            </p>
                                            <p className="mt-1 text-xs text-text-muted">
                                                {postulante.carrera?.universidad?.sigla_uni ||
                                                    postulante.carrera?.universidad?.nombre_uni ||
                                                    'Universidad no registrada'}
                                            </p>
                                        </td>
                                        <td className="px-5 py-4 align-top">
                                            <p className="break-words text-xs font-semibold leading-5 text-text-main">
                                                {postulante.inscripcion?.programa?.nombre_prog || 'Sin programa'}
                                            </p>
                                            <p className="mt-1 text-xs text-text-muted">
                                                {postulante.inscripcion?.grupo?.codigo_grupo ||
                                                    postulante.inscripcion?.grupo?.nombre_grupo ||
                                                    'Sin grupo asignado'}
                                            </p>
                                        </td>
                                        <td className="px-5 py-4 align-top">
                                            <InstitutionalStatus
                                                status={
                                                    postulante.rendimiento?.nivel_riesgo_rend ||
                                                    'Sin seguimiento'
                                                }
                                            />
                                        </td>
                                        <td className="px-3 py-4 text-center align-top">
                                            <Link
                                                href={route(
                                                    'admin.institucional.ficha.postulante',
                                                    postulante.id_post,
                                                )}
                                                className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-brand-border text-brand-secondary transition hover:bg-brand-secondary hover:text-white"
                                                aria-label={`Ver ficha de ${fullName(postulante)}`}
                                            >
                                                <ArrowRight className="h-4 w-4" />
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="grid gap-4 lg:hidden">
                        {fichas.data.map((postulante) => (
                            <article key={postulante.id_post} className={`${cardClass} p-5`}>
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="break-words text-base font-black leading-snug text-text-main">
                                            {fullName(postulante)}
                                        </p>
                                        <p className="mt-1 text-xs text-text-muted">
                                            {postulante.ci_post
                                                ? `C.I. ${postulante.ci_post}`
                                                : postulante.email_post || 'Sin identificación registrada'}
                                        </p>
                                    </div>
                                    <InstitutionalStatus
                                        status={
                                            postulante.rendimiento?.nivel_riesgo_rend ||
                                            'Sin seguimiento'
                                        }
                                    />
                                </div>
                                <div className="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div className="rounded-xl bg-brand-bg p-3">
                                        <p className="flex items-center gap-2 text-xs font-bold text-text-main">
                                            <School className="h-4 w-4 text-brand-secondary" />
                                            Colegio
                                        </p>
                                        <p className="mt-1 text-xs leading-5 text-text-muted">
                                            {postulante.colegio?.nombre_col || 'No registrado'}
                                        </p>
                                    </div>
                                    <div className="rounded-xl bg-brand-bg p-3">
                                        <p className="flex items-center gap-2 text-xs font-bold text-text-main">
                                            <GraduationCap className="h-4 w-4 text-brand-secondary" />
                                            Programa y grupo
                                        </p>
                                        <p className="mt-1 text-xs leading-5 text-text-muted">
                                            {postulante.inscripcion?.programa?.nombre_prog || 'Sin programa'}
                                            {' · '}
                                            {postulante.inscripcion?.grupo?.codigo_grupo || 'Sin grupo'}
                                        </p>
                                    </div>
                                </div>
                                <Link
                                    href={route(
                                        'admin.institucional.ficha.postulante',
                                        postulante.id_post,
                                    )}
                                    className={`${primaryButtonClass} mt-4 w-full`}
                                >
                                    Ver ficha
                                    <ArrowRight className="h-4 w-4" />
                                </Link>
                            </article>
                        ))}
                    </div>
                    <Pagination links={fichas.links} />
                </>
            ) : (
                <EmptyInstitutional
                    title="No se encontraron fichas académicas"
                    description="Ajuste los criterios de búsqueda o registre postulantes para consolidar su seguimiento institucional."
                />
            )}
        </AdminLayout>
    );
}
