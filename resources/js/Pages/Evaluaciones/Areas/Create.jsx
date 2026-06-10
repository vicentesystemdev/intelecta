import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        nombre_area: '',
        descripcion_area: '',
        estado_area: 'activo',
    });
    return (
        <AdminLayout title="Nueva Área de Conocimiento" subtitle="Definición de una línea curricular para el banco institucional.">
            <Head title="Nueva área" />
            <AreaForm data={data} setData={setData} errors={errors} processing={processing} onSubmit={(e) => { e.preventDefault(); post(route('areas-conocimiento.store')); }} label="Registrar área" />
        </AdminLayout>
    );
}

export function AreaForm({ data, setData, errors, processing, onSubmit, label }) {
    return (
        <form onSubmit={onSubmit} className="mx-auto max-w-3xl space-y-5">
            <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200">
                <CardHeader className="border-b p-5"><CardTitle className="text-lg">Información curricular</CardTitle></CardHeader>
                <CardContent className="space-y-5 p-5">
                    <div><Label htmlFor="nombre_area">Nombre *</Label><Input id="nombre_area" className="mt-1.5" value={data.nombre_area} onChange={(e) => setData('nombre_area', e.target.value)} autoFocus /><InputError className="mt-1" message={errors.nombre_area} /></div>
                    <div><Label htmlFor="descripcion_area">Descripción</Label><Textarea id="descripcion_area" className="mt-1.5 min-h-28" value={data.descripcion_area} onChange={(e) => setData('descripcion_area', e.target.value)} /></div>
                    <div><Label htmlFor="estado_area">Estado</Label><select id="estado_area" className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm" value={data.estado_area} onChange={(e) => setData('estado_area', e.target.value)}><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
                </CardContent>
            </Card>
            <div className="flex justify-end gap-3"><Button variant="outline" asChild><Link href={route('areas-conocimiento.index')}>Cancelar</Link></Button><Button disabled={processing} className="bg-brand-primary"><Save className="h-4 w-4" />{label}</Button></div>
        </form>
    );
}
