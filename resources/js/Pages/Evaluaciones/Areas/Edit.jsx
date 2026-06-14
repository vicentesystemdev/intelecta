import AdminLayout from '@/Layouts/AdminLayout';
import { AreaForm } from '@/Pages/Evaluaciones/Areas/Create';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ area, materias = [] }) {
    const form = useForm({
        id_mat: area.id_mat || '',
        nombre_area: area.nombre_area,
        descripcion_area: area.descripcion_area || '',
        estado_area: area.estado_area,
    });

    return (
        <AdminLayout
            title="Editar Área de Conocimiento"
            subtitle="Actualización de la materia y estructura curricular institucional."
        >
            <Head title={`Editar ${area.nombre_area}`} />
            <AreaForm
                {...form}
                materias={materias}
                onSubmit={(event) => {
                    event.preventDefault();
                    form.put(route('areas-conocimiento.update', area.id_area));
                }}
                label="Guardar cambios"
            />
        </AdminLayout>
    );
}
