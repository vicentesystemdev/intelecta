import StatusBadge from '@/Components/StatusBadge';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Eye, Pencil, Plus, RotateCcw, Search, ToggleLeft } from 'lucide-react';
import { useMemo, useState } from 'react';

const typeLabel = { opcion_multiple: 'Opción múltiple', verdadero_falso: 'Verdadero / falso', respuesta_corta: 'Respuesta corta' };

export default function Index({ preguntas, opciones, filtros = {}, permisos }) {
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '', id_area: filtros.id_area || '', id_tem: filtros.id_tem || '',
        dificultad_preg: filtros.dificultad_preg || '', tipo_preg: filtros.tipo_preg || '', estado_preg: filtros.estado_preg || '',
    });
    const temas = useMemo(() => opciones.temas.filter((tema) => !filters.id_area || String(tema.id_area) === String(filters.id_area)), [filters.id_area, opciones.temas]);
    const submit = (event) => { event.preventDefault(); router.get(route('preguntas.index'), filters, { preserveState: true, replace: true }); };
    const reset = () => { setFilters({ buscar: '', id_area: '', id_tem: '', dificultad_preg: '', tipo_preg: '', estado_preg: '' }); router.get(route('preguntas.index')); };
    const changeStatus = (pregunta) => {
        if (window.confirm(`¿Confirma el cambio de estado de la pregunta ${pregunta.id_preg}?`)) router.patch(route('preguntas.cambiar-estado', pregunta.id_preg), {}, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Banco de Preguntas" subtitle="Repositorio académico para evaluaciones lógico-matemáticas preuniversitarias.">
            <Head title="Banco de preguntas" />
            <div className="mb-5 flex items-center justify-between"><p className="text-sm text-slate-500">{preguntas.total} preguntas registradas</p>{permisos.crear && <Button asChild className="bg-indigo-700"><Link href={route('preguntas.create')}><Plus className="h-4 w-4" />Nueva pregunta</Link></Button>}</div>
            <Card className="mb-5 border-0 py-0 shadow-sm ring-1 ring-slate-200"><CardContent className="p-4"><form onSubmit={submit} className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div className="relative xl:col-span-2"><Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" /><Input className="pl-9" value={filters.buscar} onChange={(e) => setFilters({ ...filters, buscar: e.target.value })} placeholder="Buscar por contenido del enunciado" /></div>
                <select className="h-10 rounded-lg border-slate-200 text-sm" value={filters.id_area} onChange={(e) => setFilters({ ...filters, id_area: e.target.value, id_tem: '' })}><option value="">Todas las áreas</option>{opciones.areas.map((area) => <option key={area.id_area} value={area.id_area}>{area.nombre_area}</option>)}</select>
                <select className="h-10 rounded-lg border-slate-200 text-sm" value={filters.id_tem} onChange={(e) => setFilters({ ...filters, id_tem: e.target.value })}><option value="">Todos los temas</option>{temas.map((tema) => <option key={tema.id_tem} value={tema.id_tem}>{tema.nombre_tem}</option>)}</select>
                <select className="h-10 rounded-lg border-slate-200 text-sm" value={filters.dificultad_preg} onChange={(e) => setFilters({ ...filters, dificultad_preg: e.target.value })}><option value="">Toda dificultad</option><option value="basica">Básica</option><option value="media">Media</option><option value="avanzada">Avanzada</option></select>
                <select className="h-10 rounded-lg border-slate-200 text-sm" value={filters.tipo_preg} onChange={(e) => setFilters({ ...filters, tipo_preg: e.target.value })}><option value="">Todos los tipos</option>{Object.entries(typeLabel).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select>
                <select className="h-10 rounded-lg border-slate-200 text-sm" value={filters.estado_preg} onChange={(e) => setFilters({ ...filters, estado_preg: e.target.value })}><option value="">Todos los estados</option><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select>
                <div className="flex gap-2"><Button className="bg-indigo-700">Filtrar</Button><Button type="button" variant="outline" size="icon" onClick={reset}><RotateCcw className="h-4 w-4" /></Button></div>
            </form></CardContent></Card>
            <Card className="border-0 py-0 shadow-sm ring-1 ring-slate-200"><CardContent className="px-0"><Table>
                <TableHeader><TableRow className="bg-slate-50"><TableHead className="pl-5">Enunciado</TableHead><TableHead>Área / Tema</TableHead><TableHead>Tipo</TableHead><TableHead>Dificultad</TableHead><TableHead>Estado</TableHead><TableHead className="pr-5 text-right">Acciones</TableHead></TableRow></TableHeader>
                <TableBody>{preguntas.data.map((pregunta) => <TableRow key={pregunta.id_preg}>
                    <TableCell className="max-w-md pl-5"><p className="line-clamp-2 font-medium text-slate-900">{pregunta.enunciado_preg}</p><span className="text-xs text-slate-400">Código P-{String(pregunta.id_preg).padStart(4, '0')}</span></TableCell>
                    <TableCell><p className="text-sm font-medium">{pregunta.tema?.area?.nombre_area || 'Área general'}</p><p className="text-xs text-slate-500">{pregunta.tema?.nombre_tem || 'Sin tema'}</p></TableCell>
                    <TableCell><Badge variant="outline">{typeLabel[pregunta.tipo_preg]}</Badge></TableCell><TableCell className="capitalize">{pregunta.dificultad_preg || 'No definida'}</TableCell><TableCell><StatusBadge status={pregunta.estado_preg === 'activo' ? 'Activo' : 'Inactivo'} /></TableCell>
                    <TableCell className="pr-5"><div className="flex justify-end gap-1"><Button asChild variant="ghost" size="icon"><Link href={route('preguntas.show', pregunta.id_preg)}><Eye className="h-4 w-4" /></Link></Button>{permisos.editar && <Button asChild variant="ghost" size="icon"><Link href={route('preguntas.edit', pregunta.id_preg)}><Pencil className="h-4 w-4" /></Link></Button>}{permisos.cambiarEstado && <Button variant="ghost" size="icon" onClick={() => changeStatus(pregunta)}><ToggleLeft className="h-4 w-4" /></Button>}</div></TableCell>
                </TableRow>)}</TableBody>
            </Table></CardContent></Card>
            {preguntas.links.length > 3 && <div className="mt-5 flex justify-center gap-1">{preguntas.links.map((link, index) => <Button key={index} asChild={Boolean(link.url)} disabled={!link.url} size="sm" variant={link.active ? 'default' : 'outline'} className={link.active ? 'bg-indigo-700' : ''}>{link.url ? <Link href={link.url} dangerouslySetInnerHTML={{ __html: link.label }} /> : <span dangerouslySetInnerHTML={{ __html: link.label }} />}</Button>)}</div>}
        </AdminLayout>
    );
}
