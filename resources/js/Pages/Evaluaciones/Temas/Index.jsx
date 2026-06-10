import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';

export default function Index({ temas, areas, filtros = {}, permisos }) {
    return (
        <AdminLayout title="Temas Académicos" subtitle="Desagregación temática de las áreas de conocimiento.">
            <Head title="Temas académicos" />
            <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <select className="h-10 rounded-lg border-slate-200 text-sm sm:w-72" value={filtros.id_area || ''} onChange={(e) => router.get(route('temas.index'), { id_area: e.target.value }, { preserveState: true, replace: true })}>
                    <option value="">Todas las áreas</option>
                    {areas.map((area) => <option key={area.id_area} value={area.id_area}>{area.nombre_area}</option>)}
                </select>
                {permisos.crear && <Button asChild className="bg-brand-primary hover:bg-brand-primary/90"><Link href={route('temas.create')}><Plus className="h-4 w-4" />Nuevo tema</Link></Button>}
            </div>
            <Card className="border-0 py-0 shadow-sm ring-1 ring-slate-200"><CardContent className="px-0"><Table>
                <TableHeader><TableRow className="bg-slate-50"><TableHead className="pl-5">Tema</TableHead><TableHead>Área</TableHead><TableHead>Nivel</TableHead><TableHead>Preguntas</TableHead><TableHead>Estado</TableHead><TableHead className="pr-5 text-right">Acciones</TableHead></TableRow></TableHeader>
                <TableBody>{temas.data.map((tema) => <TableRow key={tema.id_tem}>
                    <TableCell className="pl-5 font-semibold">{tema.nombre_tem}</TableCell><TableCell>{tema.area?.nombre_area}</TableCell><TableCell className="capitalize">{tema.nivel_tem || 'No definido'}</TableCell><TableCell>{tema.preguntas_count}</TableCell><TableCell><StatusBadge status={tema.estado_tem === 'activo' ? 'Activo' : 'Inactivo'} /></TableCell>
                    <TableCell className="pr-5 text-right">{permisos.editar && <Button asChild variant="ghost" size="icon"><Link href={route('temas.edit', tema.id_tem)}><Pencil className="h-4 w-4" /></Link></Button>}</TableCell>
                </TableRow>)}</TableBody>
            </Table></CardContent></Card>
        </AdminLayout>
    );
}
