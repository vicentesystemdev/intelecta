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
import { Head, router, useForm } from '@inertiajs/react';
import { BookOpenCheck, Filter, Pencil, Plus, X } from 'lucide-react';
import { useState } from 'react';

const emptyForm = {
    id_mat: '',
    nombre_area: '',
    descripcion_area: '',
    estado_area: 'activo',
};

function FieldError({ message }) {
    if (!message) return null;

    return <p className="mt-1 text-xs font-semibold text-brand-danger">{message}</p>;
}

export default function Index({ areas, materias = [], filtros = {}, permisos = {} }) {
    const [materiaId, setMateriaId] = useState(filtros.id_mat || '');
    const [modalOpen, setModalOpen] = useState(false);
    const [editingArea, setEditingArea] = useState(null);
    const form = useForm(emptyForm);

    const filter = (event) => {
        event.preventDefault();

        router.get(
            route('areas-conocimiento.index'),
            { id_mat: materiaId || undefined },
            { preserveState: true, replace: true },
        );
    };

    const openCreateModal = () => {
        setEditingArea(null);
        form.clearErrors();
        form.setData(emptyForm);
        setModalOpen(true);
    };

    const openEditModal = (area) => {
        setEditingArea(area);
        form.clearErrors();
        form.setData({
            id_mat: area.id_mat ? String(area.id_mat) : '',
            nombre_area: area.nombre_area || '',
            descripcion_area: area.descripcion_area || '',
            estado_area: area.estado_area || 'activo',
        });
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditingArea(null);
        form.clearErrors();
    };

    const submitArea = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: closeModal,
        };

        if (editingArea) {
            form.put(route('areas-conocimiento.update', editingArea.id_area), options);
            return;
        }

        form.post(route('areas-conocimiento.store'), options);
    };

    return (
        <AdminLayout
            title="Áreas de Conocimiento"
            subtitle="Organización curricular por materia para el banco académico institucional."
        >
            <Head title="Áreas de conocimiento" />

            <div className="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <form onSubmit={filter} className="flex w-full flex-col gap-3 sm:max-w-2xl sm:flex-row sm:items-end">
                    <label className="min-w-0 flex-1">
                        <span className="mb-1.5 block text-xs font-bold text-text-main">
                            Filtrar por materia
                        </span>
                        <select
                            className="h-10 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main shadow-sm focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
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
                    <button
                        type="submit"
                        className="inline-flex h-10 items-center justify-center rounded-xl bg-brand-secondary px-4 text-sm font-bold text-white shadow-md transition-all hover:bg-brand-secondary/90 active:scale-95"
                    >
                        <Filter className="mr-2 h-4 w-4" />
                        Filtrar
                    </button>
                </form>

                {permisos.crear && (
                    <button
                        type="button"
                        onClick={openCreateModal}
                        className="inline-flex h-10 items-center justify-center rounded-xl bg-brand-secondary px-4 text-sm font-bold text-white shadow-md transition-all hover:bg-brand-secondary/90 active:scale-95"
                    >
                        <Plus className="mr-2 h-4 w-4" />
                        Nueva área
                    </button>
                )}
            </div>

            <Card className="rounded-2xl border border-brand-border bg-brand-card py-0 shadow-sm">
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
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => openEditModal(area)}
                                                aria-label={`Editar ${area.nombre_area}`}
                                                className="text-text-muted hover:text-brand-secondary"
                                            >
                                                <Pencil className="h-4 w-4" />
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

            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-2xl rounded-2xl border border-brand-border bg-brand-card p-5 shadow-2xl dark:shadow-black/40 sm:p-6">
                        <div className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2 className="text-lg font-black text-text-main">
                                    {editingArea ? 'Editar área de conocimiento' : 'Nueva área de conocimiento'}
                                </h2>
                                <p className="mt-1 text-sm leading-6 text-text-muted">
                                    Vincula cada área con su materia para conservar la estructura Materia → Área → Tema.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={closeModal}
                                className="rounded-xl border border-brand-border p-2 text-text-muted transition hover:bg-brand-border/30 hover:text-text-main"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        <form onSubmit={submitArea} className="space-y-4">
                            <div>
                                <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                    Materia obligatoria
                                </label>
                                <select
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    value={form.data.id_mat}
                                    onChange={(event) => form.setData('id_mat', event.target.value)}
                                >
                                    <option value="">Seleccione una materia</option>
                                    {materias.map((materia) => (
                                        <option key={materia.id_mat} value={materia.id_mat}>
                                            {materia.nombre_mat}
                                            {materia.estado_mat === 'inactivo' ? ' (inactiva)' : ''}
                                        </option>
                                    ))}
                                </select>
                                <FieldError message={form.errors.id_mat} />
                            </div>

                            <div>
                                <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                    Nombre del área
                                </label>
                                <input
                                    type="text"
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    value={form.data.nombre_area}
                                    onChange={(event) => form.setData('nombre_area', event.target.value)}
                                    autoFocus
                                />
                                <FieldError message={form.errors.nombre_area} />
                            </div>

                            <div>
                                <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                    Descripción
                                </label>
                                <textarea
                                    rows="3"
                                    className="mt-1 w-full rounded-xl border border-brand-border bg-brand-card px-3 py-2 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    value={form.data.descripcion_area}
                                    onChange={(event) => form.setData('descripcion_area', event.target.value)}
                                />
                                <FieldError message={form.errors.descripcion_area} />
                            </div>

                            <div>
                                <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                    Estado
                                </label>
                                <select
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    value={form.data.estado_area}
                                    onChange={(event) => form.setData('estado_area', event.target.value)}
                                >
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                                <FieldError message={form.errors.estado_area} />
                            </div>

                            <div className="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="inline-flex h-10 items-center justify-center rounded-xl border border-brand-border bg-transparent px-4 text-sm font-bold text-text-main transition-all hover:bg-brand-border/30"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="inline-flex h-10 items-center justify-center rounded-xl bg-brand-secondary px-4 text-sm font-bold text-white shadow-md transition-all hover:bg-brand-secondary/90 active:scale-95 disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {form.processing ? 'Guardando...' : editingArea ? 'Guardar cambios' : 'Registrar área'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
