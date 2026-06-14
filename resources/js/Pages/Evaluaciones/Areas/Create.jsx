import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

export default function Create({ materias = [] }) {
    const form = useForm({
        id_mat: '',
        nombre_area: '',
        descripcion_area: '',
        estado_area: 'activo',
    });

    return (
        <AdminLayout
            title="Nueva Área de Conocimiento"
            subtitle="Definición de una línea curricular vinculada con su materia académica."
        >
            <Head title="Nueva área" />
            <AreaForm
                {...form}
                materias={materias}
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('areas-conocimiento.store'));
                }}
                label="Registrar área"
            />
        </AdminLayout>
    );
}

export function AreaForm({
    data,
    setData,
    errors,
    processing,
    materias,
    onSubmit,
    label,
}) {
    return (
        <form onSubmit={onSubmit} className="mx-auto max-w-3xl space-y-5">
            <Card className="gap-0 border border-brand-border bg-brand-card py-0 shadow-sm">
                <CardHeader className="border-b border-brand-border p-5">
                    <CardTitle className="text-lg text-text-main">
                        Información curricular
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-5 p-5">
                    <div>
                        <Label htmlFor="id_mat">Materia *</Label>
                        <select
                            id="id_mat"
                            className="mt-1.5 h-10 w-full rounded-lg border border-brand-border bg-brand-card px-3 text-sm text-text-main outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary/15"
                            value={data.id_mat}
                            onChange={(event) => setData('id_mat', event.target.value)}
                        >
                            <option value="">Seleccione una materia</option>
                            {materias.map((materia) => (
                                <option key={materia.id_mat} value={materia.id_mat}>
                                    {materia.nombre_mat}
                                    {materia.estado_mat === 'inactivo' ? ' (inactiva)' : ''}
                                </option>
                            ))}
                        </select>
                        <InputError className="mt-1" message={errors.id_mat} />
                    </div>
                    <div>
                        <Label htmlFor="nombre_area">Nombre *</Label>
                        <Input
                            id="nombre_area"
                            className="mt-1.5"
                            value={data.nombre_area}
                            onChange={(event) =>
                                setData('nombre_area', event.target.value)
                            }
                            autoFocus
                        />
                        <InputError className="mt-1" message={errors.nombre_area} />
                    </div>
                    <div>
                        <Label htmlFor="descripcion_area">Descripción</Label>
                        <Textarea
                            id="descripcion_area"
                            className="mt-1.5 min-h-28"
                            value={data.descripcion_area}
                            onChange={(event) =>
                                setData('descripcion_area', event.target.value)
                            }
                        />
                        <InputError
                            className="mt-1"
                            message={errors.descripcion_area}
                        />
                    </div>
                    <div>
                        <Label htmlFor="estado_area">Estado</Label>
                        <select
                            id="estado_area"
                            className="mt-1.5 h-10 w-full rounded-lg border border-brand-border bg-brand-card px-3 text-sm text-text-main"
                            value={data.estado_area}
                            onChange={(event) =>
                                setData('estado_area', event.target.value)
                            }
                        >
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <InputError className="mt-1" message={errors.estado_area} />
                    </div>
                </CardContent>
            </Card>
            <div className="flex justify-end gap-3">
                <Button variant="outline" asChild>
                    <Link href={route('areas-conocimiento.index')}>Cancelar</Link>
                </Button>
                <Button
                    disabled={processing}
                    className="bg-brand-secondary text-white hover:bg-brand-secondary/90"
                >
                    <Save className="h-4 w-4" />
                    {label}
                </Button>
            </div>
        </form>
    );
}
