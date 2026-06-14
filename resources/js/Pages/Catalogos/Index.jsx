import {
    EmptyInstitutional,
    InstitutionalBanner,
    InstitutionalStatus,
    MetricTile,
    cardClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import {
    BookOpenCheck,
    BriefcaseBusiness,
    Building2,
    CheckCircle2,
    GraduationCap,
    Layers3,
} from 'lucide-react';

const definitions = {
    carreras: {
        title: 'Carreras',
        subtitle: 'Catálogo académico de carreras objetivo de los postulantes.',
        eyebrow: 'Gestión de postulantes',
        description:
            'Consulte las carreras vinculadas con universidades, áreas de formación y nivel de exigencia matemática.',
        icon: BriefcaseBusiness,
        labels: ['Carreras registradas', 'Carreras activas', 'Con postulantes', 'Postulantes vinculados'],
    },
    colegios: {
        title: 'Colegios',
        subtitle: 'Catálogo institucional de colegios de procedencia.',
        eyebrow: 'Gestión de postulantes',
        description:
            'Consolide el origen académico de los postulantes para apoyar lecturas institucionales por procedencia.',
        icon: Building2,
        labels: ['Colegios registrados', 'Colegios activos', 'Con postulantes', 'Postulantes vinculados'],
    },
    materias: {
        title: 'Materias',
        subtitle: 'Estructura curricular para evaluación y preparación preuniversitaria.',
        eyebrow: 'Gestión evaluativa',
        description:
            'Consulte las materias base y su organización vigente por áreas y temas académicos.',
        icon: BookOpenCheck,
        labels: ['Materias registradas', 'Materias activas', 'Con áreas definidas', 'Temas vinculados'],
    },
};

export default function Index({ tipo, items = [], metricas = {} }) {
    const config = definitions[tipo] || definitions.carreras;
    const metrics = [
        [config.labels[0], metricas.total || 0, config.icon, 'primary'],
        [config.labels[1], metricas.activos || 0, CheckCircle2, 'success'],
        [config.labels[2], metricas.vinculados || 0, Layers3, 'info'],
        [config.labels[3], metricas.postulantes || 0, GraduationCap, 'accent'],
    ];

    return (
        <AdminLayout title={config.title} subtitle={config.subtitle} wide>
            <Head title={config.title} />
            <InstitutionalBanner
                eyebrow={config.eyebrow}
                title={config.title}
                description={config.description}
                icon={config.icon}
            />
            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {metrics.map(([label, value, icon, tone]) => (
                    <MetricTile key={label} label={label} value={value} icon={icon} tone={tone} />
                ))}
            </div>

            {items.length ? (
                <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    {items.map((item) => (
                        <article
                            key={item.id_car || item.id_col || item.id_mat}
                            className={`${cardClass} p-5 sm:p-6`}
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="min-w-0">
                                    <p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary">
                                        {tipo === 'carreras'
                                            ? item.universidad?.sigla_uni || item.area_car || 'Catálogo académico'
                                            : tipo === 'colegios'
                                              ? item.tipo_col || 'Colegio de procedencia'
                                              : item.codigo_mat || 'Materia académica'}
                                    </p>
                                    <h2 className="mt-2 break-words text-lg font-black leading-snug text-text-main">
                                        {item.nombre_car || item.nombre_col || item.nombre_mat}
                                    </h2>
                                </div>
                                <InstitutionalStatus
                                    status={item.estado_car || item.estado_col || item.estado_mat}
                                />
                            </div>
                            <p className="mt-3 min-h-10 text-sm leading-5 text-text-muted">
                                {tipo === 'carreras'
                                    ? item.universidad?.nombre_uni || 'Universidad pendiente de asociación.'
                                    : tipo === 'colegios'
                                      ? item.ubicacion_col || 'Ubicación pendiente de registro.'
                                      : item.descripcion_mat || 'Materia base de preparación preuniversitaria.'}
                            </p>
                            <div className="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-brand-bg p-4 text-xs">
                                {tipo === 'materias' ? (
                                    <>
                                        <div><p className="text-text-muted">Áreas</p><p className="mt-1 text-lg font-black text-text-main">{item.areas_count || 0}</p></div>
                                        <div><p className="text-text-muted">Temas</p><p className="mt-1 text-lg font-black text-text-main">{item.temas_count || 0}</p></div>
                                    </>
                                ) : (
                                    <>
                                        <div><p className="text-text-muted">Postulantes</p><p className="mt-1 text-lg font-black text-text-main">{item.postulantes_count || 0}</p></div>
                                        <div><p className="text-text-muted">{tipo === 'carreras' ? 'Exigencia matemática' : 'Tipo'}</p><p className="mt-1 break-words font-bold text-text-main">{tipo === 'carreras' ? item.nivel_exigencia_matematica_car || 'Sin clasificación' : item.tipo_col || 'No registrado'}</p></div>
                                    </>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyInstitutional
                    title={`Sin ${config.title.toLowerCase()} registrados`}
                    description="No existe información disponible para este catálogo institucional."
                />
            )}
        </AdminLayout>
    );
}
