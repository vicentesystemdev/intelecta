import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { Filter, Pencil, Plus, X } from 'lucide-react';
import { useState } from 'react';

const emptyForm = {
    id_area: '',
    nombre_tem: '',
    descripcion_tem: '',
    nivel_tem: '',
    estado_tem: 'activo',
};

function FieldError({ message }) {
    if (!message) return null;

    return <p className="mt-1 text-xs font-semibold text-brand-danger">{message}</p>;
}

export default function Index({ temas, areas, filtros = {}, permisos = {} }) {
    const [filterArea, setFilterArea] = useState(filtros.id_area || '');
    const [modalOpen, setModalOpen] = useState(false);
    const [editingTema, setEditingTema] = useState(null);
    const form = useForm(emptyForm);

    const openCreateModal = () => {
        setEditingTema(null);
        form.clearErrors();
        form.setData(emptyForm);
        setModalOpen(true);
    };

    const openEditModal = (tema) => {
        setEditingTema(tema);
        form.clearErrors();
        form.setData({
            id_area: tema.id_area ? String(tema.id_area) : '',
            nombre_tem: tema.nombre_tem || '',
            descripcion_tem: tema.descripcion_tem || '',
            nivel_tem: tema.nivel_tem || '',
            estado_tem: tema.estado_tem || 'activo',
        });
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditingTema(null);
        form.clearErrors();
    };

    const submitFilter = (event) => {
        event.preventDefault();

        router.get(
            route('temas.index'),
            { id_area: filterArea || undefined },
            { preserveState: true, replace: true },
        );
    };

    const submitTema = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: closeModal,
        };

        if (editingTema) {
            form.put(route('temas.update', editingTema.id_tem), options);
            return;
        }

        form.post(route('temas.store'), options);
    };

    return (
        <AdminLayout title="Temas Académicos" subtitle="Desagregación temática de las áreas de conocimiento.">
            <Head title="Temas académicos" />

            <form onSubmit={submitFilter} className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <select
                        className="h-10 rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main shadow-sm focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20 sm:w-80"
                        value={filterArea}
                        onChange={(event) => setFilterArea(event.target.value)}
                    >
                        <option value="">Todas las áreas</option>
                        {areas.map((area) => (
                            <option key={area.id_area} value={area.id_area}>
                                {area.nombre_area}
                            </option>
                        ))}
                    </select>
                    <button
                        type="submit"
                        className="inline-flex h-10 items-center justify-center rounded-xl bg-brand-secondary px-4 text-sm font-bold text-white shadow-md transition-all hover:bg-brand-secondary/90 active:scale-95"
                    >
                        <Filter className="mr-2 h-4 w-4" />
                        Filtrar
                    </button>
                </div>

                {permisos.crear && (
                    <button
                        type="button"
                        onClick={openCreateModal}
                        className="inline-flex h-10 items-center justify-center rounded-xl bg-brand-secondary px-4 text-sm font-bold text-white shadow-md transition-all hover:bg-brand-secondary/90 active:scale-95"
                    >
                        <Plus className="mr-2 h-4 w-4" />
                        Nuevo tema
                    </button>
                )}
            </form>

            <Card className="rounded-2xl border border-brand-border bg-brand-card py-0 shadow-sm">
                <CardContent className="px-0">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-brand-bg/60">
                                <TableHead className="pl-5">Tema</TableHead>
                                <TableHead>Área</TableHead>
                                <TableHead>Nivel</TableHead>
                                <TableHead>Preguntas</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="pr-5 text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {temas.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={6} className="px-5 py-8 text-center text-sm font-semibold text-text-muted">
                                        No se encontraron temas académicos con los filtros aplicados.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                temas.data.map((tema) => (
                                    <TableRow key={tema.id_tem}>
                                        <TableCell className="pl-5 font-semibold text-text-main">{tema.nombre_tem}</TableCell>
                                        <TableCell className="text-text-muted">{tema.area?.nombre_area || 'Sin área asignada'}</TableCell>
                                        <TableCell className="capitalize text-text-muted">{tema.nivel_tem || 'No definido'}</TableCell>
                                        <TableCell className="text-text-muted">{tema.preguntas_count}</TableCell>
                                        <TableCell>
                                            <StatusBadge status={tema.estado_tem === 'activo' ? 'Activo' : 'Inactivo'} />
                                        </TableCell>
                                        <TableCell className="pr-5 text-right">
                                            {permisos.editar && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => openEditModal(tema)}
                                                    className="text-text-muted hover:text-brand-secondary"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-2xl rounded-2xl border border-brand-border bg-brand-card p-5 shadow-2xl dark:shadow-black/40 sm:p-6">
                        <div className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2 className="text-lg font-black text-text-main">
                                    {editingTema ? 'Editar tema académico' : 'Crear tema académico'}
                                </h2>
                                <p className="mt-1 text-sm leading-6 text-text-muted">
                                    Registra la clasificación temática asociada a un área de conocimiento.
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

                        <form onSubmit={submitTema} className="space-y-4">
                            <div>
                                <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                    Área de conocimiento
                                </label>
                                <select
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    value={form.data.id_area}
                                    onChange={(event) => form.setData('id_area', event.target.value)}
                                >
                                    <option value="">Seleccionar área</option>
                                    {areas.map((area) => (
                                        <option key={area.id_area} value={area.id_area}>
                                            {area.nombre_area}
                                        </option>
                                    ))}
                                </select>
                                <FieldError message={form.errors.id_area} />
                            </div>

                            <div>
                                <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                    Nombre del tema
                                </label>
                                <input
                                    type="text"
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    value={form.data.nombre_tem}
                                    onChange={(event) => form.setData('nombre_tem', event.target.value)}
                                />
                                <FieldError message={form.errors.nombre_tem} />
                            </div>

                            <div>
                                <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                    Descripción
                                </label>
                                <textarea
                                    rows="3"
                                    className="mt-1 w-full rounded-xl border border-brand-border bg-brand-card px-3 py-2 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    value={form.data.descripcion_tem}
                                    onChange={(event) => form.setData('descripcion_tem', event.target.value)}
                                />
                                <FieldError message={form.errors.descripcion_tem} />
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                        Nivel
                                    </label>
                                    <select
                                        className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                        value={form.data.nivel_tem}
                                        onChange={(event) => form.setData('nivel_tem', event.target.value)}
                                    >
                                        <option value="">No definido</option>
                                        <option value="basico">Básico</option>
                                        <option value="intermedio">Intermedio</option>
                                        <option value="avanzado">Avanzado</option>
                                    </select>
                                    <FieldError message={form.errors.nivel_tem} />
                                </div>

                                <div>
                                    <label className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                        Estado
                                    </label>
                                    <select
                                        className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                        value={form.data.estado_tem}
                                        onChange={(event) => form.setData('estado_tem', event.target.value)}
                                    >
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                    <FieldError message={form.errors.estado_tem} />
                                </div>
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
                                    {form.processing ? 'Guardando...' : editingTema ? 'Guardar cambios' : 'Crear tema'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
