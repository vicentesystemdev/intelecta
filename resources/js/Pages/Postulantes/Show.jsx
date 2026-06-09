import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    GraduationCap,
    Mail,
    Pencil,
    Phone,
    UserCheck,
    UserRound,
    UserX,
} from 'lucide-react';

function Detail({ label, value }) {
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

export default function Show({ postulante, permisos }) {
    const { flash } = usePage().props;
    const fullName = `${postulante.nombres_post} ${postulante.apellidos_post}`;

    const changeStatus = () => {
        const action =
            postulante.estado_post === 'activo' ? 'inactivar' : 'activar';
        if (window.confirm(`¿Confirma que desea ${action} a este postulante?`)) {
            router.patch(
                route('postulantes.cambiar-estado', postulante.id_post),
                {},
                { preserveScroll: true },
            );
        }
    };

    return (
        <AdminLayout
            title="Detalle del Postulante"
            subtitle="Información personal y académica consolidada."
        >
            <Head title={fullName} />

            <div className="mx-auto max-w-5xl">
                <div className="mb-5 flex flex-col justify-between gap-3 sm:flex-row">
                    <Button variant="outline" asChild className="self-start">
                        <Link href={route('postulantes.index')}>
                            <ArrowLeft className="h-4 w-4" />
                            Volver al listado
                        </Link>
                    </Button>
                    <div className="flex gap-2">
                        {permisos.editar && (
                            <Button variant="outline" asChild>
                                <Link
                                    href={route(
                                        'postulantes.edit',
                                        postulante.id_post,
                                    )}
                                >
                                    <Pencil className="h-4 w-4" />
                                    Editar
                                </Link>
                            </Button>
                        )}
                        {permisos.cambiarEstado && (
                            <Button
                                type="button"
                                variant="outline"
                                className={
                                    postulante.estado_post === 'activo'
                                        ? 'border-rose-200 text-rose-700 hover:bg-rose-50'
                                        : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'
                                }
                                onClick={changeStatus}
                            >
                                {postulante.estado_post === 'activo' ? (
                                    <UserX className="h-4 w-4" />
                                ) : (
                                    <UserCheck className="h-4 w-4" />
                                )}
                                {postulante.estado_post === 'activo'
                                    ? 'Inactivar'
                                    : 'Activar'}
                            </Button>
                        )}
                    </div>
                </div>

                {flash?.success && (
                    <div className="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                        {flash.success}
                    </div>
                )}

                <Card className="mb-6 border-0 bg-gradient-to-r from-indigo-800 to-blue-700 py-0 text-white shadow-lg shadow-indigo-200/50 dark:shadow-indigo-950/40">
                    <CardContent className="flex flex-col justify-between gap-5 p-6 sm:flex-row sm:items-center">
                        <div className="flex items-center gap-4">
                            <span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                                <UserRound className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-2xl font-bold">{fullName}</h2>
                                <p className="mt-1 text-sm text-indigo-100">
                                    C.I. {postulante.ci_post || 'no registrado'} ·
                                    Gestión {postulante.gestion_post}
                                </p>
                            </div>
                        </div>
                        <StatusBadge
                            status={
                                postulante.estado_post === 'activo'
                                    ? 'Activo'
                                    : 'Inactivo'
                            }
                        />
                    </CardContent>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="gap-0 border-0 bg-white py-0 shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader className="border-b border-slate-100 p-5">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <UserRound className="h-5 w-5 text-indigo-600" />
                                Información personal
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-5">
                            <dl className="grid gap-5 sm:grid-cols-2">
                                <Detail
                                    label="Nombres"
                                    value={postulante.nombres_post}
                                />
                                <Detail
                                    label="Apellidos"
                                    value={postulante.apellidos_post}
                                />
                                <Detail label="Edad" value={postulante.edad_post} />
                                <Detail
                                    label="Celular"
                                    value={postulante.celular_post}
                                />
                                <div className="sm:col-span-2">
                                    <Detail
                                        label="Correo electrónico"
                                        value={postulante.email_post}
                                    />
                                </div>
                            </dl>
                            <div className="mt-5 flex flex-wrap gap-3 text-xs text-slate-500">
                                {postulante.email_post && (
                                    <span className="flex items-center gap-1.5">
                                        <Mail className="h-3.5 w-3.5" />
                                        Contacto académico
                                    </span>
                                )}
                                {postulante.celular_post && (
                                    <span className="flex items-center gap-1.5">
                                        <Phone className="h-3.5 w-3.5" />
                                        Contacto telefónico
                                    </span>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="gap-0 border-0 bg-white py-0 shadow-sm ring-1 ring-slate-200/80">
                        <CardHeader className="border-b border-slate-100 p-5">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <GraduationCap className="h-5 w-5 text-blue-600" />
                                Información académica
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-5">
                            <dl className="grid gap-5">
                                <Detail
                                    label="Colegio de procedencia"
                                    value={postulante.colegio?.nombre_col}
                                />
                                <Detail
                                    label="Universidad postulada"
                                    value={
                                        postulante.carrera?.universidad
                                            ? `${
                                                  postulante.carrera.universidad
                                                      .sigla_uni || ''
                                              }${
                                                  postulante.carrera.universidad
                                                      .sigla_uni
                                                      ? ' - '
                                                      : ''
                                              }${
                                                  postulante.carrera.universidad
                                                      .nombre_uni
                                              }`
                                            : null
                                    }
                                />
                                <Detail
                                    label="Carrera postulada"
                                    value={postulante.carrera?.nombre_car}
                                />
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <Detail
                                        label="Tipo de universidad"
                                        value={
                                            postulante.carrera?.universidad
                                                ?.tipo_uni
                                        }
                                    />
                                    <Detail
                                        label="Exigencia matemática institucional"
                                        value={
                                            postulante.carrera?.universidad
                                                ?.nivel_exigencia_matematica_uni
                                        }
                                    />
                                </div>
                                <Detail
                                    label="Exigencia matemática de la carrera"
                                    value={
                                        postulante.carrera
                                            ?.nivel_exigencia_matematica_car
                                    }
                                />
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <Detail
                                        label="Turno"
                                        value={postulante.turno_post}
                                    />
                                    <Detail
                                        label="Gestión"
                                        value={postulante.gestion_post}
                                    />
                                </div>
                            </dl>
                            <div className="mt-5 flex items-center gap-2 text-xs text-slate-500">
                                <Building2 className="h-3.5 w-3.5" />
                                Registro del proceso preuniversitario
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card className="mt-6 gap-0 border-0 bg-white py-0 shadow-sm ring-1 ring-slate-200/80">
                    <CardHeader className="border-b border-slate-100 p-5">
                        <CardTitle className="text-lg">Observaciones</CardTitle>
                    </CardHeader>
                    <CardContent className="p-5">
                        <p className="whitespace-pre-line text-sm leading-6 text-slate-600">
                            {postulante.observaciones_post ||
                                'No se registraron observaciones para este postulante.'}
                        </p>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
