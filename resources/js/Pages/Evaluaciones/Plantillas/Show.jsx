import StatusBadge from '@/Components/StatusBadge';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Progress } from '@/Components/ui/progress';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Clock3, Pencil } from 'lucide-react';

export default function Show({ plantilla, permisos }) {
    const total = plantilla.preguntas.reduce((sum, pregunta) => sum + Number(pregunta.pivot?.puntaje_pp || 0), 0);
    return (
        <AdminLayout title="Detalle de Plantilla" subtitle="Estructura y ponderación del instrumento académico.">
            <Head title={plantilla.nombre_plan} />
            <div className="mx-auto max-w-6xl space-y-5">
                <div className="flex justify-between"><Button asChild variant="outline"><Link href={route('plantillas-evaluacion.index')}><ArrowLeft className="h-4 w-4" />Volver a plantillas</Link></Button>{permisos.editar && <Button asChild className="bg-indigo-700"><Link href={route('plantillas-evaluacion.edit', plantilla.id_plan)}><Pencil className="h-4 w-4" />Editar plantilla</Link></Button>}</div>
                <Card className="gap-0 overflow-hidden border-0 py-0 shadow-sm ring-1 ring-slate-200"><div className="h-2 bg-gradient-to-r from-indigo-700 via-blue-500 to-cyan-400" /><CardContent className="p-6"><div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"><div className="max-w-3xl"><div className="mb-3 flex flex-wrap gap-2"><StatusBadge status={plantilla.estado_plan === 'activa' ? 'Activa' : 'Inactiva'} /><Badge variant="outline" className="capitalize">{plantilla.dificultad_plan?.replace('-', ' ')}</Badge></div><h2 className="text-2xl font-bold text-slate-900">{plantilla.nombre_plan}</h2><p className="mt-3 text-sm leading-6 text-slate-600">{plantilla.descripcion_plan}</p>{plantilla.objetivo_plan && <div className="mt-4 rounded-xl bg-indigo-50 p-4"><p className="text-xs font-semibold uppercase tracking-wider text-indigo-600">Objetivo académico</p><p className="mt-1 text-sm text-indigo-900">{plantilla.objetivo_plan}</p></div>}</div><div className="grid min-w-64 grid-cols-3 gap-3 text-center"><div className="rounded-xl bg-slate-50 p-3"><p className="text-xl font-bold text-indigo-700">{plantilla.preguntas.length}</p><p className="text-xs text-slate-500">Preguntas</p></div><div className="rounded-xl bg-slate-50 p-3"><p className="text-xl font-bold text-indigo-700">{total.toFixed(0)}</p><p className="text-xs text-slate-500">Puntos</p></div><div className="rounded-xl bg-slate-50 p-3"><p className="flex items-center justify-center gap-1 text-xl font-bold text-indigo-700"><Clock3 className="h-4 w-4" />{plantilla.duracion_minutos_plan}</p><p className="text-xs text-slate-500">Minutos</p></div></div></div></CardContent></Card>
                <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200"><CardHeader className="border-b p-5"><div className="flex items-center justify-between"><CardTitle className="text-lg">Composición académica</CardTitle><div className="w-52"><div className="mb-1 flex justify-between text-xs font-semibold"><span>Ponderación</span><span className={Math.abs(total - 100) < 0.01 ? 'text-emerald-600' : 'text-amber-600'}>{total.toFixed(2)} / 100</span></div><Progress value={Math.min(total, 100)} /></div></div></CardHeader><CardContent className="divide-y p-0">{plantilla.preguntas.map((pregunta, index) => <div key={pregunta.id_preg} className="grid gap-3 p-5 md:grid-cols-[40px_1fr_100px] md:items-center"><span className="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 font-bold text-indigo-700">{index + 1}</span><div><p className="font-medium leading-6 text-slate-900">{pregunta.enunciado_preg}</p><p className="mt-1 text-xs text-slate-500">{pregunta.tema?.area?.nombre_area || 'Área general'} · {pregunta.tema?.nombre_tem || 'Sin tema'}</p></div><div className="text-right"><p className="text-lg font-bold text-indigo-700">{Number(pregunta.pivot?.puntaje_pp || 0).toFixed(2)}</p><p className="text-xs text-slate-500">puntos</p></div></div>)}</CardContent></Card>
            </div>
        </AdminLayout>
    );
}
