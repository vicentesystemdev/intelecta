import StatusBadge from '@/Components/StatusBadge';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Clock3, Eye, FileCheck2, Pencil, Plus, Search, ToggleLeft } from 'lucide-react';
import { useState } from 'react';

export default function Index({ plantillas, filtros = {}, permisos }) {
    const [filters, setFilters] = useState({ buscar: filtros.buscar || '', estado_plan: filtros.estado_plan || '' });
    const submit = (event) => { event.preventDefault(); router.get(route('plantillas-evaluacion.index'), filters, { preserveState: true, replace: true }); };
    const changeStatus = (plantilla) => {
        if (window.confirm(`¿Confirma el cambio de estado de "${plantilla.nombre_plan}"?`)) router.patch(route('plantillas-evaluacion.cambiar-estado', plantilla.id_plan), {}, { preserveScroll: true });
    };
    return (
        <AdminLayout title="Plantillas de Evaluación" subtitle="Instrumentos académicos reutilizables construidos desde el banco institucional.">
            <Head title="Plantillas de evaluación" />
            <div className="mb-5 flex items-center justify-between"><p className="text-sm text-slate-500">{plantillas.total} plantillas configuradas</p>{permisos.crear && <Button asChild className="bg-indigo-700"><Link href={route('plantillas-evaluacion.create')}><Plus className="h-4 w-4" />Nueva plantilla</Link></Button>}</div>
            <Card className="mb-5 border-0 py-0 shadow-sm ring-1 ring-slate-200"><CardContent className="p-4"><form onSubmit={submit} className="flex flex-col gap-3 sm:flex-row"><div className="relative flex-1"><Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" /><Input className="pl-9" value={filters.buscar} onChange={(e) => setFilters({ ...filters, buscar: e.target.value })} placeholder="Buscar plantilla por nombre" /></div><select className="h-10 rounded-lg border-slate-200 text-sm sm:w-48" value={filters.estado_plan} onChange={(e) => setFilters({ ...filters, estado_plan: e.target.value })}><option value="">Todos los estados</option><option value="activa">Activas</option><option value="inactiva">Inactivas</option></select><Button className="bg-indigo-700">Filtrar</Button></form></CardContent></Card>
            <div className="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
                {plantillas.data.map((plantilla) => <Card key={plantilla.id_plan} className="gap-0 overflow-hidden border-0 py-0 shadow-sm ring-1 ring-slate-200"><div className="h-1.5 bg-gradient-to-r from-indigo-600 to-cyan-400" /><CardContent className="p-5"><div className="mb-4 flex items-start justify-between gap-3"><div className="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700"><FileCheck2 className="h-5 w-5" /></div><StatusBadge status={plantilla.estado_plan === 'activa' ? 'Activa' : 'Inactiva'} /></div><h2 className="min-h-14 font-semibold leading-6 text-slate-900">{plantilla.nombre_plan}</h2><p className="mt-2 line-clamp-2 min-h-10 text-sm text-slate-500">{plantilla.descripcion_plan || 'Instrumento académico institucional.'}</p><div className="mt-5 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-center"><div><p className="text-lg font-bold text-indigo-700">{plantilla.preguntas_count}</p><p className="text-[11px] text-slate-500">Preguntas</p></div><div><p className="text-lg font-bold text-indigo-700">{Number(plantilla.puntaje_total || 0).toFixed(0)}</p><p className="text-[11px] text-slate-500">Puntos</p></div><div><p className="flex items-center justify-center gap-1 text-lg font-bold text-indigo-700"><Clock3 className="h-4 w-4" />{plantilla.duracion_minutos_plan || '—'}</p><p className="text-[11px] text-slate-500">Minutos</p></div></div><div className="mt-4 flex items-center justify-between"><Badge variant="outline" className="capitalize">{plantilla.dificultad_plan?.replace('-', ' ') || 'No definida'}</Badge><div className="flex gap-1"><Button asChild variant="ghost" size="icon"><Link href={route('plantillas-evaluacion.show', plantilla.id_plan)}><Eye className="h-4 w-4" /></Link></Button>{permisos.editar && <Button asChild variant="ghost" size="icon"><Link href={route('plantillas-evaluacion.edit', plantilla.id_plan)}><Pencil className="h-4 w-4" /></Link></Button>}{permisos.cambiarEstado && <Button variant="ghost" size="icon" onClick={() => changeStatus(plantilla)}><ToggleLeft className="h-4 w-4" /></Button>}</div></div></CardContent></Card>)}
            </div>
        </AdminLayout>
    );
}
