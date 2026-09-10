import { Field, SelectField, TextareaField, primaryButtonClass, secondaryButtonClass } from '@/Components/Institucional/InstitutionalUi';
import ModalInstitucional from '@/Components/ModalInstitucional';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function VincularPersonalModal({ personal, onClose }) {
    const [search, setSearch] = useState('');
    const [result, setResult] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const form = useForm({ user_id: '', motivo: '' });
    const searchUsers = async (page = 1) => {
        setLoading(true); setError(''); form.setData('user_id', ''); setResult(null);
        try {
            const response = await fetch(route('admin.institucional.personal.usuarios-elegibles', { buscar: search, page }), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error('No se pudo consultar cuentas elegibles. Revisa la búsqueda y tu sesión.');
            setResult(await response.json());
        } catch (exception) { setError(exception.message); }
        finally { setLoading(false); }
    };
    return <ModalInstitucional open onOpenChange={(open) => !open && !form.processing && onClose()} title="Confirmar identidad digital" description={`Personal #${personal.id_personal}: ${personal.nombres} ${personal.apellidos}. Operación exclusiva de TI; no permite reemplazar un vínculo existente.`}>
        <form className="mb-5 flex items-end gap-2" onSubmit={(event) => { event.preventDefault(); searchUsers(); }}>
            <Field className="flex-1" label="Buscar cuenta por ID, nombre o correo de acceso" required minLength={1} maxLength={160} value={search} onChange={(event) => setSearch(event.target.value)} />
            <button className={secondaryButtonClass} disabled={loading || form.processing}>Buscar</button>
        </form>
        {error && <p role="alert" className="mb-3 text-sm text-red-600">{error}</p>}
        <form className="space-y-4" onSubmit={(event) => {
            event.preventDefault();
            form.post(route('admin.institucional.personal.vincular', personal.id_personal), { preserveScroll: true, onSuccess: onClose });
        }}>
            <SelectField label="Cuenta elegible — comprueba manualmente la identidad" required value={form.data.user_id} onChange={(event) => form.setData('user_id', event.target.value)} error={form.errors.user_id}>
                <option value="">Selecciona una cuenta sin Personal vinculado</option>
                {(result?.data || []).map((user) => <option key={user.id} value={user.id}>#{user.id} · {user.name} · {user.email} · {user.estado_cuenta}</option>)}
            </SelectField>
            {result && <div className="flex flex-wrap items-center gap-2 text-xs">
                <span>{result.total} cuentas elegibles para esta búsqueda. Página {result.current_page} de {result.last_page}.</span>
                {result.current_page > 1 && <button type="button" disabled={loading} className={secondaryButtonClass} onClick={() => searchUsers(result.current_page - 1)}>Anterior</button>}
                {result.current_page < result.last_page && <button type="button" disabled={loading} className={secondaryButtonClass} onClick={() => searchUsers(result.current_page + 1)}>Siguiente</button>}
            </div>}
            <p className="text-sm text-slate-600">No hay recomendaciones por coincidencia de correo. Este vínculo no activa cuentas ni concede roles. No se ofrece reasignación o desvinculación en este bloque.</p>
            <TextareaField label="Motivo de la confirmación (sin documentos ni secretos)" required minLength={10} maxLength={500} value={form.data.motivo} onChange={(event) => form.setData('motivo', event.target.value)} error={form.errors.motivo} />
            <div className="flex justify-end gap-2"><button type="button" className={secondaryButtonClass} disabled={form.processing} onClick={onClose}>Cancelar</button><button className={primaryButtonClass} disabled={form.processing || loading || !form.data.user_id}>Confirmar vínculo por IDs</button></div>
        </form>
    </ModalInstitucional>;
}
