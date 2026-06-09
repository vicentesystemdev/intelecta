import StatusBadge from '@/Components/StatusBadge';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    Eye,
    GraduationCap,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    UserCheck,
    UserX,
} from 'lucide-react';
import { useMemo, useState } from 'react';

export default function Index({
    postulantes,
    filtros = {},
    opciones,
    permisos,
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
        id_col: filtros.id_col || '',
        id_uni: filtros.id_uni || '',
        id_car: filtros.id_car || '',
        estado_post: filtros.estado_post || '',
    });

    const submitFilters = (event) => {
        event.preventDefault();
        router.get(route('postulantes.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        setFilters({
            buscar: '',
            id_col: '',
            id_uni: '',
            id_car: '',
            estado_post: '',
        });
        router.get(route('postulantes.index'), {}, { replace: true });
    };

    const carrerasDisponibles = useMemo(
        () =>
            opciones.carreras.filter(
                (carrera) =>
                    !filters.id_uni ||
                    String(carrera.id_uni) === String(filters.id_uni),
            ),
        [filters.id_uni, opciones.carreras],
    );

    const changeUniversidad = (value) => {
        const carreraActual = opciones.carreras.find(
            (carrera) => String(carrera.id_car) === String(filters.id_car),
        );

        setFilters({
            ...filters,
            id_uni: value,
            id_car:
                carreraActual &&
                String(carreraActual.id_uni) === String(value)
                    ? filters.id_car
                    : '',
        });
    };

    const changeStatus = (postulante) => {
        const action =
            postulante.estado_post === 'activo' ? 'inactivar' : 'activar';

        if (!window.confirm(`¿Confirma que desea ${action} a este postulante?`)) {
            return;
        }

        router.patch(
            route('postulantes.cambiar-estado', postulante.id_post),
            {},
            { preserveScroll: true },
        );
    };

    return (
        <AdminLayout
            title="Gestión de Postulantes"
            subtitle="Registro y seguimiento académico de postulantes preuniversitarios."
        >
            <Head title="Postulantes" />

            <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <p className="text-sm text-slate-500">
                        {postulantes.total} postulantes registrados
                    </p>
                </div>
                {permisos.crear && (
                    <Button
                        asChild
                        className="h-10 bg-indigo-700 px-4 hover:bg-indigo-800"
                    >
                        <Link href={route('postulantes.create')}>
                            <Plus className="h-4 w-4" />
                            Nuevo postulante
                        </Link>
                    </Button>
                )}
            </div>

            {flash?.success && (
                <Alert className="mb-5 border-emerald-200 bg-emerald-50 text-emerald-800">
                    <UserCheck className="h-4 w-4" />
                    <AlertDescription className="text-emerald-700">
                        {flash.success}
                    </AlertDescription>
                </Alert>
            )}

            <Card className="mb-6 border-0 bg-white py-0 shadow-sm ring-1 ring-slate-200/80">
                <CardContent className="p-4">
                    <form
                        onSubmit={submitFilters}
                        className="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(220px,1.3fr)_1fr_1fr_1fr_150px_auto]"
                    >
                        <div className="relative">
                            <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                            <Input
                                className="h-10 bg-white pl-9"
                                placeholder="Buscar por nombre, CI o correo"
                                value={filters.buscar}
                                onChange={(event) =>
                                    setFilters({
                                        ...filters,
                                        buscar: event.target.value,
                                    })
                                }
                            />
                        </div>
                        <select
                            className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={filters.id_col}
                            onChange={(event) =>
                                setFilters({
                                    ...filters,
                                    id_col: event.target.value,
                                })
                            }
                        >
                            <option value="">Todos los colegios</option>
                            {opciones.colegios.map((colegio) => (
                                <option
                                    key={colegio.id_col}
                                    value={colegio.id_col}
                                >
                                    {colegio.nombre_col}
                                </option>
                            ))}
                        </select>
                        <select
                            className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={filters.id_uni}
                            onChange={(event) =>
                                changeUniversidad(event.target.value)
                            }
                        >
                            <option value="">Todas las universidades</option>
                            {opciones.universidades.map((universidad) => (
                                <option
                                    key={universidad.id_uni}
                                    value={universidad.id_uni}
                                >
                                    {universidad.sigla_uni ||
                                        universidad.nombre_uni}
                                </option>
                            ))}
                        </select>
                        <select
                            className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={filters.id_car}
                            onChange={(event) =>
                                setFilters({
                                    ...filters,
                                    id_car: event.target.value,
                                })
                            }
                        >
                            <option value="">Todas las carreras</option>
                            {carrerasDisponibles.map((carrera) => (
                                <option
                                    key={carrera.id_car}
                                    value={carrera.id_car}
                                >
                                    {carrera.nombre_car}
                                    {!filters.id_uni &&
                                        carrera.universidad?.sigla_uni &&
                                        ` · ${carrera.universidad.sigla_uni}`}
                                </option>
                            ))}
                        </select>
                        <select
                            className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={filters.estado_post}
                            onChange={(event) =>
                                setFilters({
                                    ...filters,
                                    estado_post: event.target.value,
                                })
                            }
                        >
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <div className="flex gap-2">
                            <Button type="submit" className="h-10 bg-indigo-700">
                                Filtrar
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                className="h-10 w-10"
                                onClick={clearFilters}
                                aria-label="Limpiar filtros"
                            >
                                <RotateCcw className="h-4 w-4" />
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card className="border-0 bg-white py-0 shadow-sm ring-1 ring-slate-200/80">
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-slate-50/80">
                                <TableHead className="pl-5">Postulante</TableHead>
                                <TableHead>Colegio</TableHead>
                                <TableHead>Universidad</TableHead>
                                <TableHead>Carrera postulada</TableHead>
                                <TableHead>Edad</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="pr-5 text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {postulantes.data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan="7"
                                        className="h-40 text-center text-slate-500"
                                    >
                                        <GraduationCap className="mx-auto mb-2 h-8 w-8 text-slate-300" />
                                        No se encontraron postulantes con los filtros
                                        seleccionados.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                postulantes.data.map((postulante) => (
                                    <TableRow key={postulante.id_post}>
                                        <TableCell className="pl-5">
                                            <p className="font-semibold text-slate-900">
                                                {postulante.apellidos_post},{' '}
                                                {postulante.nombres_post}
                                            </p>
                                            <p className="text-xs text-slate-500">
                                                {postulante.email_post ||
                                                    'Sin correo registrado'}
                                            </p>
                                        </TableCell>
                                        <TableCell className="max-w-52 truncate text-slate-600">
                                            {postulante.colegio?.nombre_col ||
                                                'Sin asignar'}
                                        </TableCell>
                                        <TableCell className="text-slate-600">
                                            {postulante.carrera?.universidad
                                                ?.sigla_uni ||
                                                postulante.carrera?.universidad
                                                    ?.nombre_uni ||
                                                'Sin asignar'}
                                        </TableCell>
                                        <TableCell className="max-w-52 truncate text-slate-600">
                                            {postulante.carrera?.nombre_car ||
                                                'Sin asignar'}
                                        </TableCell>
                                        <TableCell>
                                            {postulante.edad_post || '—'}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge
                                                status={
                                                    postulante.estado_post ===
                                                    'activo'
                                                        ? 'Activo'
                                                        : 'Inactivo'
                                                }
                                            />
                                        </TableCell>
                                        <TableCell className="pr-5">
                                            <div className="flex justify-end gap-1">
                                                <Button
                                                    asChild
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label="Ver postulante"
                                                >
                                                    <Link
                                                        href={route(
                                                            'postulantes.show',
                                                            postulante.id_post,
                                                        )}
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                {permisos.editar && (
                                                    <Button
                                                        asChild
                                                        variant="ghost"
                                                        size="icon"
                                                        aria-label="Editar postulante"
                                                    >
                                                        <Link
                                                            href={route(
                                                                'postulantes.edit',
                                                                postulante.id_post,
                                                            )}
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                )}
                                                {permisos.cambiarEstado && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        className={
                                                            postulante.estado_post ===
                                                            'activo'
                                                                ? 'text-rose-600 hover:bg-rose-50 hover:text-rose-700'
                                                                : 'text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700'
                                                        }
                                                        onClick={() =>
                                                            changeStatus(postulante)
                                                        }
                                                        aria-label={
                                                            postulante.estado_post ===
                                                            'activo'
                                                                ? 'Inactivar postulante'
                                                                : 'Activar postulante'
                                                        }
                                                    >
                                                        {postulante.estado_post ===
                                                        'activo' ? (
                                                            <UserX className="h-4 w-4" />
                                                        ) : (
                                                            <UserCheck className="h-4 w-4" />
                                                        )}
                                                    </Button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            {postulantes.links.length > 3 && (
                <div className="mt-5 flex flex-wrap justify-center gap-1">
                    {postulantes.links.map((link, index) => (
                        <Button
                            key={`${link.label}-${index}`}
                            asChild={Boolean(link.url)}
                            disabled={!link.url}
                            variant={link.active ? 'default' : 'outline'}
                            size="sm"
                            className={link.active ? 'bg-indigo-700' : ''}
                        >
                            {link.url ? (
                                <Link
                                    href={link.url}
                                    preserveScroll
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ) : (
                                <span
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            )}
                        </Button>
                    ))}
                </div>
            )}
        </AdminLayout>
    );
}
