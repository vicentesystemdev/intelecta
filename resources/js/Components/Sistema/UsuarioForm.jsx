import ConfirmModal from '@/Components/ConfirmModal';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useForm } from '@inertiajs/react';
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
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm({
        name: usuario?.name || '',
        email: usuario?.email || '',
        role: usuario?.roles?.[0]?.name || '',
        password: '',
        password_confirmation: '',
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
            (data.role !== originalRole || Boolean(data.password));

        if (sensitiveUpdate) {
            setConfirmOpen(true);
            return;
        }

        persist();
    };

    return (
        <>
            <form onSubmit={submit} className="space-y-6">
                <div className="grid gap-5 sm:grid-cols-2">
                    <div>
                        <Label htmlFor="name">Nombre completo *</Label>
                        <Input
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
                        <Label htmlFor="email">Correo electrónico *</Label>
                        <Input
                            id="email"
                            type="email"
                            className={fieldClass}
                            value={data.email}
                            onChange={(event) =>
                                setData('email', event.target.value)
                            }
                        />
                        <InputError className="mt-1.5" message={errors.email} />
                    </div>

                    <div className="sm:col-span-2">
                        <Label htmlFor="role">Rol institucional *</Label>
                        <div className="relative mt-1.5">
                            <ShieldCheck className="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" />
                            <select
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

                    <div>
                        <Label htmlFor="password">
                            {usuario ? 'Nueva contraseña' : 'Contraseña *'}
                        </Label>
                        <Input
                            id="password"
                            type="password"
                            className={fieldClass}
                            value={data.password}
                            onChange={(event) =>
                                setData('password', event.target.value)
                            }
                            autoComplete="new-password"
                        />
                        {usuario && (
                            <p className="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                Déjela vacía para conservar la contraseña actual.
                            </p>
                        )}
                        <InputError
                            className="mt-1.5"
                            message={errors.password}
                        />
                    </div>

                    <div>
                        <Label htmlFor="password_confirmation">
                            Confirmar contraseña{usuario ? '' : ' *'}
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            className={fieldClass}
                            value={data.password_confirmation}
                            onChange={(event) =>
                                setData(
                                    'password_confirmation',
                                    event.target.value,
                                )
                            }
                            autoComplete="new-password"
                        />
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
                message="Se modificará el rol institucional o la contraseña de esta cuenta."
                confirmLabel="Confirmar cambios"
                processing={processing}
                supportingText="Verifique que el perfil asignado corresponde a las responsabilidades del usuario."
                onConfirm={persist}
            />
        </>
    );
}
