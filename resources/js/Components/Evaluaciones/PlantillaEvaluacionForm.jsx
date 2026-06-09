import InputError from '@/Components/InputError';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Progress } from '@/Components/ui/progress';
import { Textarea } from '@/Components/ui/textarea';
import { Link, useForm } from '@inertiajs/react';
import { Save, Search } from 'lucide-react';
import { useMemo, useState } from 'react';

const redistribute = (questions) => {
    if (!questions.length) return [];
    const base = Math.floor(10000 / questions.length) / 100;
    return questions.map((question, index) => ({
        ...question,
        orden_pp: index + 1,
        puntaje_pp:
            index === questions.length - 1
                ? Number((100 - base * (questions.length - 1)).toFixed(2))
                : base,
    }));
};

export default function PlantillaEvaluacionForm({
    plantilla = null,
    preguntasDisponibles,
    submitRoute,
    method = 'post',
    submitLabel,
    onCancel = null,
}) {
    const selected =
        plantilla?.preguntas?.map((pregunta, index) => ({
            id_preg: pregunta.id_preg,
            orden_pp: pregunta.pivot?.orden_pp || index + 1,
            puntaje_pp: Number(pregunta.pivot?.puntaje_pp || 0),
        })) || [];
    const { data, setData, post, put, processing, errors } = useForm({
        nombre_plan: plantilla?.nombre_plan || '',
        descripcion_plan: plantilla?.descripcion_plan || '',
        objetivo_plan: plantilla?.objetivo_plan || '',
        duracion_minutos_plan: plantilla?.duracion_minutos_plan || 60,
        dificultad_plan: plantilla?.dificultad_plan || 'media',
        estado_plan: plantilla?.estado_plan || 'activa',
        preguntas: selected,
    });
    const [search, setSearch] = useState('');
    const filtered = useMemo(
        () =>
            preguntasDisponibles.filter((pregunta) =>
                `${pregunta.enunciado_preg} ${pregunta.tema?.nombre_tem || ''} ${pregunta.tema?.area?.nombre_area || ''}`
                    .toLowerCase()
                    .includes(search.toLowerCase()),
            ),
        [preguntasDisponibles, search],
    );
    const total = data.preguntas.reduce(
        (sum, item) => sum + Number(item.puntaje_pp || 0),
        0,
    );
    const selectedIds = new Set(data.preguntas.map((item) => item.id_preg));

    const toggle = (id) => {
        const next = selectedIds.has(id)
            ? data.preguntas.filter((item) => item.id_preg !== id)
            : [...data.preguntas, { id_preg: id }];
        setData('preguntas', redistribute(next));
    };

    const submit = (event) => {
        event.preventDefault();
        
        const options = {
            onSuccess: () => {
                if (onCancel) {
                    onCancel();
                }
            },
        };

        method === 'put' ? put(submitRoute, options) : post(submitRoute, options);
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200">
                <CardHeader className="border-b border-slate-100 p-5">
                    <CardTitle className="text-lg">
                        Configuración de la plantilla
                    </CardTitle>
                </CardHeader>
                <CardContent className="grid gap-5 p-5 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                        <Label htmlFor="nombre_plan">Nombre institucional *</Label>
                        <Input
                            id="nombre_plan"
                            className="mt-1.5"
                            value={data.nombre_plan}
                            onChange={(event) =>
                                setData('nombre_plan', event.target.value)
                            }
                            autoFocus
                        />
                        <InputError
                            className="mt-1"
                            message={errors.nombre_plan}
                        />
                    </div>
                    <div>
                        <Label htmlFor="duracion_minutos_plan">
                            Duración (minutos)
                        </Label>
                        <Input
                            id="duracion_minutos_plan"
                            type="number"
                            min="1"
                            className="mt-1.5"
                            value={data.duracion_minutos_plan}
                            onChange={(event) =>
                                setData(
                                    'duracion_minutos_plan',
                                    event.target.value,
                                )
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="dificultad_plan">Dificultad</Label>
                        <select
                            id="dificultad_plan"
                            className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm"
                            value={data.dificultad_plan}
                            onChange={(event) =>
                                setData('dificultad_plan', event.target.value)
                            }
                        >
                            {[
                                ['basica', 'Básica'],
                                ['basica-media', 'Básica - media'],
                                ['media', 'Media'],
                                ['media-alta', 'Media - alta'],
                                ['avanzada', 'Avanzada'],
                                ['mixta', 'Mixta'],
                            ].map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="sm:col-span-2">
                        <Label htmlFor="descripcion_plan">Descripción</Label>
                        <Textarea
                            id="descripcion_plan"
                            className="mt-1.5"
                            value={data.descripcion_plan}
                            onChange={(event) =>
                                setData('descripcion_plan', event.target.value)
                            }
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <Label htmlFor="objetivo_plan">Objetivo académico</Label>
                        <Textarea
                            id="objetivo_plan"
                            className="mt-1.5"
                            value={data.objetivo_plan}
                            onChange={(event) =>
                                setData('objetivo_plan', event.target.value)
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="estado_plan">Estado</Label>
                        <select
                            id="estado_plan"
                            className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm"
                            value={data.estado_plan}
                            onChange={(event) =>
                                setData('estado_plan', event.target.value)
                            }
                        >
                            <option value="activa">Activa</option>
                            <option value="inactiva">Inactiva</option>
                        </select>
                    </div>
                </CardContent>
            </Card>

            <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200">
                <CardHeader className="border-b border-slate-100 p-5">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <CardTitle className="text-lg">
                                Preguntas asociadas
                            </CardTitle>
                            <p className="mt-1 text-sm text-slate-500">
                                La ponderación se distribuye automáticamente y
                                puede ajustarse.
                            </p>
                        </div>
                        <div className="min-w-64">
                            <div className="mb-2 flex justify-between text-sm font-semibold">
                                <span>{data.preguntas.length} seleccionadas</span>
                                <span
                                    className={
                                        Math.abs(total - 100) < 0.01
                                            ? 'text-emerald-600'
                                            : 'text-amber-600'
                                    }
                                >
                                    {total.toFixed(2)} / 100 pts
                                </span>
                            </div>
                            <Progress value={Math.min(total, 100)} />
                        </div>
                    </div>
                </CardHeader>
                <CardContent className="p-5">
                    <div className="relative mb-4">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                        <Input
                            className="pl-9"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Buscar por enunciado, tema o área"
                        />
                    </div>
                    <div className="max-h-[34rem] space-y-2 overflow-y-auto pr-1">
                        {filtered.map((pregunta) => {
                            const current = data.preguntas.find(
                                (item) => item.id_preg === pregunta.id_preg,
                            );
                            return (
                                <div
                                    key={pregunta.id_preg}
                                    className={`grid gap-3 rounded-xl border p-3 md:grid-cols-[24px_1fr_110px] md:items-center ${
                                        current
                                            ? 'border-indigo-300 bg-indigo-50/50'
                                            : 'border-slate-200'
                                    }`}
                                >
                                    <input
                                        type="checkbox"
                                        checked={Boolean(current)}
                                        onChange={() => toggle(pregunta.id_preg)}
                                        className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <div>
                                        <p className="line-clamp-2 text-sm font-medium text-slate-800">
                                            {pregunta.enunciado_preg}
                                        </p>
                                        <div className="mt-2 flex flex-wrap gap-1.5">
                                            <Badge variant="outline">
                                                {pregunta.tema?.area
                                                    ?.nombre_area ||
                                                    'Área general'}
                                            </Badge>
                                            <span className="text-xs text-slate-500">
                                                {pregunta.tema?.nombre_tem ||
                                                    'Sin tema'}
                                            </span>
                                        </div>
                                    </div>
                                    {current && (
                                        <div>
                                            <Label
                                                htmlFor={`puntaje-${pregunta.id_preg}`}
                                                className="text-xs"
                                            >
                                                Puntaje
                                            </Label>
                                            <Input
                                                id={`puntaje-${pregunta.id_preg}`}
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                value={current.puntaje_pp}
                                                onChange={(event) =>
                                                    setData(
                                                        'preguntas',
                                                        data.preguntas.map(
                                                            (item) =>
                                                                item.id_preg ===
                                                                pregunta.id_preg
                                                                    ? {
                                                                          ...item,
                                                                          puntaje_pp:
                                                                              event
                                                                                  .target
                                                                                  .value,
                                                                      }
                                                                    : item,
                                                        ),
                                                    )
                                                }
                                            />
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                    <InputError className="mt-3" message={errors.preguntas} />
                </CardContent>
            </Card>

            <div className="flex justify-end gap-3">
                {onCancel ? (
                    <Button
                        type="button"
                        variant="outline"
                        className="h-10"
                        disabled={processing}
                        onClick={onCancel}
                    >
                        Cancelar
                    </Button>
                ) : (
                    <Button variant="outline" asChild>
                        <Link href={route('plantillas-evaluacion.index')}>
                            Cancelar
                        </Link>
                    </Button>
                )}
                <Button
                    type="submit"
                    disabled={processing}
                    className="bg-indigo-700 hover:bg-indigo-800"
                >
                    <Save className="h-4 w-4" />
                    {submitLabel}
                </Button>
            </div>
        </form>
    );
}
