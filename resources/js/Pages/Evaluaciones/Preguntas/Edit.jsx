import PreguntaForm from '@/Components/Evaluaciones/PreguntaForm';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Edit({ pregunta, opciones }) {
    return <AdminLayout title="Editar Pregunta" subtitle="Actualización controlada del contenido y sus alternativas."><Head title="Editar pregunta" /><div className="mx-auto max-w-5xl"><PreguntaForm pregunta={pregunta} opciones={opciones} submitRoute={route('preguntas.update', pregunta.id_preg)} method="put" submitLabel="Guardar cambios" /></div></AdminLayout>;
}
