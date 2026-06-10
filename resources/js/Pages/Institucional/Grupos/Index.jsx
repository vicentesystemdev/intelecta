import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import {
    EmptyInstitutional,
    Field,
    FlashMessage,
    InstitutionalBanner,
    InstitutionalStatus,
    SelectField,
    cardClass,
    primaryButtonClass,
    secondaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { DoorOpen, Eye, Pencil, Plus, School, UserRoundCheck, Users } from 'lucide-react';
import { useState } from 'react';

const emptyGroup = {
    id_prog: '',
    nombre_grupo: '',
    codigo_grupo: '',
    turno_grupo: '',
    aula_grupo: '',
    capacidad_grupo: 30,
    nivel_grupo: '',
    tutor_responsable_grupo: '',
    estado_grupo: 'activo',
};

export default function Index({ grupos, programas = [], filtros = {} }) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        id_prog: filtros.id_prog || '',
        turno_grupo: filtros.turno_grupo || '',
        nivel_grupo: filtros.nivel_grupo || '',
        estado_grupo: filtros.estado_grupo || '',
    });
    const [formModal, setFormModal] = useState({ open: false, grupo: null });
    const [detail, setDetail] = useState(null);
    const form = useForm(emptyGroup);

    const openForm = (grupo = null) => {
        form.clearErrors();
        form.setData(grupo ? { ...emptyGroup, ...grupo, id_prog: String(grupo.id_prog) } : emptyGroup);
        setFormModal({ open: true, grupo });
    };

    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => setFormModal({ open: false, grupo: null }) };
        formModal.grupo
            ? form.put(route('admin.institucional.grupos.update', formModal.grupo.id_grupo), options)
            : form.post(route('admin.institucional.grupos.store'), options);
    };

    const filter = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.grupos.index'), filters, { preserveState: true, replace: true });
    };

    return (
        <AdminLayout title="Grupos y Paralelos" subtitle="Organización operativa de postulantes por programa, turno y nivel." wide>
            <Head title="Grupos y Paralelos" />
            <InstitutionalBanner eyebrow="Gestión institucional" title="Grupos y Paralelos" description="Administre cupos, aulas, turnos y tutoría responsable para cada ciclo de nivelación." icon={School} action={<button className={primaryButtonClass} onClick={() => openForm()}><Plus className="h-4 w-4" />Nuevo grupo</button>} />
            <FlashMessage message={flash?.success} />
            <form onSubmit={filter} className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-5`}>
                <select className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main" value={filters.id_prog} onChange={(e) => setFilters({ ...filters, id_prog: e.target.value })}><option value="">Todos los programas</option>{programas.map((programa) => <option key={programa.id_prog} value={programa.id_prog}>{programa.nombre_prog}</option>)}</select>
                <input className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main" placeholder="Turno" value={filters.turno_grupo} onChange={(e) => setFilters({ ...filters, turno_grupo: e.target.value })} />
                <input className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main" placeholder="Nivel" value={filters.nivel_grupo} onChange={(e) => setFilters({ ...filters, nivel_grupo: e.target.value })} />
                <select className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main" value={filters.estado_grupo} onChange={(e) => setFilters({ ...filters, estado_grupo: e.target.value })}><option value="">Todos los estados</option><option value="activo">Activos</option><option value="inactivo">Inactivos</option></select>
                <button className={primaryButtonClass}>Filtrar</button>
            </form>
            {grupos.data.length ? <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">{grupos.data.map((grupo) => {
                const occupied = Number(grupo.inscritos_count || 0);
                const capacity = Number(grupo.capacidad_grupo || 0);
                const percentage = capacity ? Math.min(100, Math.round((occupied / capacity) * 100)) : 0;
                return <article key={grupo.id_grupo} className={`${cardClass} p-5 sm:p-6`}>
                    <div className="flex items-start justify-between gap-3"><div className="min-w-0"><p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary">{grupo.codigo_grupo || 'Grupo académico'}</p><h3 className="mt-2 break-words text-lg font-black text-text-main">{grupo.nombre_grupo}</h3><p className="mt-1 text-xs text-text-muted">{grupo.programa?.nombre_prog}</p></div><InstitutionalStatus status={grupo.estado_grupo} /></div>
                    <div className="mt-5 grid grid-cols-2 gap-3 text-xs"><div className="rounded-xl bg-brand-bg p-3"><p className="text-text-muted">Turno</p><p className="mt-1 font-bold text-text-main">{grupo.turno_grupo || 'Por definir'}</p></div><div className="rounded-xl bg-brand-bg p-3"><p className="text-text-muted">Aula</p><p className="mt-1 font-bold text-text-main">{grupo.aula_grupo || 'Por definir'}</p></div><div className="rounded-xl bg-brand-bg p-3"><p className="text-text-muted">Nivel</p><p className="mt-1 font-bold text-text-main">{grupo.nivel_grupo || 'General'}</p></div><div className="rounded-xl bg-brand-bg p-3"><p className="text-text-muted">Simulacros</p><p className="mt-1 font-bold text-text-main">{grupo.simulacros_count}</p></div></div>
                    <div className="mt-5"><div className="flex justify-between text-xs"><span className="font-bold text-text-main">Cupos ocupados</span><span className="text-text-muted">{occupied}/{capacity}</span></div><div className="mt-2 h-2 overflow-hidden rounded-full bg-brand-border"><div className="h-full rounded-full bg-brand-secondary" style={{ width: `${percentage}%` }} /></div></div>
                    <div className="mt-5 flex items-center justify-between border-t border-brand-border pt-4"><span className="flex min-w-0 items-center gap-2 text-xs text-text-muted"><UserRoundCheck className="h-4 w-4 shrink-0" /><span className="truncate">{grupo.tutor_responsable_grupo || 'Tutor por asignar'}</span></span><div className="flex gap-1"><button className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30" onClick={() => setDetail(grupo)}><Eye className="h-4 w-4" /></button><button className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30" onClick={() => openForm(grupo)}><Pencil className="h-4 w-4" /></button></div></div>
                </article>;
            })}</div> : <EmptyInstitutional title="No hay grupos disponibles" description="Registre un grupo/paralelo para comenzar la asignación de postulantes." />}
            <Pagination links={grupos.links} />

            <ModalInstitucional open={formModal.open} onOpenChange={(open) => setFormModal((current) => ({ ...current, open }))} title={formModal.grupo ? 'Editar grupo académico' : 'Nuevo grupo académico'} description="Defina programa, cupos, turno y tutor responsable." size="lg">
                <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                    <SelectField label="Programa académico" value={form.data.id_prog} onChange={(e) => form.setData('id_prog', e.target.value)} error={form.errors.id_prog} className="sm:col-span-2"><option value="">Seleccione un programa</option>{programas.map((programa) => <option key={programa.id_prog} value={programa.id_prog}>{programa.nombre_prog}</option>)}</SelectField>
                    <Field label="Nombre del grupo" value={form.data.nombre_grupo} onChange={(e) => form.setData('nombre_grupo', e.target.value)} error={form.errors.nombre_grupo} />
                    <Field label="Código" value={form.data.codigo_grupo} onChange={(e) => form.setData('codigo_grupo', e.target.value)} error={form.errors.codigo_grupo} />
                    <Field label="Turno" value={form.data.turno_grupo} onChange={(e) => form.setData('turno_grupo', e.target.value)} error={form.errors.turno_grupo} />
                    <Field label="Aula" value={form.data.aula_grupo} onChange={(e) => form.setData('aula_grupo', e.target.value)} error={form.errors.aula_grupo} />
                    <Field type="number" min="1" label="Capacidad" value={form.data.capacidad_grupo} onChange={(e) => form.setData('capacidad_grupo', e.target.value)} error={form.errors.capacidad_grupo} />
                    <Field label="Nivel" value={form.data.nivel_grupo} onChange={(e) => form.setData('nivel_grupo', e.target.value)} error={form.errors.nivel_grupo} />
                    <Field label="Tutor responsable" value={form.data.tutor_responsable_grupo} onChange={(e) => form.setData('tutor_responsable_grupo', e.target.value)} error={form.errors.tutor_responsable_grupo} />
                    <SelectField label="Estado" value={form.data.estado_grupo} onChange={(e) => form.setData('estado_grupo', e.target.value)} error={form.errors.estado_grupo}><option value="activo">Activo</option><option value="inactivo">Inactivo</option></SelectField>
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2"><button type="button" className={secondaryButtonClass} onClick={() => setFormModal({ open: false, grupo: null })}>Cancelar</button><button className={primaryButtonClass} disabled={form.processing}>{formModal.grupo ? 'Guardar cambios' : 'Crear grupo'}</button></div>
                </form>
            </ModalInstitucional>
            <ModalInstitucional open={Boolean(detail)} onOpenChange={(open) => !open && setDetail(null)} title="Detalle del grupo/paralelo" description="Configuración operativa y ocupación actual.">
                {detail && <div className="space-y-4"><div className="rounded-2xl bg-brand-primary p-5 text-white"><p className="text-xs font-bold text-brand-accent">{detail.codigo_grupo}</p><h3 className="mt-2 text-xl font-black">{detail.nombre_grupo}</h3><p className="mt-2 text-sm text-slate-300">{detail.programa?.nombre_prog}</p></div><div className="grid grid-cols-2 gap-3">{[['Turno', detail.turno_grupo], ['Aula', detail.aula_grupo], ['Nivel', detail.nivel_grupo], ['Capacidad', detail.capacidad_grupo], ['Inscritos', detail.inscritos_count], ['Tutor responsable', detail.tutor_responsable_grupo]].map(([label, value]) => <div key={label} className="rounded-xl border border-brand-border p-3"><p className="text-xs text-text-muted">{label}</p><p className="mt-1 font-bold text-text-main">{value || 'No registrado'}</p></div>)}</div></div>}
            </ModalInstitucional>
        </AdminLayout>
    );
}
