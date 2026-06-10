import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { BookOpenCheck, Pencil, Plus } from 'lucide-react';

export default function Index({ areas, permisos }) {
    return (
        <AdminLayout
            title="Áreas de Conocimiento"
            subtitle="Organización curricular del banco lógico-matemático institucional."
        >
            <Head title="Áreas de conocimiento" />
            <div className="mb-5 flex justify-between">
                <p className="text-sm text-slate-500">
                    {areas.total} áreas académicas configuradas
                </p>
                {permisos.crear && (
                    <Button asChild className="bg-brand-primary hover:bg-brand-primary/90">
                        <Link href={route('areas-conocimiento.create')}>
                            <Plus className="h-4 w-4" /> Nueva área
                        </Link>
                    </Button>
                )}
            </div>
            <Card className="border-0 py-0 shadow-sm ring-1 ring-slate-200">
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-slate-50">
                                <TableHead className="pl-5">Área</TableHead>
                                <TableHead>Descripción</TableHead>
                                <TableHead>Temas</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="pr-5 text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {areas.data.map((area) => (
                                <TableRow key={area.id_area}>
                                    <TableCell className="pl-5 font-semibold">
                                        {area.nombre_area}
                                    </TableCell>
                                    <TableCell className="max-w-xl text-slate-600">
                                        {area.descripcion_area || 'Sin descripción'}
                                    </TableCell>
                                    <TableCell>
                                        {area.temas_count} temas
                                    </TableCell>
                                    <TableCell>
                                        <StatusBadge
                                            status={
                                                area.estado_area === 'activo'
                                                    ? 'Activo'
                                                    : 'Inactivo'
                                            }
                                        />
                                    </TableCell>
                                    <TableCell className="pr-5 text-right">
                                        {permisos.editar && (
                                            <Button asChild variant="ghost" size="icon">
                                                <Link
                                                    href={route(
                                                        'areas-conocimiento.edit',
                                                        area.id_area,
                                                    )}
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                            {!areas.data.length && (
                                <TableRow>
                                    <TableCell colSpan="5" className="h-40 text-center text-slate-500">
                                        <BookOpenCheck className="mx-auto mb-2 h-8 w-8 text-slate-300" />
                                        No existen áreas registradas.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </AdminLayout>
    );
}
