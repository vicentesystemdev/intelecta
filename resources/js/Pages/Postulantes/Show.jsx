import ConfirmModal from '@/Components/ConfirmModal';
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
import { useState } from 'react';

function Detail({ label, value }) {
    return (
        <div>
            <dt className="text-xs font-semibold uppercase tracking-wider text-text-muted">
                {label}
            </dt>
            <dd className="mt-1 text-sm font-medium text-text-main">
                {value || 'No registrado'}
            </dd>
        </div>
    );
}

export default function Show({ postulante, permisos }) {
    const { flash } = usePage().props;
    const fullName = `${postulante.nombres_post} ${postulante.apellidos_post}`;
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [changingStatus, setChangingStatus] = useState(false);

    const changeStatus = () => {
        setChangingStatus(true);
        router.patch(
            route('postulantes.cambiar-estado', postulante.id_post),
            {},
            {
                preserveScroll: true,
                onSuccess: () => setConfirmOpen(false),
                onFinish: () => setChangingStatus(false),
            },
        );
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
                                onClick={() => setConfirmOpen(true)}
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

                <Card className="mb-6 border-0 bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary py-0 text-white shadow-lg shadow-brand-primary/20 dark:shadow-brand-primary/40">
                    <CardContent className="flex flex-col justify-between gap-5 p-6 sm:flex-row sm:items-center">
                        <div className="flex items-center gap-4">
                            <span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                                <UserRound className="h-7 w-7" />
                            </span>
                            <div>
                                <h2 className="text-2xl font-bold">{fullName}</h2>
                                <p className="mt-1 text-sm text-slate-200">
                                    C.I. {postulante.ci_post || 'no registrado'} ·
                                    Gestión {postulante.gestion_post}
                                </p>
                            </div>
                        </div>
                        <span className={`rounded-full px-2.5 py-0.5 text-xs font-semibold border ${
                            postulante.estado_post === 'activo'
                                ? 'bg-brand-success/10 text-brand-success border-brand-success/20'
                                : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
                        }`}>
                            {postulante.estado_post === 'activo' ? 'Activo' : 'Inactivo'}
                        </span>
                    </CardContent>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card className="bg-brand-card border border-brand-border rounded-2xl">
                        <CardHeader className="border-b border-brand-border p-5">
                            <CardTitle className="flex items-center gap-2 text-lg text-text-main">
                                <UserRound className="h-5 w-5 text-brand-secondary" />
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
                            <div className="mt-5 flex flex-wrap gap-3 text-xs text-text-muted">
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

                    <Card className="bg-brand-card border border-brand-border rounded-2xl">
                        <CardHeader className="border-b border-brand-border p-5">
                            <CardTitle className="flex items-center gap-2 text-lg text-text-main">
                                <GraduationCap className="h-5 w-5 text-brand-secondary" />
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
                            <div className="mt-5 flex items-center gap-2 text-xs text-text-muted">
                                <Building2 className="h-3.5 w-3.5" />
                                Registro del proceso preuniversitario
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card className="mt-6 bg-brand-card border border-brand-border rounded-2xl">
                    <CardHeader className="border-b border-brand-border p-5">
                        <CardTitle className="text-lg text-text-main">Observaciones</CardTitle>
                    </CardHeader>
                    <CardContent className="p-5">
                        <p className="whitespace-pre-line text-sm leading-6 text-text-muted">
                            {postulante.observaciones_post ||
                                'No se registraron observaciones para este postulante.'}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <ConfirmModal
                open={confirmOpen}
                onOpenChange={setConfirmOpen}
                title={
                    postulante.estado_post === 'activo'
                        ? 'Inactivar postulante'
                        : 'Activar postulante'
                }
                message={`¿Confirma que desea ${
                    postulante.estado_post === 'activo'
                        ? 'inactivar'
                        : 'activar'
                } a ${fullName}?`}
                confirmLabel={
                    postulante.estado_post === 'activo'
                        ? 'Inactivar'
                        : 'Activar'
                }
                variant={
                    postulante.estado_post === 'activo' ? 'danger' : 'normal'
                }
                processing={changingStatus}
                onConfirm={changeStatus}
            />
        </AdminLayout>
    );
}
