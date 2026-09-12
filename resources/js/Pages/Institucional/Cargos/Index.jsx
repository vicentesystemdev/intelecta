import { EmptyInstitutional, Field, FlashMessage, InstitutionalBanner, InstitutionalStatus, TextareaField, cardClass, primaryButtonClass, secondaryButtonClass } from '@/Components/Institucional/InstitutionalUi';
import OrganizationFilters from '@/Components/Institucional/OrganizationFilters';
import ConfirmModal from '@/Components/ConfirmModal';
import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { validationProps } from '@/lib/inputValidation';
import { Head, useForm, usePage } from '@inertiajs/react';
import { BriefcaseBusiness } from 'lucide-react';
import { useState } from 'react';

export default function Index({ cargos, filtros = {}, permisos = {} }) {
    const { flash } = usePage().props;
    const [modal, setModal] = useState(null);
    const [transition, setTransition] = useState(null);
    const form = useForm({ nombre_cargo: '', descripcion: '' });
    const stateForm = useForm({ estado: '' });
    const open = (cargo = null) => {
        form.clearErrors();
        form.setData({ nombre_cargo: cargo?.nombre_cargo || '', descripcion: cargo?.descripcion || '' });
        setModal({ cargo });
    };
    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => setModal(null) };
        modal.cargo ? form.put(route('admin.institucional.cargos.update', modal.cargo.id_cargo), options)
            : form.post(route('admin.institucional.cargos.store'), options);
    };
    return <AdminLayout title="Cargos" subtitle="Catálogo organizacional independiente del acceso al sistema.">
        <Head title="Cargos" />
        <InstitutionalBanner eyebrow="Gestión institucional" title="Cargos" description="Un cargo describe una posición institucional. No asigna roles, permisos ni cuentas." icon={BriefcaseBusiness}
            action={permisos.crear && <button className={primaryButtonClass} onClick={() => open()}>Nuevo cargo</button>} />
        <FlashMessage message={flash?.success} />
        <OrganizationFilters routeName="admin.institucional.cargos.index" filters={filtros} />
        {stateForm.errors.estado && <p role="alert" className="mb-3 text-red-600">{stateForm.errors.estado}</p>}
        {cargos.data.length ? <div className={`${cardClass} overflow-x-auto`}><table className="w-full text-left text-sm">
            <thead><tr className="border-b border-brand-border"><th className="p-4">Cargo</th><th className="p-4">Descripción</th><th className="p-4">Estado</th><th className="p-4">Personal</th><th className="p-4">Acciones</th></tr></thead>
            <tbody>{cargos.data.map((cargo) => <tr key={cargo.id_cargo} className="border-b border-brand-border last:border-0">
                <td className="p-4 font-semibold">{cargo.nombre_cargo}</td><td className="max-w-md whitespace-pre-wrap break-words p-4">{cargo.descripcion || '—'}</td>
                <td className="p-4"><InstitutionalStatus status={cargo.estado} /></td><td className="p-4">{cargo.personal_count}</td>
                <td className="p-4"><div className="flex flex-wrap gap-2">
                    {permisos.editar && <button className={secondaryButtonClass} onClick={() => open(cargo)}>Editar</button>}
                    {permisos.cambiar_estado && <button className={secondaryButtonClass} onClick={() => {
                        stateForm.clearErrors(); stateForm.setData('estado', cargo.estado === 'activo' ? 'inactivo' : 'activo'); setTransition(cargo);
                    }}>{cargo.estado === 'activo' ? 'Inactivar' : 'Activar'}</button>}
                </div></td>
            </tr>)}</tbody>
        </table></div> : <EmptyInstitutional title="Sin cargos" description="Registre las posiciones institucionales confirmadas." />}
        <Pagination links={cargos.links} />
        <ModalInstitucional open={Boolean(modal)} onOpenChange={(open) => !open && !form.processing && setModal(null)} title={modal?.cargo ? 'Editar cargo' : 'Nuevo cargo'} description="La denominación es única sin distinguir mayúsculas. Los cargos nuevos comienzan activos.">
            <form onSubmit={submit} className="space-y-4">
                <Field label="Nombre del cargo *" {...validationProps('nombre_cargo')} value={form.data.nombre_cargo} onChange={(event) => form.setData('nombre_cargo', event.target.value)} error={form.errors.nombre_cargo} />
                <TextareaField label="Descripción *" required minLength={10} maxLength={2000} value={form.data.descripcion} onChange={(event) => form.setData('descripcion', event.target.value)} error={form.errors.descripcion} />
                <div className="flex justify-end gap-2"><button type="button" className={secondaryButtonClass} disabled={form.processing} onClick={() => setModal(null)}>Cancelar</button><button className={primaryButtonClass} disabled={form.processing}>Guardar</button></div>
            </form>
        </ModalInstitucional>
        <ConfirmModal open={Boolean(transition)} onOpenChange={(open) => !open && !stateForm.processing && setTransition(null)} title="Cambiar estado del cargo" message={`${transition?.nombre_cargo || ''} → ${stateForm.data.estado}`} supportingText="Las asignaciones existentes se conservan. Un cargo inactivo no admite nuevas asignaciones; no modifica cuentas ni roles." processing={stateForm.processing}
            onConfirm={() => stateForm.patch(route('admin.institucional.cargos.estado', transition.id_cargo), { preserveScroll: true, onSuccess: () => setTransition(null) })} />
    </AdminLayout>;
}
