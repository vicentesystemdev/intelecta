import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import {
    EmptyInstitutional,
    Field,
    FlashMessage,
    InstitutionalBanner,
    InstitutionalStatus,
    SelectField,
    TextareaField,
    cardClass,
    primaryButtonClass,
    secondaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    BookOpenCheck,
    CalendarDays,
    Eye,
    GraduationCap,
    Pencil,
    Plus,
    Search,
    Users,
} from 'lucide-react';
import { useState } from 'react';

const emptyProgram = {
    nombre_prog: '',
    codigo_prog: '',
    universidad_objetivo_prog: '',
    carrera_area_prog: '',
    modalidad_prog: '',
    fecha_inicio_prog: '',
    fecha_fin_prog: '',
    descripcion_prog: '',
    estado_prog: 'activo',
};

const localToday = () => {
    const date = new Date();
    return new Date(date.getTime() - date.getTimezoneOffset() * 60000)
        .toISOString()
        .slice(0, 10);
};

export default function Index({ programas, universidades = [], modalidades = [], filtros = {} }) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
        estado_prog: filtros.estado_prog || '',
        universidad_objetivo_prog: filtros.universidad_objetivo_prog || '',
        modalidad_prog: filtros.modalidad_prog || '',
    });
    const [formModal, setFormModal] = useState({ open: false, programa: null });
    const [detail, setDetail] = useState(null);
    const form = useForm(emptyProgram);

    const openForm = (programa = null) => {
        form.clearErrors();
        form.setData(
            programa
                ? {
                      ...emptyProgram,
                      ...programa,
                      fecha_inicio_prog: programa.fecha_inicio_prog?.slice(0, 10) || '',
                      fecha_fin_prog: programa.fecha_fin_prog?.slice(0, 10) || '',
                  }
                : emptyProgram,
        );
        setFormModal({ open: true, programa });
    };

    const submitForm = (event) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => setFormModal({ open: false, programa: null }),
        };
        if (formModal.programa) {
            form.put(route('admin.institucional.programas.update', formModal.programa.id_prog), options);
        } else {
            form.post(route('admin.institucional.programas.store'), options);
        }
    };

    const submitFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.programas.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AdminLayout title="Programas Académicos" subtitle="Ciclos de nivelación y preparación intensiva para postulantes de Ingeniería." wide>
            <Head title="Programas Académicos" />
            <InstitutionalBanner
                eyebrow="Gestión institucional"
                title="Programas Académicos"
                description="Organice ciclos de nivelación por universidad objetivo, área de formación, modalidad y periodo académico."
                icon={GraduationCap}
                action={
                    <button className={primaryButtonClass} onClick={() => openForm()}>
                        <Plus className="h-4 w-4" />
                        Nuevo programa
                    </button>
                }
            />
            <FlashMessage message={flash?.success} />

            <form onSubmit={submitFilters} className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-[minmax(220px,1fr)_180px_200px_180px_auto]`}>
                <div className="relative">
                    <Search className="absolute left-3 top-3 h-4 w-4 text-text-muted" />
                    <input
                        className="h-10 w-full rounded-xl border border-brand-border bg-brand-card pl-9 pr-3 text-sm text-text-main"
                        placeholder="Buscar programa"
                        value={filters.buscar}
                        onChange={(event) => setFilters({ ...filters, buscar: event.target.value })}
                    />
                </div>
                <select className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main" value={filters.estado_prog} onChange={(event) => setFilters({ ...filters, estado_prog: event.target.value })}>
                    <option value="">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
                <select className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main" value={filters.universidad_objetivo_prog} onChange={(event) => setFilters({ ...filters, universidad_objetivo_prog: event.target.value })}>
                    <option value="">Todas las universidades</option>
                    {universidades.map((item) => <option key={item} value={item}>{item}</option>)}
                </select>
                <select className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main" value={filters.modalidad_prog} onChange={(event) => setFilters({ ...filters, modalidad_prog: event.target.value })}>
                    <option value="">Todas las modalidades</option>
                    {modalidades.map((item) => <option key={item} value={item}>{item}</option>)}
                </select>
                <button className={primaryButtonClass}>Filtrar</button>
            </form>

            {programas.data.length ? (
                <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    {programas.data.map((programa) => (
                        <article key={programa.id_prog} className={`${cardClass} flex flex-col overflow-hidden`}>
                            <div className="h-1.5 bg-gradient-to-r from-brand-primary to-brand-secondary" />
                            <div className="flex flex-1 flex-col p-5 sm:p-6">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary">
                                            {programa.codigo_prog || 'Programa institucional'}
                                        </p>
                                        <h3 className="mt-2 break-words text-lg font-black leading-snug text-text-main">
                                            {programa.nombre_prog}
                                        </h3>
                                    </div>
                                    <InstitutionalStatus status={programa.estado_prog} />
                                </div>
                                <p className="mt-3 line-clamp-3 text-sm leading-6 text-text-muted">
                                    {programa.descripcion_prog || 'Ciclo académico de preparación preuniversitaria.'}
                                </p>
                                <div className="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-brand-bg p-4 text-xs">
                                    <div><p className="text-text-muted">Universidad</p><p className="mt-1 font-bold text-text-main">{programa.universidad_objetivo_prog || 'Enfoque múltiple'}</p></div>
                                    <div><p className="text-text-muted">Modalidad</p><p className="mt-1 font-bold text-text-main">{programa.modalidad_prog || 'Por definir'}</p></div>
                                    <div><p className="text-text-muted">Grupos</p><p className="mt-1 font-bold text-text-main">{programa.grupos_count}</p></div>
                                    <div><p className="text-text-muted">Inscritos</p><p className="mt-1 font-bold text-text-main">{programa.inscritos_count}</p></div>
                                </div>
                                <div className="mt-5 flex items-center justify-between border-t border-brand-border pt-4">
                                    <span className="flex items-center gap-1.5 text-xs text-text-muted">
                                        <CalendarDays className="h-4 w-4" />
                                        {programa.fecha_inicio_prog ? new Date(`${programa.fecha_inicio_prog.slice(0, 10)}T00:00:00`).toLocaleDateString('es-BO') : 'Fecha abierta'}
                                    </span>
                                    <div className="flex gap-1">
                                        <button className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30 hover:text-text-main" onClick={() => setDetail(programa)} aria-label="Ver detalle"><Eye className="h-4 w-4" /></button>
                                        <button className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30 hover:text-text-main" onClick={() => openForm(programa)} aria-label="Editar programa"><Pencil className="h-4 w-4" /></button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyInstitutional title="No hay programas con estos filtros" description="Registre un programa académico o ajuste los criterios de búsqueda." />
            )}
            <Pagination links={programas.links} />

            <ModalInstitucional open={formModal.open} onOpenChange={(open) => setFormModal((current) => ({ ...current, open }))} title={formModal.programa ? 'Editar programa académico' : 'Nuevo programa académico'} description="Configure el ciclo de nivelación, su enfoque y vigencia." size="lg">
                <form onSubmit={submitForm} className="grid gap-4 sm:grid-cols-2">
                    <Field label="Nombre del programa" value={form.data.nombre_prog} onChange={(e) => form.setData('nombre_prog', e.target.value)} error={form.errors.nombre_prog} className="sm:col-span-2" />
                    <Field label="Código" value={form.data.codigo_prog} onChange={(e) => form.setData('codigo_prog', e.target.value)} error={form.errors.codigo_prog} />
                    <Field label="Universidad objetivo" value={form.data.universidad_objetivo_prog} onChange={(e) => form.setData('universidad_objetivo_prog', e.target.value)} error={form.errors.universidad_objetivo_prog} />
                    <Field label="Carrera o área" value={form.data.carrera_area_prog} onChange={(e) => form.setData('carrera_area_prog', e.target.value)} error={form.errors.carrera_area_prog} />
                    <Field label="Modalidad" value={form.data.modalidad_prog} onChange={(e) => form.setData('modalidad_prog', e.target.value)} error={form.errors.modalidad_prog} />
                    <Field type="date" min={formModal.programa ? undefined : localToday()} label="Fecha de inicio" value={form.data.fecha_inicio_prog} onChange={(e) => form.setData('fecha_inicio_prog', e.target.value)} error={form.errors.fecha_inicio_prog} />
                    <Field type="date" min={form.data.fecha_inicio_prog || undefined} label="Fecha de finalización" value={form.data.fecha_fin_prog} onChange={(e) => form.setData('fecha_fin_prog', e.target.value)} error={form.errors.fecha_fin_prog} />
                    <SelectField label="Estado" value={form.data.estado_prog} onChange={(e) => form.setData('estado_prog', e.target.value)} error={form.errors.estado_prog}><option value="activo">Activo</option><option value="inactivo">Inactivo</option></SelectField>
                    <TextareaField label="Descripción" value={form.data.descripcion_prog} onChange={(e) => form.setData('descripcion_prog', e.target.value)} error={form.errors.descripcion_prog} className="sm:col-span-2" />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button type="button" className={secondaryButtonClass} onClick={() => setFormModal({ open: false, programa: null })}>Cancelar</button>
                        <button className={primaryButtonClass} disabled={form.processing}>{formModal.programa ? 'Guardar cambios' : 'Crear programa'}</button>
                    </div>
                </form>
            </ModalInstitucional>

            <ModalInstitucional open={Boolean(detail)} onOpenChange={(open) => !open && setDetail(null)} title="Detalle del programa académico" description="Resumen operativo del ciclo de nivelación." size="md">
                {detail && <div className="space-y-5">
                    <div className="rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary p-5 text-white"><p className="text-xs font-bold uppercase tracking-wider text-brand-accent">{detail.codigo_prog || 'Programa institucional'}</p><h3 className="mt-2 text-xl font-black">{detail.nombre_prog}</h3><p className="mt-2 text-sm leading-6 text-slate-200">{detail.descripcion_prog || 'Sin descripción registrada.'}</p></div>
                    <div className="grid gap-3 sm:grid-cols-2">
                        {[['Universidad objetivo', detail.universidad_objetivo_prog], ['Carrera o área', detail.carrera_area_prog], ['Modalidad', detail.modalidad_prog], ['Grupos configurados', detail.grupos_count], ['Postulantes inscritos', detail.inscritos_count], ['Simulacros', detail.simulacros_count]].map(([label, value]) => <div key={label} className="rounded-xl border border-brand-border p-4"><p className="text-xs text-text-muted">{label}</p><p className="mt-1 font-bold text-text-main">{value || 'No registrado'}</p></div>)}
                    </div>
                </div>}
            </ModalInstitucional>
        </AdminLayout>
    );
}
