import { formatDateLatam } from '@/lib/dateOnly';
import { inputOptions, validationProps } from '@/lib/inputValidation';
import InputError from '@/Components/InputError';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { useMemo } from 'react';

const fieldClass =
    'mt-1.5 h-10 border-slate-200 bg-white text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

export default function PostulanteForm({
    postulante = null,
    opciones,
    gestionActual,
    submitRoute,
    method = 'post',
    submitLabel,
    onCancel = null,
}) {
    const { data, setData, post, put, processing, errors } = useForm({
        nombres_post: postulante?.nombres_post || '',
        apellidos_post: postulante?.apellidos_post || '',
        ci_post: postulante?.ci_post || '',
        email_post: postulante?.email_post || '',
        celular_post: postulante?.celular_post || '',
        fecha_nacimiento_post: formatDateLatam(postulante?.fecha_nacimiento_post),
        id_col: postulante?.id_col || '',
        crear_otro_colegio: false,
        otro_colegio_nombre: '',
        id_uni: postulante?.carrera?.id_uni || '',
        crear_otra_universidad: false,
        otra_universidad_nombre: '',
        otra_universidad_sigla: '',
        id_car: postulante?.id_car || '',
        crear_otra_carrera: false,
        otra_carrera_nombre: '',
        turno_post: postulante?.turno_post || '',
        gestion_post: postulante?.gestion_post || gestionActual,
        estado_post: postulante?.estado_post || 'activo',
        observaciones_post: postulante?.observaciones_post || '',
    });

    const submit = (event) => {
        event.preventDefault();

        const options = {
            onSuccess: () => {
                if (onCancel) {
                    onCancel();
                }
            },
        };

        if (method === 'put') {
            put(submitRoute, options);
            return;
        }

        post(submitRoute, options);
    };

    const field = (name, value) => setData(name, value);
    const carrerasDisponibles = useMemo(
        () =>
            data.id_uni
                ? opciones.carreras.filter(
                      (carrera) =>
                          String(carrera.id_uni) === String(data.id_uni),
                  )
                : [],
        [data.id_uni, opciones.carreras],
    );
    const universidadIndicada = Boolean(data.id_uni || data.crear_otra_universidad);

    const changeUniversidad = (value) => {
        if (value === '__otro__') {
            setData({
                ...data,
                id_uni: '',
                id_car: '',
                crear_otra_universidad: true,
                crear_otra_carrera: false,
                otra_carrera_nombre: '',
            });
            return;
        }
        const carreraActual = opciones.carreras.find(
            (carrera) => String(carrera.id_car) === String(data.id_car),
        );

        setData({
            ...data,
            id_uni: value,
            crear_otra_universidad: false,
            otra_universidad_nombre: '',
            otra_universidad_sigla: '',
            id_car:
                carreraActual &&
                String(carreraActual.id_uni) === String(value)
                    ? data.id_car
                    : '',
            crear_otra_carrera: false,
            otra_carrera_nombre: '',
        });
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <Card className="gap-0 border-0 bg-white py-0 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-950/70 dark:ring-slate-800">
                <CardHeader className="border-b border-slate-100 p-5 dark:border-slate-800">
                    <CardTitle className="text-lg text-slate-900 dark:text-slate-100">
                        Información personal
                    </CardTitle>
                </CardHeader>
                <CardContent className="grid gap-5 p-5 sm:grid-cols-2">
                    <div>
                        <Label htmlFor="nombres_post">Nombres *</Label>
                        <Input {...validationProps('nombres_post')}
                            id="nombres_post"
                            className={fieldClass}
                            value={data.nombres_post}
                            onChange={(event) =>
                                field('nombres_post', event.target.value)
                            }
                            autoFocus
                        />
                        <InputError
                            className="mt-1.5"
                            message={errors.nombres_post}
                        />
                    </div>
                    <div>
                        <Label htmlFor="apellidos_post">Apellidos *</Label>
                        <Input {...validationProps('apellidos_post')}
                            id="apellidos_post"
                            className={fieldClass}
                            value={data.apellidos_post}
                            onChange={(event) =>
                                field('apellidos_post', event.target.value)
                            }
                        />
                        <InputError
                            className="mt-1.5"
                            message={errors.apellidos_post}
                        />
                    </div>
                    <div>
                        <Label htmlFor="ci_post">C.I.</Label>
                        <Input {...validationProps('ci_post')}
                            id="ci_post"
                            className={fieldClass}
                            value={data.ci_post}
                            onChange={(event) => field('ci_post', event.target.value)}
                        />
                        <InputError className="mt-1.5" message={errors.ci_post} />
                    </div>
                    <div>
                        <Label htmlFor="fecha_nacimiento_post">
                            Fecha de nacimiento{!postulante || postulante.fecha_nacimiento_post ? ' *' : ''}
                        </Label>
                        <Input {...validationProps('fecha_nacimiento_post', { required: !postulante || !!postulante.fecha_nacimiento_post })}
                            id="fecha_nacimiento_post"
                            type="text"
                            placeholder="DD/MM/AAAA"
                            autoComplete="bday"
                            title="Ingrese la fecha en formato DD/MM/AAAA."
                            aria-describedby="fecha_nacimiento_ayuda fecha_nacimiento_error"
                            aria-invalid={!!errors.fecha_nacimiento_post}
                            className={fieldClass}
                            value={data.fecha_nacimiento_post}
                            onChange={(event) => field('fecha_nacimiento_post', event.target.value)}
                        />
                        <div id="fecha_nacimiento_ayuda" className="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                            <p>Formato: DD/MM/AAAA</p>
                            {postulante && !postulante.fecha_nacimiento_post && (
                                <p>Fecha de nacimiento pendiente de completar</p>
                            )}
                        </div>
                        <InputError id="fecha_nacimiento_error" className="mt-1.5" message={errors.fecha_nacimiento_post} />
                    </div>
                    <div>
                        <Label htmlFor="email_post">Correo de contacto académico</Label>
                        <Input {...validationProps('email_post')}
                            id="email_post"
                            type="email"
                            className={fieldClass}
                            value={data.email_post}
                            onChange={(event) =>
                                field('email_post', event.target.value)
                            }
                        />
                        <InputError className="mt-1.5" message={errors.email_post} />
                    </div>
                    <div>
                        <Label htmlFor="celular_post">Celular</Label>
                        <Input {...validationProps('celular_post')}
                            id="celular_post"
                            className={fieldClass}
                            value={data.celular_post}
                            onChange={(event) =>
                                field('celular_post', event.target.value)
                            }
                        />
                        <InputError
                            className="mt-1.5"
                            message={errors.celular_post}
                        />
                    </div>
                </CardContent>
            </Card>

            <Card className="gap-0 border-0 bg-white py-0 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-950/70 dark:ring-slate-800">
                <CardHeader className="border-b border-slate-100 p-5 dark:border-slate-800">
                    <CardTitle className="text-lg text-slate-900 dark:text-slate-100">
                        Información académica
                    </CardTitle>
                </CardHeader>
                <CardContent className="grid gap-5 p-5 sm:grid-cols-2">
                    <div>
                        <Label htmlFor="id_col">Colegio de procedencia</Label>
                        <select {...validationProps('id_col')}
                            id="id_col"
                            className={`${fieldClass} w-full rounded-lg border border-slate-200 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500`}
                            value={data.crear_otro_colegio ? '__otro__' : data.id_col}
                            onChange={(event) => event.target.value === '__otro__'
                                ? setData({ ...data, id_col: '', crear_otro_colegio: true })
                                : setData({ ...data, id_col: event.target.value, crear_otro_colegio: false, otro_colegio_nombre: '' })}
                        >
                            <option value="">Seleccione un colegio</option>
                            {opciones.colegios.map((colegio) => (
                                <option key={colegio.id_col} value={colegio.id_col}>
                                    {colegio.nombre_col}
                                </option>
                            ))}
                            <option value="__otro__">Otro colegio</option>
                        </select>
                        <InputError className="mt-1.5" message={errors.id_col} />
                        {data.crear_otro_colegio && <div className="mt-3">
                            <Label htmlFor="otro_colegio_nombre">Nombre del nuevo colegio *</Label>
                            <Input {...validationProps('otro_colegio_nombre', { required: true, minLength: 2, maxLength: 255 })} id="otro_colegio_nombre" className={fieldClass} value={data.otro_colegio_nombre} onChange={(event) => field('otro_colegio_nombre', event.target.value)} />
                            <InputError className="mt-1.5" message={errors.otro_colegio_nombre} />
                        </div>}
                    </div>
                    <div>
                        <Label htmlFor="id_uni">Universidad postulada</Label>
                        <select {...validationProps('id_uni')}
                            id="id_uni"
                            className={`${fieldClass} w-full rounded-lg border border-slate-200 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500`}
                            value={data.crear_otra_universidad ? '__otro__' : data.id_uni}
                            onChange={(event) =>
                                changeUniversidad(event.target.value)
                            }
                        >
                            <option value="">Seleccione una universidad</option>
                            {opciones.universidades.map((universidad) => (
                                <option
                                    key={universidad.id_uni}
                                    value={universidad.id_uni}
                                >
                                    {universidad.sigla_uni
                                        ? `${universidad.sigla_uni} - ${universidad.nombre_uni}`
                                        : universidad.nombre_uni}
                                </option>
                            ))}
                            <option value="__otro__">Otra universidad</option>
                        </select>
                        <InputError className="mt-1.5" message={errors.id_uni} />
                        {data.crear_otra_universidad && <div className="mt-3 grid gap-3">
                            <div><Label htmlFor="otra_universidad_nombre">Nombre de la nueva universidad *</Label><Input {...validationProps('otra_universidad_nombre', { required: true, minLength: 2, maxLength: 255 })} id="otra_universidad_nombre" className={fieldClass} value={data.otra_universidad_nombre} onChange={(event) => field('otra_universidad_nombre', event.target.value)} /><InputError className="mt-1.5" message={errors.otra_universidad_nombre} /></div>
                            <div><Label htmlFor="otra_universidad_sigla">Sigla</Label><Input {...validationProps('otra_universidad_sigla', { maxLength: 60 })} id="otra_universidad_sigla" className={fieldClass} value={data.otra_universidad_sigla} onChange={(event) => field('otra_universidad_sigla', event.target.value)} /><InputError className="mt-1.5" message={errors.otra_universidad_sigla} /></div>
                        </div>}
                    </div>
                    <div>
                        <Label htmlFor="id_car">Carrera postulada{universidadIndicada ? ' *' : ''}</Label>
                        <select {...validationProps('id_car', { required: universidadIndicada })}
                            id="id_car"
                            className={`${fieldClass} w-full rounded-lg border border-slate-200 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500`}
                            value={data.crear_otra_carrera ? '__otro__' : data.id_car}
                            disabled={!universidadIndicada}
                            onChange={(event) => event.target.value === '__otro__'
                                ? setData({ ...data, id_car: '', crear_otra_carrera: true })
                                : setData({ ...data, id_car: event.target.value, crear_otra_carrera: false, otra_carrera_nombre: '' })}
                        >
                            <option value="">
                                {universidadIndicada
                                    ? 'Seleccione una carrera'
                                    : 'Seleccione primero una universidad'}
                            </option>
                            {carrerasDisponibles.map((carrera) => (
                                <option key={carrera.id_car} value={carrera.id_car}>
                                    {carrera.nombre_car} ·{' '}
                                    {carrera.nivel_exigencia_matematica_car ||
                                        'Nivel no definido'}
                                </option>
                            ))}
                            {universidadIndicada && <option value="__otro__">Otra carrera</option>}
                        </select>
                        <InputError className="mt-1.5" message={errors.id_car} />
                        {data.crear_otra_carrera && <div className="mt-3">
                            <Label htmlFor="otra_carrera_nombre">Nombre de la nueva carrera *</Label>
                            <Input {...validationProps('otra_carrera_nombre', { required: true, minLength: 2, maxLength: 255 })} id="otra_carrera_nombre" className={fieldClass} value={data.otra_carrera_nombre} onChange={(event) => field('otra_carrera_nombre', event.target.value)} />
                            <InputError className="mt-1.5" message={errors.otra_carrera_nombre} />
                        </div>}
                    </div>
                    <div>
                        <Label htmlFor="turno_post">Turno</Label>
                        <select {...validationProps('turno_post')}
                            id="turno_post"
                            className={`${fieldClass} w-full rounded-lg border border-slate-200 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500`}
                            value={data.turno_post}
                            onChange={(event) =>
                                field('turno_post', event.target.value)
                            }
                        >
                            <option value="">Sin asignar</option>
                            {inputOptions.turno_post.map((turno) => <option key={turno} value={turno}>{turno}</option>)}
                        </select>
                        <InputError
                            className="mt-1.5"
                            message={errors.turno_post}
                        />
                    </div>
                    <div>
                        <Label htmlFor="gestion_post">Gestión *</Label>
                        <Input {...validationProps('gestion_post')}
                            id="gestion_post"
                            type="number"
                            min="2000"
                            max={gestionActual + 1}
                            className={fieldClass}
                            value={data.gestion_post}
                            onChange={(event) =>
                                field('gestion_post', event.target.value)
                            }
                        />
                        <InputError
                            className="mt-1.5"
                            message={errors.gestion_post}
                        />
                    </div>
                    <div>
                        <Label htmlFor="estado_post">Estado *</Label>
                        <select {...validationProps('estado_post')}
                            id="estado_post"
                            className={`${fieldClass} w-full rounded-lg border border-slate-200 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500`}
                            value={data.estado_post}
                            onChange={(event) =>
                                field('estado_post', event.target.value)
                            }
                        >
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <InputError
                            className="mt-1.5"
                            message={errors.estado_post}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <Label htmlFor="observaciones_post">Observaciones</Label>
                        <Textarea {...validationProps('observaciones_post')}
                            id="observaciones_post"
                            className="mt-1.5 min-h-28 border-slate-200 bg-white text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            value={data.observaciones_post}
                            onChange={(event) =>
                                field('observaciones_post', event.target.value)
                            }
                        />
                        <InputError
                            className="mt-1.5"
                            message={errors.observaciones_post}
                        />
                    </div>
                </CardContent>
            </Card>

            <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                {errors.catalogos && <InputError className="sm:mr-auto" message={errors.catalogos} />}
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
                    <Button variant="outline" asChild className="h-10">
                        <Link href={route('postulantes.index')}>Cancelar</Link>
                    </Button>
                )}
                <Button
                    type="submit"
                    disabled={processing}
                    className="h-10 bg-indigo-700 px-5 hover:bg-indigo-800"
                >
                    <Save className="h-4 w-4" />
                    {submitLabel}
                </Button>
            </div>
        </form>
    );
}
