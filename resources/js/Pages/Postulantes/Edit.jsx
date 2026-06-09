import PostulanteForm from '@/Components/Postulantes/PostulanteForm';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function Edit({ postulante, opciones }) {
    return (
        <AdminLayout
            title="Editar postulante"
            subtitle="Actualización de información personal y académica."
        >
            <Head title={`Editar ${postulante.nombres_post}`} />

            <div className="mx-auto max-w-5xl">
                <PostulanteForm
                    postulante={postulante}
                    opciones={opciones}
                    gestionActual={new Date().getFullYear()}
                    submitRoute={route('postulantes.update', postulante.id_post)}
                    method="put"
                    submitLabel="Guardar cambios"
                />
            </div>
        </AdminLayout>
    );
}
