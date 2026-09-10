import { useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import ConfirmModal from '@/Components/ConfirmModal';
import { Button } from '@/Components/ui/button';
import { useState } from 'react';

export default function UsuarioRolesForm({ usuario, roles, onClose }) {
    const form = useForm({ roles: usuario.roles.map((role) => role.name) });
    const [confirm, setConfirm] = useState(false);
    const toggle = (name) => form.setData('roles', form.data.roles.includes(name)
        ? form.data.roles.filter((role) => role !== name) : [...form.data.roles, name]);
    const persist = () => form.put(route('admin.sistema.usuarios.roles', usuario.id), {
        preserveScroll: true, onSuccess: onClose, onError: () => setConfirm(false),
    });
    return <div className="space-y-4">
        <p className="font-semibold">{usuario.name}</p>
        <p className="text-sm">Administrador + Docente es válido. Estudiante y Super Administrador son exclusivos. Para nuevos roles se exige: Postulante vinculado (Estudiante), Personal + Tutor (Docente), Personal (Administrador). Personal pendiente es válido.</p>
        <fieldset className="space-y-2"><legend className="font-medium">Conjunto completo de roles</legend>
            {roles.map((role) => <label key={role.id} className="flex items-center gap-3">
                <input type="checkbox" checked={form.data.roles.includes(role.name)} onChange={() => toggle(role.name)} disabled={form.processing} />{role.name}
            </label>)}
        </fieldset>
        {Object.entries(form.errors).map(([key, message]) => <InputError key={key} message={message} />)}
        <p className="text-sm">Quitar todas las selecciones deja la cuenta sin roles. No borra cuentas ni expedientes.</p>
        <div className="flex justify-end gap-2"><Button variant="outline" onClick={onClose} disabled={form.processing}>Cancelar</Button><Button onClick={() => setConfirm(true)} disabled={form.processing}>Revisar cambios</Button></div>
        <ConfirmModal open={confirm} onOpenChange={setConfirm} title="Confirmar asignación de roles"
            message={`Antes: ${usuario.roles.map((r) => r.name).join(' + ') || 'Sin roles'}. Después: ${form.data.roles.join(' + ') || 'Sin roles'}.`}
            supportingText="Los cambios revocan sesiones y enlaces anteriores. Si la cuenta está pendiente, reenvía su activación después. El último SA activo está protegido."
            confirmLabel="Confirmar roles" processing={form.processing} onConfirm={persist} />
    </div>;
}
