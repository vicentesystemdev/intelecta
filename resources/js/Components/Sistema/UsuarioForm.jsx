import { validationProps } from '@/lib/inputValidation';
import ConfirmModal from '@/Components/ConfirmModal';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useForm, usePage } from '@inertiajs/react';
import { Save, ShieldCheck } from 'lucide-react';
import { useState } from 'react';

const fieldClass =
    'mt-1.5 h-10 border-slate-200 bg-white text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

export default function UsuarioForm({
    usuario = null,
    roles,
    submitRoute,
    method = 'post',
    submitLabel,
    onCancel,
}) {
    const { permisos } = usePage().props;
    const canChangeLoginEmail = !usuario || permisos?.cambiarCorreoAcceso === true;
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm({
        name: usuario?.name || '',
        email: usuario?.email || '',
        role: usuario?.roles?.[0]?.name || '',
    });

    const persist = () => {
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setConfirmOpen(false);
                onCancel();
            },
        };

        if (method === 'put') {
            put(submitRoute, options);
            return;
        }

        post(submitRoute, options);
    };

    const submit = (event) => {
        event.preventDefault();

        const originalRole = usuario?.roles?.[0]?.name || '';
        const sensitiveUpdate =
            usuario &&
            (data.role !== originalRole || data.email !== usuario.email);

        if (sensitiveUpdate) {
            setConfirmOpen(true);
            return;
        }

        persist();
    };

    return (
        <>
            <form onSubmit={submit} className="space-y-6">
                <InputError message={errors.ultimo_sa || errors.activacion} />
                <p className="text-sm text-slate-500">La contraseña la establece el titular mediante un enlace seguro. TI no define ni conoce contraseñas iniciales.</p>
                <div className="grid gap-5 sm:grid-cols-2">
                    <div>
                        <Label htmlFor="name">Nombre completo *</Label>
                        <Input {...validationProps('name')}
                            id="name"
                            className={fieldClass}
                            value={data.name}
                            onChange={(event) =>
                                setData('name', event.target.value)
                            }
                            autoFocus
                        />
                        <InputError className="mt-1.5" message={errors.name} />
                    </div>

                    <div>
                        <Label htmlFor="email">Correo de acceso *</Label>
                        <Input {...validationProps('email')}
                            id="email"
                            type="email"
                            className={fieldClass}
                            value={data.email}
                            readOnly={!canChangeLoginEmail}
                            onChange={(event) =>
                                setData('email', event.target.value)
                            }
                        />
                        <InputError className="mt-1.5" message={errors.email} />
                        {usuario && (
                            <p className="mt-1.5 text-xs text-slate-500">
                                Solo TI puede cambiar este correo. El cambio invalida su verificación y los enlaces de recuperación anteriores.
                            </p>
                        )}
                    </div>

                    <div className="sm:col-span-2">
                        <Label htmlFor="role">Rol institucional *</Label>
                        <div className="relative mt-1.5">
                            <ShieldCheck className="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" />
                            <select {...validationProps('role')}
                                id="role"
                                className={`${fieldClass} mt-0 w-full rounded-lg pl-9 pr-3 text-sm focus:border-indigo-500 focus:ring-indigo-500`}
                                value={data.role}
                                onChange={(event) =>
                                    setData('role', event.target.value)
                                }
                            >
                                <option value="">Seleccione un rol</option>
                                {roles.map((role) => (
                                    <option key={role.id} value={role.name}>
                                        {role.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <InputError className="mt-1.5" message={errors.role} />
                    </div>

                </div>

                <div className="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 dark:border-slate-800 sm:flex-row sm:justify-end">
                    <Button
                        type="button"
                        variant="outline"
                        className="h-10"
                        disabled={processing}
                        onClick={onCancel}
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        disabled={processing}
                        className="h-10 bg-indigo-700 px-5 text-white hover:bg-indigo-800"
                    >
                        <Save className="h-4 w-4" />
                        {processing ? 'Guardando...' : submitLabel}
                    </Button>
                </div>
            </form>

            <ConfirmModal
                open={confirmOpen}
                onOpenChange={setConfirmOpen}
                title="Confirmar actualización sensible"
                message="Se modificará el rol o el correo de acceso de esta cuenta."
                confirmLabel="Confirmar cambios"
                processing={processing}
                supportingText="Verifique que el perfil asignado corresponde a las responsabilidades del usuario."
                onConfirm={persist}
            />
        </>
    );
}
