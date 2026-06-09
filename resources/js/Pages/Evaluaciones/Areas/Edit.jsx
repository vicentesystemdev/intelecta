import { AreaForm } from '@/Pages/Evaluaciones/Areas/Create';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ area }) {
    const { data, setData, put, processing, errors } = useForm({
        nombre_area: area.nombre_area,
        descripcion_area: area.descripcion_area || '',
        estado_area: area.estado_area,
    });
    return (
        <AdminLayout title="Editar Área de Conocimiento" subtitle="Actualización de la estructura curricular institucional.">
            <Head title={`Editar ${area.nombre_area}`} />
            <AreaForm data={data} setData={setData} errors={errors} processing={processing} onSubmit={(e) => { e.preventDefault(); put(route('areas-conocimiento.update', area.id_area)); }} label="Guardar cambios" />
        </AdminLayout>
    );
}
