import {
    EmptyInstitutional,
    Field,
    FlashMessage,
    InstitutionalBanner,
    InstitutionalStatus,
    MetricTile,
    cardClass,
    primaryButtonClass,
    secondaryButtonClass,
    TextareaField,
} from '@/Components/Institucional/InstitutionalUi';
import ConfirmModal from '@/Components/ConfirmModal';
import ModalInstitucional from '@/Components/ModalInstitucional';
import AdminLayout from '@/Layouts/AdminLayout';
import { validationProps } from '@/lib/inputValidation';
import { Head, useForm, usePage } from '@inertiajs/react';
import {
    BookOpenCheck,
    BriefcaseBusiness,
    Building2,
    CheckCircle2,
    GraduationCap,
    Layers3,
    Pencil,
    Plus,
} from 'lucide-react';
import { useState } from 'react';

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

export default function Index({ tipo, items = [], metricas = {}, permisos = {} }) {
    const { flash } = usePage().props;
    const config = definitions[tipo] || definitions.carreras;
    const [modal, setModal] = useState(null);
    const [transition, setTransition] = useState(null);
    const form = useForm({ codigo_mat: '', nombre_mat: '', descripcion_mat: '' });
    const stateForm = useForm({ estado_mat: '' });
    const openMateria = (materia = null) => {
        form.clearErrors();
        form.setData({
            codigo_mat: materia?.codigo_mat || '',
            nombre_mat: materia?.nombre_mat || '',
            descripcion_mat: materia?.descripcion_mat || '',
        });
        setModal({ materia });
    };
    const submitMateria = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => setModal(null) };
        modal.materia
            ? form.put(route('admin.evaluaciones.materias.update', modal.materia.id_mat), options)
            : form.post(route('admin.evaluaciones.materias.store'), options);
    };
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
                action={tipo === 'materias' && permisos.crear ? <button className={primaryButtonClass} onClick={() => openMateria()}><Plus className="h-4 w-4" />Nueva materia</button> : null}
            />
            <FlashMessage message={flash?.success} />
            {stateForm.errors.estado_mat && <p role="alert" className="mb-4 text-sm font-semibold text-brand-danger">{stateForm.errors.estado_mat}</p>}
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
                            {tipo === 'materias' && (permisos.editar || permisos.cambiarEstado) && <div className="mt-4 flex flex-wrap justify-end gap-2 border-t border-brand-border pt-4">
                                {permisos.editar && <button className={secondaryButtonClass} onClick={() => openMateria(item)}><Pencil className="h-4 w-4" />Editar</button>}
                                {permisos.cambiarEstado && <button className={secondaryButtonClass} onClick={() => { stateForm.clearErrors(); stateForm.setData('estado_mat', item.estado_mat === 'activo' ? 'inactivo' : 'activo'); setTransition(item); }}>{item.estado_mat === 'activo' ? 'Inactivar' : 'Activar'}</button>}
                            </div>}
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyInstitutional
                    title={`Sin ${config.title.toLowerCase()} registrados`}
                    description="No existe información disponible para este catálogo institucional."
                />
            )}
            {tipo === 'materias' && <ModalInstitucional open={Boolean(modal)} onOpenChange={(open) => !open && !form.processing && setModal(null)} title={modal?.materia ? 'Editar materia' : 'Nueva materia'} description="Catálogo curricular raíz. Los nuevos registros comienzan activos.">
                <form onSubmit={submitMateria} className="space-y-4">
                    <Field {...validationProps('codigo_mat', { required: true, minLength: 2, maxLength: 60 })} label="Código *" value={form.data.codigo_mat} onChange={(event) => form.setData('codigo_mat', event.target.value)} error={form.errors.codigo_mat} />
                    <Field {...validationProps('nombre_mat', { required: true, minLength: 2, maxLength: 255 })} label="Nombre *" value={form.data.nombre_mat} onChange={(event) => form.setData('nombre_mat', event.target.value)} error={form.errors.nombre_mat} />
                    <TextareaField {...validationProps('descripcion_mat', { required: true, minLength: 10, maxLength: 3000 })} label="Descripción *" value={form.data.descripcion_mat} onChange={(event) => form.setData('descripcion_mat', event.target.value)} error={form.errors.descripcion_mat} />
                    <div className="flex justify-end gap-2"><button type="button" className={secondaryButtonClass} onClick={() => setModal(null)}>Cancelar</button><button className={primaryButtonClass} disabled={form.processing}>Guardar</button></div>
                </form>
            </ModalInstitucional>}
            {tipo === 'materias' && <ConfirmModal open={Boolean(transition)} onOpenChange={(open) => !open && !stateForm.processing && setTransition(null)} title="Cambiar estado de la materia" message={`${transition?.nombre_mat || ''} → ${stateForm.data.estado_mat}`} supportingText="No se elimina historial. Una materia con áreas activas no puede inactivarse." processing={stateForm.processing} onConfirm={() => stateForm.patch(route('admin.evaluaciones.materias.estado', transition.id_mat), { preserveScroll: true, onSuccess: () => setTransition(null) })} />}
        </AdminLayout>
    );
}
