import Pagination from '@/Components/Pagination';
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
import { Head, Link, router } from '@inertiajs/react';
import { BookOpenCheck, Pencil, Plus } from 'lucide-react';
import { useState } from 'react';

export default function Index({ areas, materias = [], filtros = {}, permisos }) {
    const [materiaId, setMateriaId] = useState(filtros.id_mat || '');

    const filter = (event) => {
        event.preventDefault();
        router.get(
            route('areas-conocimiento.index'),
            { id_mat: materiaId },
            { preserveState: true, replace: true },
        );
    };

    return (
        <AdminLayout
            title="Áreas de Conocimiento"
            subtitle="Organización curricular por materia para el banco académico institucional."
        >
            <Head title="Áreas de conocimiento" />
            <div className="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <form onSubmit={filter} className="flex w-full gap-2 sm:max-w-lg">
                    <label className="min-w-0 flex-1">
                        <span className="mb-1.5 block text-xs font-bold text-text-main">
                            Filtrar por materia
                        </span>
                        <select
                            className="h-10 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main"
                            value={materiaId}
                            onChange={(event) => setMateriaId(event.target.value)}
                        >
                            <option value="">Todas las materias</option>
                            {materias.map((materia) => (
                                <option key={materia.id_mat} value={materia.id_mat}>
                                    {materia.nombre_mat}
                                </option>
                            ))}
                        </select>
                    </label>
                    <Button type="submit" variant="outline">
                        Aplicar
                    </Button>
                </form>
                {permisos.crear && (
                    <Button
                        asChild
                        className="bg-brand-secondary text-white hover:bg-brand-secondary/90"
                    >
                        <Link href={route('areas-conocimiento.create')}>
                            <Plus className="h-4 w-4" />
                            Nueva área
                        </Link>
                    </Button>
                )}
            </div>
            <Card className="border border-brand-border bg-brand-card py-0 shadow-sm">
                <CardContent className="px-0">
                    <Table className="table-fixed">
                        <TableHeader>
                            <TableRow className="bg-brand-bg">
                                <TableHead className="w-[20%] pl-5">Área</TableHead>
                                <TableHead className="w-[18%]">Materia</TableHead>
                                <TableHead className="w-[35%]">Descripción</TableHead>
                                <TableHead className="w-[9%]">Temas</TableHead>
                                <TableHead className="w-[10%]">Estado</TableHead>
                                <TableHead className="w-[8%] pr-5 text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {areas.data.map((area) => (
                                <TableRow key={area.id_area}>
                                    <TableCell className="whitespace-normal break-words pl-5 font-semibold leading-snug text-text-main">
                                        {area.nombre_area}
                                    </TableCell>
                                    <TableCell className="whitespace-normal break-words leading-snug">
                                        {area.materia?.nombre_mat || (
                                            <span className="font-semibold text-brand-warning">
                                                Sin materia asignada
                                            </span>
                                        )}
                                    </TableCell>
                                    <TableCell className="whitespace-normal break-words leading-snug text-text-muted">
                                        {area.descripcion_area || 'Sin descripción'}
                                    </TableCell>
                                    <TableCell>{area.temas_count} temas</TableCell>
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
                                                    aria-label={`Editar ${area.nombre_area}`}
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
                                    <TableCell
                                        colSpan="6"
                                        className="h-40 text-center text-text-muted"
                                    >
                                        <BookOpenCheck className="mx-auto mb-2 h-8 w-8 text-brand-border" />
                                        No existen áreas registradas con este filtro.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
            <Pagination links={areas.links} />
        </AdminLayout>
    );
}
