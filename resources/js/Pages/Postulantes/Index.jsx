import ConfirmModal from '@/Components/ConfirmModal';
import ModalInstitucional from '@/Components/ModalInstitucional';
import PostulanteForm from '@/Components/Postulantes/PostulanteForm';
import StatusBadge from '@/Components/StatusBadge';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
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
    Building2,
    Mail,
    Phone,
    UserRound,
} from 'lucide-react';
import { useMemo, useState } from 'react';

function DetailField({ label, value }) {
    return (
        <div>
            <dt className="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                {label}
            </dt>
            <dd className="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200">
                {value || 'No registrado'}
            </dd>
        </div>
    );
}

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
    const [formModal, setFormModal] = useState({
        open: false,
        postulante: null,
    });
    const [detailModal, setDetailModal] = useState({
        open: false,
        postulante: null,
    });
    const [statusModal, setStatusModal] = useState({
        open: false,
        postulante: null,
    });
    const [changingStatus, setChangingStatus] = useState(false);

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

    const changeStatus = () => {
        if (!statusModal.postulante) {
            return;
        }

        setChangingStatus(true);
        router.patch(
            route(
                'postulantes.cambiar-estado',
                statusModal.postulante.id_post,
            ),
            {},
            {
                preserveScroll: true,
                onSuccess: () =>
                    setStatusModal({ open: false, postulante: null }),
                onFinish: () => setChangingStatus(false),
            },
        );
    };

    const openCreateModal = () => {
        setFormModal({ open: true, postulante: null });
    };

    const openEditModal = (postulante) => {
        setFormModal({ open: true, postulante });
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
                        type="button"
                        className="h-10 bg-indigo-700 px-4 hover:bg-indigo-800"
                        onClick={openCreateModal}
                    >
                        <Plus className="h-4 w-4" />
                        Nuevo postulante
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
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label="Ver postulante"
                                                    onClick={() => setDetailModal({ open: true, postulante })}
                                                >
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                                {permisos.editar && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        aria-label="Editar postulante"
                                                        onClick={() =>
                                                            openEditModal(
                                                                postulante,
                                                            )
                                                        }
                                                    >
                                                        <Pencil className="h-4 w-4" />
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
                                                            setStatusModal({
                                                                open: true,
                                                                postulante,
                                                            })
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

            <ModalInstitucional
                open={formModal.open}
                onOpenChange={(open) =>
                    setFormModal((current) => ({ ...current, open }))
                }
                title={
                    formModal.postulante
                        ? 'Editar postulante'
                        : 'Registrar postulante'
                }
                description={
                    formModal.postulante
                        ? 'Actualice la información personal y académica del postulante.'
                        : 'Incorpore un postulante al proceso académico preuniversitario.'
                }
                size="xl"
            >
                {formModal.open && (
                    <PostulanteForm
                        key={
                            formModal.postulante
                                ? `edit-${formModal.postulante.id_post}`
                                : 'create-postulante'
                        }
                        postulante={formModal.postulante}
                        opciones={opciones}
                        gestionActual={new Date().getFullYear()}
                        submitRoute={
                            formModal.postulante
                                ? route(
                                      'postulantes.update',
                                      formModal.postulante.id_post,
                                  )
                                : route('postulantes.store')
                        }
                        method={formModal.postulante ? 'put' : 'post'}
                        submitLabel={
                            formModal.postulante
                                ? 'Guardar cambios'
                                : 'Registrar postulante'
                        }
                        onCancel={() =>
                            setFormModal({
                                open: false,
                                postulante: null,
                            })
                        }
                    />
                )}
            </ModalInstitucional>

            <ModalInstitucional
                open={detailModal.open}
                onOpenChange={(open) =>
                    setDetailModal((current) => ({ ...current, open }))
                }
                title="Detalle del Postulante"
                description="Información académica y personal registrada en el sistema."
                size="xl"
            >
                {detailModal.postulante && (
                    <div className="space-y-6">
                        {/* Tarjeta de cabecera con gradiente */}
                        <div className="rounded-2xl bg-gradient-to-r from-indigo-800 to-blue-700 p-6 text-white shadow-lg dark:from-indigo-950 dark:to-blue-900">
                            <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                                <div className="flex items-center gap-4">
                                    <span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                                        <UserRound className="h-7 w-7" />
                                    </span>
                                    <div>
                                        <h3 className="text-xl font-bold">
                                            {detailModal.postulante.nombres_post} {detailModal.postulante.apellidos_post}
                                        </h3>
                                        <p className="mt-1 text-sm text-indigo-100">
                                            C.I. {detailModal.postulante.ci_post || 'no registrado'} · Gestión {detailModal.postulante.gestion_post}
                                        </p>
                                    </div>
                                </div>
                                <StatusBadge
                                    status={
                                        detailModal.postulante.estado_post === 'activo'
                                            ? 'Activo'
                                            : 'Inactivo'
                                    }
                                />
                            </div>
                        </div>

                        {/* Grid con información */}
                        <div className="grid gap-6 lg:grid-cols-2">
                            {/* Información personal */}
                            <Card className="border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50">
                                <CardHeader className="border-b border-slate-100 dark:border-slate-800/60 p-5">
                                    <CardTitle className="flex items-center gap-2 text-base font-semibold">
                                        <UserRound className="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                        Información personal
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-5">
                                    <dl className="grid gap-5 sm:grid-cols-2">
                                        <DetailField label="Nombres" value={detailModal.postulante.nombres_post} />
                                        <DetailField label="Apellidos" value={detailModal.postulante.apellidos_post} />
                                        <DetailField label="Edad" value={detailModal.postulante.edad_post} />
                                        <DetailField label="Celular" value={detailModal.postulante.celular_post} />
                                        <div className="sm:col-span-2">
                                            <DetailField label="Correo electrónico" value={detailModal.postulante.email_post} />
                                        </div>
                                    </dl>
                                    <div className="mt-5 flex flex-wrap gap-3 text-xs text-slate-500">
                                        {detailModal.postulante.email_post && (
                                            <span className="flex items-center gap-1.5">
                                                <Mail className="h-3.5 w-3.5" />
                                                Contacto académico
                                            </span>
                                        )}
                                        {detailModal.postulante.celular_post && (
                                            <span className="flex items-center gap-1.5">
                                                <Phone className="h-3.5 w-3.5" />
                                                Contacto telefónico
                                            </span>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Información académica */}
                            <Card className="border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50">
                                <CardHeader className="border-b border-slate-100 dark:border-slate-800/60 p-5">
                                    <CardTitle className="flex items-center gap-2 text-base font-semibold">
                                        <GraduationCap className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                        Información académica
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-5">
                                    <dl className="grid gap-5">
                                        <DetailField label="Colegio de procedencia" value={detailModal.postulante.colegio?.nombre_col} />
                                        <DetailField
                                            label="Universidad postulada"
                                            value={
                                                detailModal.postulante.carrera?.universidad
                                                    ? `${detailModal.postulante.carrera.universidad.sigla_uni || ''}${detailModal.postulante.carrera.universidad.sigla_uni ? ' - ' : ''}${detailModal.postulante.carrera.universidad.nombre_uni}`
                                                    : null
                                            }
                                        />
                                        <DetailField label="Carrera postulada" value={detailModal.postulante.carrera?.nombre_car} />
                                        <div className="grid gap-5 sm:grid-cols-2">
                                            <DetailField label="Tipo de universidad" value={detailModal.postulante.carrera?.universidad?.tipo_uni} />
                                            <DetailField label="Exigencia matemática institucional" value={detailModal.postulante.carrera?.universidad?.nivel_exigencia_matematica_uni} />
                                        </div>
                                        <DetailField label="Exigencia matemática de la carrera" value={detailModal.postulante.carrera?.nivel_exigencia_matematica_car} />
                                        <div className="grid gap-5 sm:grid-cols-2">
                                            <DetailField label="Turno" value={detailModal.postulante.turno_post} />
                                            <DetailField label="Gestión" value={detailModal.postulante.gestion_post} />
                                        </div>
                                    </dl>
                                    <div className="mt-5 flex items-center gap-2 text-xs text-slate-500">
                                        <Building2 className="h-3.5 w-3.5" />
                                        Registro del proceso preuniversitario
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Observaciones */}
                        <Card className="border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50">
                            <CardHeader className="border-b border-slate-100 dark:border-slate-800/60 p-5">
                                <CardTitle className="text-base font-semibold">Observaciones</CardTitle>
                            </CardHeader>
                            <CardContent className="p-5">
                                <p className="whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    {detailModal.postulante.observaciones_post || 'No se registraron observaciones para este postulante.'}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </ModalInstitucional>

            <ConfirmModal
                open={statusModal.open}
                onOpenChange={(open) =>
                    setStatusModal((current) => ({ ...current, open }))
                }
                title={
                    statusModal.postulante?.estado_post === 'activo'
                        ? 'Inactivar postulante'
                        : 'Activar postulante'
                }
                message={
                    statusModal.postulante
                        ? `¿Confirma que desea ${
                              statusModal.postulante.estado_post === 'activo'
                                  ? 'inactivar'
                                  : 'activar'
                          } a ${statusModal.postulante.nombres_post} ${statusModal.postulante.apellidos_post}?`
                        : ''
                }
                confirmLabel={
                    statusModal.postulante?.estado_post === 'activo'
                        ? 'Inactivar'
                        : 'Activar'
                }
                variant={
                    statusModal.postulante?.estado_post === 'activo'
                        ? 'danger'
                        : 'normal'
                }
                processing={changingStatus}
                onConfirm={changeStatus}
            />
        </AdminLayout>
    );
}
