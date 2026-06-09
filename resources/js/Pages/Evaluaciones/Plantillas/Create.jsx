import PlantillaEvaluacionForm from '@/Components/Evaluaciones/PlantillaEvaluacionForm';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Create({ preguntasDisponibles }) {
    return <AdminLayout title="Nueva Plantilla de Evaluación" subtitle="Composición de un instrumento académico desde el banco de preguntas."><Head title="Nueva plantilla" /><div className="mx-auto max-w-6xl"><PlantillaEvaluacionForm preguntasDisponibles={preguntasDisponibles} submitRoute={route('plantillas-evaluacion.store')} submitLabel="Crear plantilla" /></div></AdminLayout>;
}
