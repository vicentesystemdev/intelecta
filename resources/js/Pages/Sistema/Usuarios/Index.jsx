import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import UsuarioForm from '@/Components/Sistema/UsuarioForm';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
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
import { Head, router, usePage } from '@inertiajs/react';
import {
    Eye,
    GraduationCap,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    ShieldCheck,
    UserCog,
    Users,
} from 'lucide-react';
import { useMemo, useState } from 'react';

const metricCards = [
    { key: 'total', label: 'Total usuarios', icon: Users, tone: 'primary' },
    {
        key: 'administradores',
        label: 'Administradores',
        icon: ShieldCheck,
        tone: 'accent',
    },
    { key: 'docentes', label: 'Docentes', icon: UserCog, tone: 'secondary' },
    {
        key: 'estudiantes',
        label: 'Estudiantes',
        icon: GraduationCap,
        tone: 'muted',
    },
];

const toneClasses = {
    primary: 'bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20 dark:text-slate-200',
    accent: 'bg-brand-accent/10 text-brand-primary dark:bg-brand-accent/20 dark:text-brand-accent',
    secondary: 'bg-brand-secondary/10 text-brand-secondary dark:bg-brand-secondary/20 dark:text-brand-secondary',
    muted: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
};

export default function Index({
    usuarios,
    roles,
    filtros = {},
    metricas,
    permisos,
    usuarioActualId,
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
        role: filtros.role || '',
    });
    const [formModal, setFormModal] = useState({
        open: false,
        usuario: null,
    });
    const [detailModal, setDetailModal] = useState({
        open: false,
        usuario: null,
    });

    const rolesDisponibles = useMemo(
        () =>
            permisos.asignarSuperAdministrador
                ? roles
                : roles.filter((role) => role.name !== 'Super Administrador'),
        [permisos.asignarSuperAdministrador, roles],
    );

    const submitFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.sistema.usuarios'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        setFilters({ buscar: '', role: '' });
        router.get(route('admin.sistema.usuarios'), {}, { replace: true });
    };

    const formatDate = (date) =>
        new Intl.DateTimeFormat('es-BO', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(new Date(date));

    const canEditUser = (usuario) =>
        permisos.editar &&
        (permisos.asignarSuperAdministrador ||
            !usuario.roles.some(
                (role) => role.name === 'Super Administrador',
            ));

    return (
        <AdminLayout
            title="Gestión de Usuarios"
            subtitle="Administración de accesos institucionales y perfiles del sistema."
        >
            <Head title="Gestión de Usuarios" />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {metricCards.map(({ key, label, icon: Icon, tone }) => (
                    <Card
                        key={key}
                        className="border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:ring-slate-800"
                    >
                        <CardContent className="flex items-center justify-between p-5">
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {label}
                                </p>
                                <p className="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">
                                    {metricas[key]}
                                </p>
                            </div>
                            <span
                                className={`flex h-11 w-11 items-center justify-center rounded-2xl ${toneClasses[tone]}`}
                            >
                                <Icon className="h-5 w-5" />
                            </span>
                        </CardContent>
                    </Card>
                ))}
            </div>

            {flash?.success && (
                <Alert className="mb-5 border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
                    <ShieldCheck className="h-4 w-4" />
                    <AlertDescription>{flash.success}</AlertDescription>
                </Alert>
            )}

            <div className="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <p className="text-sm text-slate-500 dark:text-slate-400">
                    {usuarios.total} cuentas institucionales registradas
                </p>
                {permisos.crear && (
                    <Button
                        type="button"
                        className="h-10 bg-brand-primary px-4 text-white hover:bg-brand-primary/90"
                        onClick={() =>
                            setFormModal({ open: true, usuario: null })
                        }
                    >
                        <Plus className="h-4 w-4" />
                        Nuevo usuario
                    </Button>
                )}
            </div>

            <Card className="mb-5 border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:ring-slate-800">
                <CardContent className="p-4">
                    <form
                        onSubmit={submitFilters}
                        className="grid gap-3 md:grid-cols-[minmax(260px,1fr)_240px_auto]"
                    >
                        <div className="relative">
                            <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                            <Input
                                className="h-10 bg-white pl-9 dark:border-slate-700 dark:bg-slate-950"
                                placeholder="Buscar por nombre o correo"
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
                            className="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            value={filters.role}
                            onChange={(event) =>
                                setFilters({
                                    ...filters,
                                    role: event.target.value,
                                })
                            }
                        >
                            <option value="">Todos los roles</option>
                            {roles.map((role) => (
                                <option key={role.id} value={role.name}>
                                    {role.name}
                                </option>
                            ))}
                        </select>
                        <div className="flex gap-2">
                            <Button className="h-10 flex-1 bg-brand-primary text-white hover:bg-brand-primary/90">
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

            <Card className="border-0 py-0 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900/60 dark:ring-slate-800">
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-slate-50/80 dark:bg-slate-900">
                                <TableHead className="pl-5">Nombre</TableHead>
                                <TableHead>Correo electrónico</TableHead>
                                <TableHead>Rol</TableHead>
                                <TableHead>Fecha de creación</TableHead>
                                <TableHead className="pr-5 text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {usuarios.data.map((usuario) => (
                                <TableRow key={usuario.id}>
                                    <TableCell className="pl-5">
                                        <div className="flex items-center gap-3">
                                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-primary/10 text-xs font-bold text-brand-primary dark:bg-brand-primary/20 dark:text-slate-200">
                                                {usuario.name
                                                    .split(' ')
                                                    .map((part) => part[0])
                                                    .join('')
                                                    .slice(0, 2)
                                                    .toUpperCase()}
                                            </span>
                                            <div>
                                                <p className="font-semibold text-slate-900 dark:text-slate-100">
                                                    {usuario.name}
                                                </p>
                                                {usuario.id ===
                                                    usuarioActualId && (
                                                    <p className="text-xs text-brand-secondary dark:text-brand-secondary">
                                                        Sesión actual
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-slate-600 dark:text-slate-300">
                                        {usuario.email}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex flex-wrap gap-1.5">
                                            {usuario.roles.map((role) => (
                                                <Badge
                                                    key={role.id}
                                                    variant="outline"
                                                    className="border-brand-primary/20 bg-brand-primary/5 text-brand-primary dark:border-brand-primary/30 dark:bg-brand-primary/10 dark:text-slate-200"
                                                >
                                                    {role.name}
                                                </Badge>
                                            ))}
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-slate-500 dark:text-slate-400">
                                        {formatDate(usuario.created_at)}
                                    </TableCell>
                                    <TableCell className="pr-5">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                aria-label="Ver usuario"
                                                onClick={() =>
                                                    setDetailModal({
                                                        open: true,
                                                        usuario,
                                                    })
                                                }
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            {canEditUser(usuario) && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label="Editar usuario"
                                                    onClick={() =>
                                                        setFormModal({
                                                            open: true,
                                                            usuario,
                                                        })
                                                    }
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Pagination links={usuarios.links} />

            <ModalInstitucional
                open={formModal.open}
                onOpenChange={(open) =>
                    setFormModal((current) => ({ ...current, open }))
                }
                title={
                    formModal.usuario ? 'Editar usuario' : 'Registrar usuario'
                }
                description={
                    formModal.usuario
                        ? 'Actualice el acceso institucional, rol o credenciales de la cuenta.'
                        : 'Cree una cuenta institucional y asigne su perfil de acceso.'
                }
                size="lg"
            >
                {formModal.open && (
                    <UsuarioForm
                        key={
                            formModal.usuario
                                ? `edit-user-${formModal.usuario.id}`
                                : 'create-user'
                        }
                        usuario={formModal.usuario}
                        roles={rolesDisponibles}
                        submitRoute={
                            formModal.usuario
                                ? route(
                                      'admin.sistema.usuarios.update',
                                      formModal.usuario.id,
                                  )
                                : route('admin.sistema.usuarios.store')
                        }
                        method={formModal.usuario ? 'put' : 'post'}
                        submitLabel={
                            formModal.usuario
                                ? 'Guardar cambios'
                                : 'Registrar usuario'
                        }
                        onCancel={() =>
                            setFormModal({ open: false, usuario: null })
                        }
                    />
                )}
            </ModalInstitucional>

            <ModalInstitucional
                open={detailModal.open}
                onOpenChange={(open) =>
                    setDetailModal((current) => ({ ...current, open }))
                }
                title="Detalle del usuario"
                description="Información consolidada de la cuenta y su perfil institucional."
                size="md"
            >
                {detailModal.usuario && (
                    <div className="space-y-5">
                        <div className="rounded-2xl bg-gradient-to-r from-brand-primary to-brand-secondary p-5 text-white">
                            <p className="text-lg font-bold">
                                {detailModal.usuario.name}
                            </p>
                            <p className="mt-1 text-sm text-slate-200">
                                {detailModal.usuario.email}
                            </p>
                        </div>
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Rol asignado
                                </dt>
                                <dd className="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    {detailModal.usuario.roles
                                        .map((role) => role.name)
                                        .join(', ') || 'Sin rol asignado'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Cuenta creada
                                </dt>
                                <dd className="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    {formatDate(
                                        detailModal.usuario.created_at,
                                    )}
                                </dd>
                            </div>
                        </dl>
                    </div>
                )}
            </ModalInstitucional>
        </AdminLayout>
    );
}
