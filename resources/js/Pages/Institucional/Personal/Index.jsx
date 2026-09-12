import { EmptyInstitutional, Field, FlashMessage, InstitutionalBanner, InstitutionalStatus, SelectField, cardClass, primaryButtonClass, secondaryButtonClass } from '@/Components/Institucional/InstitutionalUi';
import OrganizationFilters from '@/Components/Institucional/OrganizationFilters';
import VincularPersonalModal from '@/Components/Institucional/VincularPersonalModal';
import ConfirmModal from '@/Components/ConfirmModal';
import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { validationProps } from '@/lib/inputValidation';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Users } from 'lucide-react';
import { useState } from 'react';

const empty = { nombres: '', apellidos: '', ci: '', celular: '', correo_contacto: '', cargo_id: '' };
const labels = { nombres: 'Nombres', apellidos: 'Apellidos', ci: 'C.I.', celular: 'Celular', correo_contacto: 'Correo de contacto' };
const progressiveRequired = new Set(['ci', 'celular', 'correo_contacto']);

export default function Index({ personal, cargosActivos = [], filtros = {}, permisos = {} }) {
    const { flash } = usePage().props;
    const [modal, setModal] = useState(null);
    const [transition, setTransition] = useState(null);
    const [link, setLink] = useState(null);
    const form = useForm(empty);
    const stateForm = useForm({ estado: '' });
    const open = (person = null) => {
        form.clearErrors();
        form.setData(Object.fromEntries(Object.keys(empty).map((field) => [field, person?.[field] ?? ''])));
        setModal({ person });
    };
    const changeState = (person, state) => {
        stateForm.clearErrors(); stateForm.setData('estado', state); setTransition(person);
    };
    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => setModal(null) };
        modal.person ? form.put(route('admin.institucional.personal.update', modal.person.id_personal), options)
            : form.post(route('admin.institucional.personal.store'), options);
    };
    const currentCargo = modal?.person?.cargo;
    const retainInactive = currentCargo && !cargosActivos.some((cargo) => cargo.id_cargo === currentCargo.id_cargo);
    return <AdminLayout title="Personal Institucional" subtitle="Personas y cargos, independientes de la identidad digital y los permisos." wide>
        <Head title="Personal Institucional" />
        <InstitutionalBanner eyebrow="Gestión institucional" title="Personal Institucional" description="Registre los datos institucionales completos. La gestión de seguridad de User continúa a cargo de TI." icon={Users}
            action={permisos.crear && <button className={primaryButtonClass} onClick={() => open()}>Nuevo personal</button>} />
        <FlashMessage message={flash?.success} />
        <OrganizationFilters routeName="admin.institucional.personal.index" filters={filtros} personal />
        {stateForm.errors.estado && <p role="alert" className="mb-3 text-red-600">{stateForm.errors.estado}</p>}
        {personal.data.length ? <div className={`${cardClass} overflow-x-auto`}><table className="w-full text-left text-sm">
            <thead><tr className="border-b border-brand-border">{['Persona / C.I.', 'Contacto', 'Cargo', 'Estado institucional', 'Cuenta digital', 'Acciones'].map((title) => <th key={title} className="p-4">{title}</th>)}</tr></thead>
            <tbody>{personal.data.map((person) => <tr key={person.id_personal} className="border-b border-brand-border last:border-0">
                <td className="min-w-44 p-4"><p className="font-semibold">{person.nombres} {person.apellidos}</p><p className="mt-1 text-xs text-text-muted">Personal #{person.id_personal} · C.I. {person.ci || 'Sin registrar'}</p></td>
                <td className="max-w-64 break-words p-4"><p>{person.celular || 'Sin celular'}</p><p>{person.correo_contacto || 'Sin correo de contacto'}</p></td>
                <td className="p-4">{person.cargo?.nombre_cargo || 'Sin cargo'}{person.cargo?.estado === 'inactivo' && <p className="mt-1 text-xs text-text-muted">Cargo inactivo · asignación conservada</p>}</td>
                <td className="p-4"><InstitutionalStatus status={person.estado} /></td>
                <td className="max-w-64 break-words p-4">{person.user ? <><p className="font-medium">User #{person.user.id} · {person.user.name}</p><p>{person.user.email}</p><p className="mt-1 text-xs">Cuenta {person.user.estado_cuenta}</p></> : 'Sin cuenta vinculada'}</td>
                <td className="p-4"><div className="flex min-w-40 flex-wrap gap-2">
                    {permisos.editar && <button className={secondaryButtonClass} onClick={() => open(person)}>Editar</button>}
                    {permisos.cambiar_estado && ['activo', 'inactivo', 'pendiente'].filter((state) => state !== person.estado).map((state) => <button key={state} className={secondaryButtonClass} onClick={() => changeState(person, state)}>{state === 'activo' ? 'Activar' : state === 'inactivo' ? 'Inactivar' : 'Marcar pendiente'}</button>)}
                    {permisos.vincular && !person.user_id && <button className={secondaryButtonClass} onClick={() => setLink(person)}>Vincular User</button>}
                </div></td>
            </tr>)}</tbody>
        </table></div> : <EmptyInstitutional title="Sin personal registrado" description="No se generan perfiles automáticamente desde usuarios ni tutores." />}
        <Pagination links={personal.links} />
        <ModalInstitucional open={Boolean(modal)} onOpenChange={(open) => !open && !form.processing && setModal(null)} title={modal?.person ? 'Editar datos institucionales' : 'Registrar personal'} description="Los nuevos registros quedan pendientes. Este formulario no modifica cuentas, contraseñas ni roles." size="lg">
            <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                {Object.entries(labels).map(([field, label]) => {
                    const required = !modal?.person || !progressiveRequired.has(field) || Boolean(form.data[field]);
                    return <Field key={field} label={`${label}${required ? ' *' : ''}`} {...validationProps(field, { required })} value={form.data[field]} onChange={(event) => form.setData(field, event.target.value)} error={form.errors[field]} />;
                })}
                <SelectField label={`Cargo principal${!modal?.person || Boolean(form.data.cargo_id) ? ' *' : ''}`} required={!modal?.person || Boolean(form.data.cargo_id)} value={form.data.cargo_id} onChange={(event) => form.setData('cargo_id', event.target.value)} error={form.errors.cargo_id}>
                    <option value="">Seleccione un cargo institucional</option>
                    {cargosActivos.map((cargo) => <option key={cargo.id_cargo} value={cargo.id_cargo}>{cargo.nombre_cargo}</option>)}
                    {retainInactive && <option value={currentCargo.id_cargo}>{currentCargo.nombre_cargo} (inactivo, asignación actual)</option>}
                </SelectField>
                <p className="text-xs text-slate-600 sm:col-span-2">El correo de contacto es independiente del correo de acceso. {modal?.person?.user ? `Cuenta vinculada: User #${modal.person.user.id} — ${modal.person.user.email}.` : 'Sin cuenta vinculada. TI puede confirmar el vínculo por separado.'}</p>
                <div className="flex justify-end gap-2 sm:col-span-2"><button type="button" className={secondaryButtonClass} disabled={form.processing} onClick={() => setModal(null)}>Cancelar</button><button className={primaryButtonClass} disabled={form.processing}>Guardar</button></div>
            </form>
        </ModalInstitucional>
        <ConfirmModal open={Boolean(transition)} onOpenChange={(open) => !open && !stateForm.processing && setTransition(null)} title="Cambiar estado institucional" message={`${transition?.nombres || ''} ${transition?.apellidos || ''} → ${stateForm.data.estado}`} supportingText="Este cambio no activa ni bloquea la cuenta digital y no modifica roles, permisos o expedientes académicos." processing={stateForm.processing}
            onConfirm={() => stateForm.patch(route('admin.institucional.personal.estado', transition.id_personal), { preserveScroll: true, onSuccess: () => setTransition(null) })} />
        {link && <VincularPersonalModal key={link.id_personal} personal={link} onClose={() => setLink(null)} />}
    </AdminLayout>;
}
