import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

export default function Create({ areas }) {
    const form = useForm({ id_area: '', nombre_tem: '', descripcion_tem: '', nivel_tem: 'intermedio', estado_tem: 'activo' });
    return <AdminLayout title="Nuevo Tema Académico" subtitle="Clasificación temática para el banco de preguntas."><Head title="Nuevo tema" /><TemaForm {...form} areas={areas} onSubmit={(e) => { e.preventDefault(); form.post(route('temas.store')); }} label="Registrar tema" /></AdminLayout>;
}

export function TemaForm({ data, setData, errors, processing, areas, onSubmit, label }) {
    return <form onSubmit={onSubmit} className="mx-auto max-w-3xl space-y-5"><Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200"><CardHeader className="border-b p-5"><CardTitle className="text-lg">Información temática</CardTitle></CardHeader><CardContent className="grid gap-5 p-5 sm:grid-cols-2">
        <div><Label>Área *</Label><select className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm" value={data.id_area} onChange={(e) => setData('id_area', e.target.value)}><option value="">Seleccione un área</option>{areas.map((area) => <option key={area.id_area} value={area.id_area}>{area.nombre_area}</option>)}</select><InputError className="mt-1" message={errors.id_area} /></div>
        <div><Label>Nivel</Label><select className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm" value={data.nivel_tem} onChange={(e) => setData('nivel_tem', e.target.value)}><option value="basico">Básico</option><option value="intermedio">Intermedio</option><option value="avanzado">Avanzado</option></select></div>
        <div className="sm:col-span-2"><Label>Nombre *</Label><Input className="mt-1.5" value={data.nombre_tem} onChange={(e) => setData('nombre_tem', e.target.value)} autoFocus /><InputError className="mt-1" message={errors.nombre_tem} /></div>
        <div className="sm:col-span-2"><Label>Descripción</Label><Textarea className="mt-1.5" value={data.descripcion_tem} onChange={(e) => setData('descripcion_tem', e.target.value)} /></div>
        <div><Label>Estado</Label><select className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm" value={data.estado_tem} onChange={(e) => setData('estado_tem', e.target.value)}><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
    </CardContent></Card><div className="flex justify-end gap-3"><Button variant="outline" asChild><Link href={route('temas.index')}>Cancelar</Link></Button><Button disabled={processing} className="bg-indigo-700"><Save className="h-4 w-4" />{label}</Button></div></form>;
}
