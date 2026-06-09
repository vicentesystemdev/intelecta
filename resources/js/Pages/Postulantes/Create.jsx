import PostulanteForm from '@/Components/Postulantes/PostulanteForm';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Create({ gestionActual, opciones }) {
    return (
        <AdminLayout
            title="Registrar postulante"
            subtitle="Incorporación de postulantes al proceso académico preuniversitario."
        >
            <Head title="Registrar postulante" />

            <div className="mx-auto max-w-5xl">
                <PostulanteForm
                    gestionActual={gestionActual}
                    opciones={opciones}
                    submitRoute={route('postulantes.store')}
                    submitLabel="Registrar postulante"
                />
            </div>
        </AdminLayout>
    );
}
