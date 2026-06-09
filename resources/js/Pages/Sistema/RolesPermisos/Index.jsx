import ConfirmModal from '@/Components/ConfirmModal';
import ModalInstitucional from '@/Components/ModalInstitucional';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, usePage } from '@inertiajs/react';
import {
    Eye,
    KeyRound,
    Pencil,
    ShieldCheck,
    ShieldEllipsis,
    Users,
} from 'lucide-react';
import { useMemo, useState } from 'react';

const groupLabels = {
    areas: 'Áreas de conocimiento',
    dashboard: 'Panel principal',
    docentes: 'Docentes',
    evaluaciones: 'Evaluaciones',
    general: 'General',
    learning_analytics: 'Learning Analytics',
    plantillas: 'Plantillas',
    postulantes: 'Postulantes',
    preguntas: 'Banco de Preguntas',
    reportes: 'Reportes Académicos',
    roles: 'Roles y Permisos',
    temas: 'Temas',
    usuarios: 'Usuarios',
};

function PermissionEditor({ role, grupos, onClose }) {
    const [selected, setSelected] = useState(role.permissions);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const togglePermission = (permission) => {
        setSelected((current) =>
            current.includes(permission)
                ? current.filter((item) => item !== permission)
                : [...current, permission],
        );
    };

    const save = () => {
        setProcessing(true);
        router.put(
            route('admin.sistema.roles-permisos.update', role.id),
            { permissions: selected },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setConfirmOpen(false);
                    onClose();
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <>
            <div className="space-y-5">
                <div className="rounded-2xl border border-indigo-100 bg-indigo-50 p-4 dark:border-indigo-900 dark:bg-indigo-950/30">
                    <p className="text-sm font-semibold text-indigo-900 dark:text-indigo-200">
                        {role.name}
                    </p>
                    <p className="mt-1 text-xs leading-5 text-indigo-700 dark:text-indigo-300">
                        Seleccione las capacidades que corresponden a este perfil
                        institucional.
                    </p>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    {Object.entries(grupos).map(([group, permissions]) => (
                        <section
                            key={group}
                            className="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <h3 className="mb-3 text-xs font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                                {groupLabels[group] ||
                                    group.replaceAll('_', ' ')}
                            </h3>
                            <div className="space-y-2">
                                {permissions.map((permission) => (
                                    <label
                                        key={permission.id}
                                        className="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 transition hover:border-indigo-300 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-indigo-700"
                                    >
                                        <input
                                            type="checkbox"
                                            className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950"
                                            checked={selected.includes(
                                                permission.name,
                                            )}
                                            onChange={() =>
                                                togglePermission(
                                                    permission.name,
                                                )
                                            }
                                        />
                                        <span>{permission.name}</span>
                                    </label>
                                ))}
                            </div>
                        </section>
                    ))}
                </div>

                <div className="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 dark:border-slate-800 sm:flex-row sm:justify-end">
                    <Button
                        type="button"
                        variant="outline"
                        className="h-10"
                        onClick={onClose}
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="button"
                        className="h-10 bg-indigo-700 px-5 text-white hover:bg-indigo-800"
                        onClick={() => setConfirmOpen(true)}
                    >
                        <ShieldCheck className="h-4 w-4" />
                        Revisar y guardar
                    </Button>
                </div>
            </div>

            <ConfirmModal
                open={confirmOpen}
                onOpenChange={setConfirmOpen}
                title="Confirmar actualización de permisos"
                message={`Se sincronizarán ${selected.length} permisos para el rol ${role.name}.`}
                confirmLabel="Guardar permisos"
                processing={processing}
                supportingText="La matriz seleccionada reemplazará las capacidades actuales del rol."
                onConfirm={save}
            />
        </>
    );
}

export default function Index({ roles, permisosAgrupados, puedeEditar }) {
    const { flash, errors } = usePage().props;
    const [detailRole, setDetailRole] = useState(null);
    const [editRole, setEditRole] = useState(null);
    const totalPermissions = useMemo(
        () =>
            Object.values(permisosAgrupados).reduce(
                (total, group) => total + group.length,
                0,
            ),
        [permisosAgrupados],
    );

    return (
        <AdminLayout
            title="Roles y Permisos"
            subtitle="Control de perfiles institucionales y autorización por módulos."
        >
            <Head title="Roles y Permisos" />

            {flash?.success && (
                <Alert className="mb-5 border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
                    <ShieldCheck className="h-4 w-4" />
                    <AlertDescription>{flash.success}</AlertDescription>
                </Alert>
            )}
            {errors?.permissions && (
                <Alert className="mb-5 border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200">
                    <ShieldEllipsis className="h-4 w-4" />
                    <AlertDescription>{errors.permissions}</AlertDescription>
                </Alert>
            )}

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <Card className="border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:ring-slate-800">
                    <CardContent className="flex items-center gap-4 p-5">
                        <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            <ShieldCheck className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="text-2xl font-black">{roles.length}</p>
                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                Roles institucionales
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card className="border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:ring-slate-800">
                    <CardContent className="flex items-center gap-4 p-5">
                        <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                            <KeyRound className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="text-2xl font-black">
                                {totalPermissions}
                            </p>
                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                Permisos disponibles
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card className="border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:ring-slate-800">
                    <CardContent className="flex items-center gap-4 p-5">
                        <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300">
                            <Users className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="text-2xl font-black">
                                {roles.reduce(
                                    (total, role) =>
                                        total + role.users_count,
                                    0,
                                )}
                            </p>
                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                Asignaciones de usuario
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-5 lg:grid-cols-2">
                {roles.map((role) => (
                    <Card
                        key={role.id}
                        className="overflow-hidden border border-slate-200 bg-white py-0 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div className="h-1.5 bg-gradient-to-r from-indigo-700 via-blue-500 to-cyan-400" />
                        <CardContent className="p-5">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h2 className="text-lg font-bold text-slate-900 dark:text-slate-100">
                                            {role.name}
                                        </h2>
                                        {role.protected && (
                                            <Badge
                                                variant="outline"
                                                className="border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300"
                                            >
                                                Rol protegido
                                            </Badge>
                                        )}
                                    </div>
                                    <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                        Perfil institucional con autorización
                                        centralizada por módulos.
                                    </p>
                                </div>
                                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                                    <ShieldCheck className="h-5 w-5" />
                                </span>
                            </div>

                            <div className="mt-5 grid grid-cols-2 gap-3">
                                <div className="rounded-xl bg-slate-50 p-3 dark:bg-slate-950/60">
                                    <p className="text-xl font-bold text-slate-900 dark:text-slate-100">
                                        {role.users_count}
                                    </p>
                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                        Usuarios asociados
                                    </p>
                                </div>
                                <div className="rounded-xl bg-slate-50 p-3 dark:bg-slate-950/60">
                                    <p className="text-xl font-bold text-slate-900 dark:text-slate-100">
                                        {role.permissions_count}
                                    </p>
                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                        Permisos asignados
                                    </p>
                                </div>
                            </div>

                            <div className="mt-5 flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-800">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setDetailRole(role)}
                                >
                                    <Eye className="h-4 w-4" />
                                    Ver detalle
                                </Button>
                                {puedeEditar && role.editable && (
                                    <Button
                                        type="button"
                                        className="bg-indigo-700 text-white hover:bg-indigo-800"
                                        onClick={() => setEditRole(role)}
                                    >
                                        <Pencil className="h-4 w-4" />
                                        Editar permisos
                                    </Button>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                ))}
            </div>

            <ModalInstitucional
                open={Boolean(detailRole)}
                onOpenChange={(open) => !open && setDetailRole(null)}
                title={detailRole ? `Rol: ${detailRole.name}` : 'Detalle del rol'}
                description="Usuarios asociados y capacidades actualmente autorizadas."
                size="lg"
            >
                {detailRole && (
                    <div className="grid gap-5 lg:grid-cols-2">
                        <section>
                            <h3 className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Usuarios asociados
                            </h3>
                            <div className="space-y-2">
                                {detailRole.users.length ? (
                                    detailRole.users.map((user) => (
                                        <div
                                            key={user.id}
                                            className="rounded-xl border border-slate-200 p-3 dark:border-slate-800"
                                        >
                                            <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                {user.name}
                                            </p>
                                            <p className="text-xs text-slate-500">
                                                {user.email}
                                            </p>
                                        </div>
                                    ))
                                ) : (
                                    <p className="rounded-xl bg-slate-50 p-4 text-sm text-slate-500 dark:bg-slate-950">
                                        No existen usuarios asociados.
                                    </p>
                                )}
                            </div>
                        </section>
                        <section>
                            <h3 className="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Permisos asignados
                            </h3>
                            <div className="flex flex-wrap gap-2">
                                {detailRole.permissions.map((permission) => (
                                    <Badge
                                        key={permission}
                                        variant="outline"
                                        className="border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-300"
                                    >
                                        {permission}
                                    </Badge>
                                ))}
                            </div>
                        </section>
                    </div>
                )}
            </ModalInstitucional>

            <ModalInstitucional
                open={Boolean(editRole)}
                onOpenChange={(open) => !open && setEditRole(null)}
                title={
                    editRole
                        ? `Permisos de ${editRole.name}`
                        : 'Editar permisos'
                }
                description="Configure las capacidades habilitadas para este rol institucional."
                size="xl"
            >
                {editRole && (
                    <PermissionEditor
                        key={editRole.id}
                        role={editRole}
                        grupos={permisosAgrupados}
                        onClose={() => setEditRole(null)}
                    />
                )}
            </ModalInstitucional>
        </AdminLayout>
    );
}
