import PlantillaEvaluacionForm from '@/Components/Evaluaciones/PlantillaEvaluacionForm';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Edit({ plantilla, preguntasDisponibles }) {
    return <AdminLayout title="Editar Plantilla de Evaluación" subtitle="Actualización de estructura, ponderación y contenido académico."><Head title="Editar plantilla" /><div className="mx-auto max-w-6xl"><PlantillaEvaluacionForm plantilla={plantilla} preguntasDisponibles={preguntasDisponibles} submitRoute={route('plantillas-evaluacion.update', plantilla.id_plan)} method="put" submitLabel="Guardar cambios" /></div></AdminLayout>;
}
