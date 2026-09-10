import { Field, SelectField, cardClass, secondaryButtonClass } from '@/Components/Institucional/InstitutionalUi';
import { router } from '@inertiajs/react';
import { useState } from 'react';

export default function OrganizationFilters({ routeName, filters, personal = false }) {
    const [values, setValues] = useState({ buscar: filters.buscar || '', estado: filters.estado || '' });
    return <form className={`${cardClass} mb-5 flex flex-wrap items-end gap-3 p-4`} onSubmit={(event) => {
        event.preventDefault();
        router.get(route(routeName), values, { preserveState: true, replace: true });
    }}>
        <Field label="Buscar" className="min-w-48 flex-1" maxLength={160} value={values.buscar} onChange={(event) => setValues({ ...values, buscar: event.target.value })} />
        <SelectField label="Estado" value={values.estado} onChange={(event) => setValues({ ...values, estado: event.target.value })}>
            <option value="">Todos</option>
            {personal && <option value="pendiente">Pendiente</option>}
            <option value="activo">Activo</option><option value="inactivo">Inactivo</option>
        </SelectField>
        <button className={secondaryButtonClass}>Filtrar</button>
    </form>;
}
