import PreguntaForm from '@/Components/Evaluaciones/PreguntaForm';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Create({ opciones }) {
    return <AdminLayout title="Nueva Pregunta" subtitle="Incorporación de contenido al banco académico institucional."><Head title="Nueva pregunta" /><div className="mx-auto max-w-5xl"><PreguntaForm opciones={opciones} submitRoute={route('preguntas.store')} submitLabel="Registrar pregunta" /></div></AdminLayout>;
}
