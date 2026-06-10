import StatusBadge from '@/Components/StatusBadge';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2, Pencil } from 'lucide-react';

export default function Show({ pregunta, permisos }) {
    return (
        <AdminLayout title="Detalle de Pregunta" subtitle="Revisión académica del enunciado, clasificación y respuesta esperada.">
            <Head title={`Pregunta ${pregunta.id_preg}`} />
            <div className="mx-auto max-w-5xl space-y-5">
                <div className="flex justify-between"><Button asChild variant="outline"><Link href={route('preguntas.index')}><ArrowLeft className="h-4 w-4" />Volver al banco</Link></Button>{permisos.editar && <Button asChild className="bg-brand-primary hover:bg-brand-primary/90"><Link href={route('preguntas.edit', pregunta.id_preg)}><Pencil className="h-4 w-4" />Editar</Link></Button>}</div>
                <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200"><CardHeader className="border-b p-5"><div className="flex flex-wrap items-center gap-2"><Badge className="bg-brand-primary">{pregunta.tema?.area?.materia?.nombre_mat || 'Materia general'}</Badge><Badge>{pregunta.tema?.area?.nombre_area || 'Área general'}</Badge><Badge variant="outline">{pregunta.tema?.nombre_tem || 'Sin tema'}</Badge><Badge variant="outline" className="capitalize">{pregunta.dificultad_preg}</Badge><StatusBadge status={pregunta.estado_preg === 'activo' ? 'Activo' : 'Inactivo'} /></div></CardHeader><CardContent className="p-6"><p className="text-lg font-semibold leading-8 text-slate-900">{pregunta.enunciado_preg}</p><div className="mt-5 grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2"><p><span className="font-semibold">Habilidad:</span> {pregunta.habilidad_preg || 'Clasificación pendiente'}</p><p><span className="font-semibold">Exigencia:</span> {pregunta.exigencia_preg || 'Contenido común'}</p><p><span className="font-semibold">Subtema:</span> {pregunta.subtema_preg || 'No especificado'}</p><p><span className="font-semibold">Tiempo estimado:</span> {pregunta.tiempo_estimado_seg_preg ? `${pregunta.tiempo_estimado_seg_preg} segundos` : 'No especificado'}</p></div></CardContent></Card>
                {pregunta.alternativas.length > 0 && <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200"><CardHeader className="border-b p-5"><CardTitle className="text-lg">Alternativas</CardTitle></CardHeader><CardContent className="space-y-3 p-5">{pregunta.alternativas.map((alt) => <div key={alt.id_alt} className={`flex items-center gap-3 rounded-xl border p-4 ${alt.es_correcta_alt ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200'}`}><span className="flex h-8 w-8 items-center justify-center rounded-lg bg-white font-bold text-brand-primary ring-1 ring-slate-200">{alt.letra_alt}</span><p className="flex-1 text-sm">{alt.texto_alt}</p>{alt.es_correcta_alt && <CheckCircle2 className="h-5 w-5 text-emerald-600" />}</div>)}</CardContent></Card>}
                {pregunta.explicacion_preg && <Card className="border-brand-secondary/30 bg-brand-secondary/5 py-0"><CardContent className="p-5"><p className="mb-2 text-sm font-semibold text-brand-primary">Fundamento de la respuesta</p><p className="text-sm leading-6 text-slate-700">{pregunta.explicacion_preg}</p></CardContent></Card>}
            </div>
        </AdminLayout>
    );
}
