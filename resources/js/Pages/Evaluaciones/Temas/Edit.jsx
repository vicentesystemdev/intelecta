import { TemaForm } from '@/Pages/Evaluaciones/Temas/Create';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ tema, areas }) {
    const form = useForm({ id_area: tema.id_area, nombre_tem: tema.nombre_tem, descripcion_tem: tema.descripcion_tem || '', nivel_tem: tema.nivel_tem || 'intermedio', estado_tem: tema.estado_tem });
    return <AdminLayout title="Editar Tema Académico" subtitle="Actualización de la clasificación temática."><Head title={`Editar ${tema.nombre_tem}`} /><TemaForm {...form} areas={areas} onSubmit={(e) => { e.preventDefault(); form.put(route('temas.update', tema.id_tem)); }} label="Guardar cambios" /></AdminLayout>;
}
