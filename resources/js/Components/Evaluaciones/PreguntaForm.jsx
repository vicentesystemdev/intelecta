import AlternativasEditor from '@/Components/Evaluaciones/AlternativasEditor';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { useMemo } from 'react';

const letters = ['A', 'B', 'C', 'D', 'E'];
const makeAlternatives = (count) =>
    letters.slice(0, count).map((letter, index) => ({
        texto_alt: '',
        letra_alt: letter,
        es_correcta_alt: false,
        orden_alt: index + 1,
        estado_alt: 'activo',
    }));

export default function PreguntaForm({
    pregunta = null,
    opciones,
    submitRoute,
    method = 'post',
    submitLabel,
}) {
    const existing = pregunta?.alternativas?.map((item, index) => ({
        texto_alt: item.texto_alt,
        letra_alt: item.letra_alt || letters[index],
        es_correcta_alt: Boolean(item.es_correcta_alt),
        orden_alt: item.orden_alt || index + 1,
        estado_alt: item.estado_alt || 'activo',
    }));
    const initialType = pregunta?.tipo_preg || 'opcion_multiple';
    const { data, setData, post, put, processing, errors } = useForm({
        id_area: pregunta?.tema?.id_area || '',
        id_tem: pregunta?.id_tem || '',
        enunciado_preg: pregunta?.enunciado_preg || '',
        tipo_preg: initialType,
        dificultad_preg: pregunta?.dificultad_preg || 'media',
        puntaje_preg: pregunta?.puntaje_preg || 1,
        explicacion_preg: pregunta?.explicacion_preg || '',
        estado_preg: pregunta?.estado_preg || 'activo',
        alternativas:
            existing ||
            makeAlternatives(initialType === 'verdadero_falso' ? 2 : 5),
    });

    const temas = useMemo(
        () =>
            opciones.temas.filter(
                (tema) =>
                    !data.id_area ||
                    String(tema.id_area) === String(data.id_area),
            ),
        [data.id_area, opciones.temas],
    );

    const changeType = (tipo) => {
        const count =
            tipo === 'opcion_multiple' ? 5 : tipo === 'verdadero_falso' ? 2 : 0;
        setData({
            ...data,
            tipo_preg: tipo,
            alternativas: makeAlternatives(count),
        });
    };

    const submit = (event) => {
        event.preventDefault();
        method === 'put' ? put(submitRoute) : post(submitRoute);
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200">
                <CardHeader className="border-b border-slate-100 p-5">
                    <CardTitle className="text-lg">Contenido académico</CardTitle>
                </CardHeader>
                <CardContent className="grid gap-5 p-5 sm:grid-cols-2">
                    <div>
                        <Label htmlFor="id_area">Área de conocimiento</Label>
                        <select
                            id="id_area"
                            className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm"
                            value={data.id_area}
                            onChange={(event) =>
                                setData({
                                    ...data,
                                    id_area: event.target.value,
                                    id_tem: '',
                                })
                            }
                        >
                            <option value="">Todas las áreas</option>
                            {opciones.areas.map((area) => (
                                <option key={area.id_area} value={area.id_area}>
                                    {area.nombre_area}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <Label htmlFor="id_tem">Tema</Label>
                        <select
                            id="id_tem"
                            className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm"
                            value={data.id_tem}
                            onChange={(event) =>
                                setData('id_tem', event.target.value)
                            }
                        >
                            <option value="">Sin tema asignado</option>
                            {temas.map((tema) => (
                                <option key={tema.id_tem} value={tema.id_tem}>
                                    {tema.nombre_tem}
                                </option>
                            ))}
                        </select>
                        <InputError className="mt-1" message={errors.id_tem} />
                    </div>
                    <div className="sm:col-span-2">
                        <Label htmlFor="enunciado_preg">Enunciado *</Label>
                        <Textarea
                            id="enunciado_preg"
                            className="mt-1.5 min-h-28"
                            value={data.enunciado_preg}
                            onChange={(event) =>
                                setData('enunciado_preg', event.target.value)
                            }
                            autoFocus
                        />
                        <InputError
                            className="mt-1"
                            message={errors.enunciado_preg}
                        />
                    </div>
                    <div>
                        <Label htmlFor="tipo_preg">Tipo de pregunta *</Label>
                        <select
                            id="tipo_preg"
                            className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm"
                            value={data.tipo_preg}
                            onChange={(event) => changeType(event.target.value)}
                        >
                            <option value="opcion_multiple">
                                Opción múltiple
                            </option>
                            <option value="verdadero_falso">
                                Verdadero o falso
                            </option>
                            <option value="respuesta_corta">
                                Respuesta corta
                            </option>
                        </select>
                    </div>
                    <div>
                        <Label htmlFor="dificultad_preg">Dificultad</Label>
                        <select
                            id="dificultad_preg"
                            className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm"
                            value={data.dificultad_preg}
                            onChange={(event) =>
                                setData('dificultad_preg', event.target.value)
                            }
                        >
                            <option value="basica">Básica</option>
                            <option value="media">Media</option>
                            <option value="avanzada">Avanzada</option>
                        </select>
                    </div>
                    <div>
                        <Label htmlFor="puntaje_preg">
                            Puntaje de referencia
                        </Label>
                        <Input
                            id="puntaje_preg"
                            type="number"
                            min="0.01"
                            step="0.01"
                            className="mt-1.5"
                            value={data.puntaje_preg}
                            onChange={(event) =>
                                setData('puntaje_preg', event.target.value)
                            }
                        />
                        <InputError
                            className="mt-1"
                            message={errors.puntaje_preg}
                        />
                    </div>
                    <div>
                        <Label htmlFor="estado_preg">Estado</Label>
                        <select
                            id="estado_preg"
                            className="mt-1.5 h-10 w-full rounded-lg border-slate-200 text-sm"
                            value={data.estado_preg}
                            onChange={(event) =>
                                setData('estado_preg', event.target.value)
                            }
                        >
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div className="sm:col-span-2">
                        <Label htmlFor="explicacion_preg">
                            Explicación de la respuesta
                        </Label>
                        <Textarea
                            id="explicacion_preg"
                            className="mt-1.5 min-h-24"
                            value={data.explicacion_preg}
                            onChange={(event) =>
                                setData('explicacion_preg', event.target.value)
                            }
                        />
                    </div>
                </CardContent>
            </Card>

            <Card className="gap-0 border-0 py-0 shadow-sm ring-1 ring-slate-200">
                <CardContent className="p-5">
                    <AlternativasEditor
                        alternativas={data.alternativas}
                        onChange={(value) => setData('alternativas', value)}
                        tipo={data.tipo_preg}
                        errors={errors}
                    />
                </CardContent>
            </Card>

            <div className="flex justify-end gap-3">
                <Button variant="outline" asChild>
                    <Link href={route('preguntas.index')}>Cancelar</Link>
                </Button>
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
