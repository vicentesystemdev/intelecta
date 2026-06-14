import { InstitutionalBanner, cardClass } from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import { Bell, BookOpenCheck, Clock3, Settings, ShieldCheck } from 'lucide-react';

const sections = [
    ['Operación académica', 'Criterios institucionales aplicados a programas, grupos, simulacros y evaluaciones.', BookOpenCheck, ['Gestión por ciclos académicos', 'Seguimiento por grupos y paralelos', 'Estados institucionales normalizados']],
    ['Tiempos y evaluaciones', 'Parámetros administrados desde plantillas y simulacros autorizados.', Clock3, ['Duración definida por plantilla', 'Horarios definidos por simulacro', 'Ponderación académica controlada']],
    ['Notificaciones institucionales', 'Criterios de comunicación para alertas académicas y administrativas.', Bell, ['Mensajes de seguimiento no sancionatorios', 'Alertas por estados reales', 'Información dirigida al rol correspondiente']],
    ['Seguridad y trazabilidad', 'La administración de acceso permanece centralizada en Usuarios y Roles y Permisos.', ShieldCheck, ['Acceso protegido por autenticación', 'Permisos administrados por rol', 'Parámetros sensibles fuera de esta vista']],
];

export default function Configuracion() {
    return (
        <AdminLayout title="Configuración Institucional" subtitle="Criterios operativos seguros para el funcionamiento académico de INTELECTA." wide>
            <Head title="Configuración Institucional" />
            <InstitutionalBanner
                eyebrow="Administración del sistema"
                title="Configuración Institucional"
                description="Consulte el marco operativo vigente. Los cambios sensibles de infraestructura y seguridad no se exponen en esta interfaz."
                icon={Settings}
            />
            <div className="grid gap-5 md:grid-cols-2">
                {sections.map(([title, description, Icon, items]) => (
                    <section key={title} className={`${cardClass} p-5 sm:p-6`}>
                        <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-secondary/10 text-brand-secondary"><Icon className="h-5 w-5" /></span>
                        <h2 className="mt-4 text-lg font-black text-text-main">{title}</h2>
                        <p className="mt-2 text-sm leading-6 text-text-muted">{description}</p>
                        <ul className="mt-5 space-y-3">
                            {items.map((item) => (
                                <li key={item} className="flex items-start gap-3 rounded-xl bg-brand-bg p-3 text-sm text-text-main">
                                    <span className="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand-success" />
                                    <span>{item}</span>
                                </li>
                            ))}
                        </ul>
                    </section>
                ))}
            </div>
            <div className="mt-6 rounded-2xl border border-brand-info/25 bg-brand-info/10 p-5 text-sm leading-6 text-text-main">
                Los parámetros académicos se administran desde sus módulos correspondientes para conservar trazabilidad y evitar cambios globales inseguros.
            </div>
        </AdminLayout>
    );
}
