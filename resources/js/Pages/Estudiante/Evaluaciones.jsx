import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Progress } from '@/Components/ui/progress';
import StudentTrackingNav from '@/Components/Estudiante/StudentTrackingNav';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowRight,
    Award,
    BookOpenCheck,
    CheckCircle2,
    Clock,
    GraduationCap,
    History,
    RefreshCw,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

const formatTime = (seconds) => {
    const safeSeconds = Math.max(0, seconds);
    const minutes = Math.floor(safeSeconds / 60);
    const remaining = safeSeconds % 60;
    return `${String(minutes).padStart(2, '0')}:${String(remaining).padStart(2, '0')}`;
};

const formatDate = (value) =>
    value
        ? new Date(value).toLocaleString('es-BO', {
              dateStyle: 'medium',
              timeStyle: 'short',
          })
        : 'Sin fecha';

export default function Evaluaciones({
    postulanteVinculado = false,
    habilitacionAcademica = null,
    estructuraResultadosDisponible = false,
    plantillas = [],
    evaluacionActiva = null,
    resultado = null,
    historial = [],
}) {
    const { errors, flash } = usePage().props;
    const [selectedTemplate, setSelectedTemplate] = useState(
        evaluacionActiva?.plantilla?.id_plan || plantillas[0]?.id_plan || '',
    );
    const [answers, setAnswers] = useState({});
    const [processing, setProcessing] = useState(false);
    const durationSeconds =
        Number(evaluacionActiva?.plantilla?.duracion_minutos_plan || 60) * 60;
    const [secondsLeft, setSecondsLeft] = useState(
        evaluacionActiva?.segundos_restantes ?? durationSeconds,
    );
    const accessRestricted =
        habilitacionAcademica?.habilitado_evaluaciones_hab === false;
    const selected = useMemo(
        () =>
            plantillas.find(
                (plantilla) =>
                    String(plantilla.id_plan) === String(selectedTemplate),
            ) || plantillas[0],
        [plantillas, selectedTemplate],
    );
    const answeredCount = Object.keys(answers).length;
    const totalQuestions = evaluacionActiva?.preguntas?.length || 0;

    useEffect(() => {
        if (!evaluacionActiva) return;

        setAnswers({});
        setSelectedTemplate(evaluacionActiva.plantilla.id_plan);
        setSecondsLeft(
            evaluacionActiva.segundos_restantes ?? durationSeconds,
        );
    }, [evaluacionActiva?.id_eval_apl]);

    useEffect(() => {
        if (!evaluacionActiva || resultado || secondsLeft <= 0) {
            return undefined;
        }

        const timer = window.setInterval(
            () => setSecondsLeft((current) => Math.max(0, current - 1)),
            1000,
        );

        return () => window.clearInterval(timer);
    }, [evaluacionActiva, resultado, secondsLeft]);

    const start = () => {
        if (
            !selected ||
            accessRestricted ||
            !postulanteVinculado ||
            !estructuraResultadosDisponible
        ) {
            return;
        }

        router.post(
            route('estudiante.evaluaciones.iniciar', selected.id_plan),
            { tipo_eval_apl: selected.dificultad_plan },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
            },
        );
    };

    const submit = () => {
        if (!evaluacionActiva || processing) {
            return;
        }

        const responses = evaluacionActiva.preguntas
            .filter((question) => answers[question.id_preg])
            .map((question) => ({
                id_preg: question.id_preg,
                id_alt: answers[question.id_preg],
            }));

        router.post(
            route(
                'estudiante.evaluaciones.enviar',
                evaluacionActiva.id_eval_apl,
            ),
            {
                respuestas: responses,
                tiempo_total_segundos: Math.max(
                    0,
                    durationSeconds - secondsLeft,
                ),
            },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="flex items-center gap-2 text-xl font-bold text-text-main">
                    <GraduationCap className="h-6 w-6 text-brand-secondary" />
                    Centro de Evaluaciones Académicas
                </h2>
            }
        >
            <Head title="Evaluaciones del Postulante" />

            <main className="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                <StudentTrackingNav />

                {(errors?.postulante ||
                    errors?.habilitacion ||
                    errors?.plantilla ||
                    errors?.evaluacion ||
                    errors?.respuestas) && (
                    <div className="rounded-2xl border border-brand-danger/20 bg-brand-danger/10 p-4 text-sm text-brand-danger">
                        {errors.postulante ||
                            errors.habilitacion ||
                            errors.plantilla ||
                            errors.evaluacion ||
                            errors.respuestas}
                    </div>
                )}

                {flash?.success && (
                    <div className="rounded-2xl border border-brand-success/20 bg-brand-success/10 p-4 text-sm font-semibold text-brand-success">
                        {flash.success}
                    </div>
                )}

                {accessRestricted && (
                    <div className="flex items-start gap-3 rounded-2xl border border-brand-danger/20 bg-brand-danger/10 p-5 text-brand-danger">
                        <AlertTriangle className="mt-0.5 h-5 w-5 shrink-0" />
                        <div>
                            <p className="text-sm font-bold">
                                Habilitación académica temporalmente restringida
                            </p>
                            <p className="mt-1 text-xs leading-5">
                                Tu acceso a evaluaciones se encuentra temporalmente
                                restringido. Consulta con administración académica
                                para regularizar tu habilitación.
                            </p>
                        </div>
                    </div>
                )}

                {!postulanteVinculado && (
                    <div className="rounded-2xl border border-brand-warning/25 bg-brand-warning/10 p-5">
                        <p className="font-bold text-text-main">
                            Cuenta pendiente de vinculación académica
                        </p>
                        <p className="mt-1 text-sm text-text-muted">
                            No se encontró un postulante con el correo de esta
                            cuenta. Administración debe verificar la vinculación
                            antes de iniciar una evaluación.
                        </p>
                    </div>
                )}

                {!estructuraResultadosDisponible && (
                    <div className="rounded-2xl border border-brand-info/25 bg-brand-info/10 p-5">
                        <p className="font-bold text-text-main">
                            Estructura de resultados pendiente de habilitación
                        </p>
                        <p className="mt-1 text-sm text-text-muted">
                            Las migraciones de evaluaciones aplicadas deben
                            ejecutarse antes de registrar respuestas.
                        </p>
                    </div>
                )}

                {!evaluacionActiva && !resultado && (
                    <>
                        <section className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-7 text-white shadow-xl sm:p-9">
                            <div className="relative z-10 max-w-3xl">
                                <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-brand-accent">
                                    <BookOpenCheck className="h-4 w-4" />
                                    Evaluaciones institucionales
                                </span>
                                <h1 className="mt-4 text-3xl font-black">
                                    Evaluaciones con resultados trazables
                                </h1>
                                <p className="mt-3 text-sm leading-6 text-slate-200">
                                    Cada respuesta será registrada y calificada con
                                    la ponderación definida en la plantilla
                                    académica.
                                </p>
                            </div>
                        </section>

                        <section>
                            <h2 className="text-lg font-black text-text-main">
                                Instrumentos disponibles
                            </h2>
                            <p className="mt-1 text-sm text-text-muted">
                                Selecciona una plantilla para iniciar una aplicación
                                académica.
                            </p>
                            <div className="mt-4 grid gap-4 md:grid-cols-2">
                                {plantillas.map((plantilla) => {
                                    const isSelected =
                                        String(selected?.id_plan) ===
                                        String(plantilla.id_plan);
                                    return (
                                        <button
                                            key={plantilla.id_plan}
                                            type="button"
                                            onClick={() =>
                                                setSelectedTemplate(
                                                    plantilla.id_plan,
                                                )
                                            }
                                            className={`rounded-2xl border p-5 text-left transition ${
                                                isSelected
                                                    ? 'border-brand-secondary bg-brand-secondary/10 shadow-md'
                                                    : 'border-brand-border bg-brand-card hover:border-brand-secondary/40'
                                            }`}
                                        >
                                            <div className="flex items-start justify-between gap-4">
                                                <div>
                                                    <h3 className="font-black leading-snug text-text-main">
                                                        {plantilla.nombre_plan}
                                                    </h3>
                                                    <p className="mt-2 text-xs leading-5 text-text-muted">
                                                        {plantilla.descripcion_plan ||
                                                            'Instrumento académico institucional.'}
                                                    </p>
                                                </div>
                                                <span className="rounded-xl bg-brand-primary/10 px-3 py-2 text-xs font-black text-brand-primary dark:text-slate-100">
                                                    {
                                                        plantilla.preguntas_count
                                                    }{' '}
                                                    preguntas
                                                </span>
                                            </div>
                                            <div className="mt-4 flex flex-wrap gap-2">
                                                {plantilla.materias.map(
                                                    (materia) => (
                                                        <span
                                                            key={materia}
                                                            className="rounded-full border border-brand-border bg-brand-bg px-2.5 py-1 text-[10px] font-bold text-text-muted"
                                                        >
                                                            {materia}
                                                        </span>
                                                    ),
                                                )}
                                            </div>
                                            <p className="mt-4 text-xs font-semibold text-text-muted">
                                                {plantilla.duracion_minutos_plan ||
                                                    60}{' '}
                                                minutos ·{' '}
                                                {Number(
                                                    plantilla.puntaje_maximo,
                                                ).toFixed(2)}{' '}
                                                puntos
                                            </p>
                                        </button>
                                    );
                                })}
                            </div>
                        </section>

                        <Card className="border-brand-border bg-brand-card">
                            <CardContent className="flex flex-col items-center p-6 text-center sm:p-8">
                                <h3 className="text-lg font-black text-text-main">
                                    {selected?.nombre_plan ||
                                        'Sin plantilla disponible'}
                                </h3>
                                <p className="mt-2 max-w-xl text-sm leading-6 text-text-muted">
                                    Al iniciar se abrirá un registro de evaluación
                                    aplicado asociado a tu ficha académica.
                                </p>
                                <button
                                    type="button"
                                    onClick={start}
                                    disabled={
                                        processing ||
                                        accessRestricted ||
                                        !postulanteVinculado ||
                                        !estructuraResultadosDisponible ||
                                        !selected
                                    }
                                    className="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-secondary px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-secondary/90 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Iniciar evaluación
                                    <ArrowRight className="h-4 w-4" />
                                </button>
                            </CardContent>
                        </Card>
                    </>
                )}

                {evaluacionActiva && !resultado && (
                    <>
                        <div className="sticky top-4 z-20 rounded-2xl border border-brand-border bg-brand-card/95 p-4 shadow-lg backdrop-blur sm:p-5">
                            <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                                <div>
                                    <p className="text-sm font-black text-text-main">
                                        {evaluacionActiva.plantilla.nombre_plan}
                                    </p>
                                    <p className="mt-1 text-xs text-text-muted">
                                        {answeredCount} de {totalQuestions}{' '}
                                        respuestas registradas
                                    </p>
                                </div>
                                <span className="inline-flex items-center gap-2 rounded-xl bg-brand-warning/10 px-4 py-2 font-black text-brand-warning">
                                    <Clock className="h-4 w-4" />
                                    {formatTime(secondsLeft)}
                                </span>
                            </div>
                            <Progress
                                value={
                                    totalQuestions
                                        ? (answeredCount / totalQuestions) * 100
                                        : 0
                                }
                                className="mt-4 h-2"
                            />
                        </div>

                        <div className="space-y-5">
                            {evaluacionActiva.preguntas.map((pregunta, index) => (
                                <Card
                                    key={pregunta.id_preg}
                                    className="overflow-hidden border-brand-border bg-brand-card"
                                >
                                    <CardHeader>
                                        <div className="flex flex-wrap justify-between gap-2 text-xs">
                                            <span className="font-bold uppercase tracking-wider text-brand-secondary">
                                                {pregunta.materia} ·{' '}
                                                {pregunta.area}
                                            </span>
                                            <span className="text-text-muted">
                                                Pregunta {index + 1} de{' '}
                                                {totalQuestions} ·{' '}
                                                {Number(
                                                    pregunta.puntaje,
                                                ).toFixed(2)}{' '}
                                                pts
                                            </span>
                                        </div>
                                        <CardTitle className="mt-3 whitespace-pre-line text-base leading-7 text-text-main">
                                            {pregunta.enunciado_preg}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="grid gap-3">
                                        {pregunta.alternativas.map(
                                            (alternativa) => {
                                                const checked =
                                                    answers[
                                                        pregunta.id_preg
                                                    ] === alternativa.id_alt;
                                                return (
                                                    <button
                                                        key={
                                                            alternativa.id_alt
                                                        }
                                                        type="button"
                                                        onClick={() =>
                                                            setAnswers(
                                                                (current) => ({
                                                                    ...current,
                                                                    [pregunta.id_preg]:
                                                                        alternativa.id_alt,
                                                                }),
                                                            )
                                                        }
                                                        className={`flex items-start gap-3 rounded-xl border p-4 text-left text-sm transition ${
                                                            checked
                                                                ? 'border-brand-secondary bg-brand-secondary/10 text-brand-secondary'
                                                                : 'border-brand-border bg-brand-bg text-text-main hover:border-brand-secondary/40'
                                                        }`}
                                                    >
                                                        <span
                                                            className={`flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-black ${
                                                                checked
                                                                    ? 'bg-brand-secondary text-white'
                                                                    : 'bg-brand-card text-text-muted'
                                                            }`}
                                                        >
                                                            {
                                                                alternativa.letra_alt
                                                            }
                                                        </span>
                                                        <span className="leading-6">
                                                            {
                                                                alternativa.texto_alt
                                                            }
                                                        </span>
                                                    </button>
                                                );
                                            },
                                        )}
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        <div className="flex justify-end">
                            <button
                                type="button"
                                onClick={submit}
                                disabled={
                                    processing ||
                                    answeredCount < totalQuestions
                                }
                                className="inline-flex items-center gap-2 rounded-xl bg-brand-secondary px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-secondary/90 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Finalizar y calificar
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
                    </>
                )}

                {resultado && (
                    <>
                        <Card className="overflow-hidden border-brand-border bg-brand-card">
                            <div className="bg-gradient-to-br from-brand-primary to-brand-secondary p-7 text-center text-white sm:p-9">
                                <span className="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-brand-accent">
                                    <Award className="h-4 w-4" />
                                    Evaluación finalizada
                                </span>
                                <h1 className="mt-4 text-2xl font-black">
                                    {resultado.plantilla?.nombre_plan}
                                </h1>
                                <p className="mt-5 text-5xl font-black">
                                    {Number(resultado.porcentaje).toFixed(1)}%
                                </p>
                                <p className="mt-2 text-sm text-slate-200">
                                    {Number(resultado.puntaje_total).toFixed(2)} de{' '}
                                    {Number(resultado.puntaje_maximo).toFixed(2)}{' '}
                                    puntos · {resultado.correctas} de{' '}
                                    {resultado.total_preguntas} respuestas
                                    correctas
                                </p>
                            </div>
                        </Card>

                        <section className="space-y-3">
                            <h2 className="text-lg font-black text-text-main">
                                Respuestas registradas
                            </h2>
                            {resultado.respuestas.map((respuesta) => (
                                <article
                                    key={respuesta.id_resp_eval}
                                    className="rounded-2xl border border-brand-border bg-brand-card p-5"
                                >
                                    <div className="flex items-start gap-3">
                                        <span
                                            className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${
                                                respuesta.es_correcta
                                                    ? 'bg-brand-success/10 text-brand-success'
                                                    : 'bg-brand-danger/10 text-brand-danger'
                                            }`}
                                        >
                                            {respuesta.es_correcta ? (
                                                <CheckCircle2 className="h-5 w-5" />
                                            ) : (
                                                <AlertTriangle className="h-5 w-5" />
                                            )}
                                        </span>
                                        <div className="min-w-0">
                                            <p className="text-xs font-bold uppercase tracking-wider text-brand-secondary">
                                                {respuesta.materia} ·{' '}
                                                {respuesta.area}
                                            </p>
                                            <p className="mt-2 text-sm font-semibold leading-6 text-text-main">
                                                {respuesta.enunciado}
                                            </p>
                                            <p className="mt-2 text-xs text-text-muted">
                                                Respuesta:{' '}
                                                {respuesta.alternativa
                                                    ? `${respuesta.alternativa.letra_alt}. ${respuesta.alternativa.texto_alt}`
                                                    : 'Sin respuesta'}{' '}
                                                ·{' '}
                                                {Number(
                                                    respuesta.puntaje_obtenido,
                                                ).toFixed(2)}{' '}
                                                /{' '}
                                                {Number(
                                                    respuesta.puntaje_maximo,
                                                ).toFixed(2)}{' '}
                                                pts
                                            </p>
                                            {respuesta.explicacion && (
                                                <p className="mt-3 rounded-xl bg-brand-bg p-3 text-xs leading-5 text-text-muted">
                                                    {respuesta.explicacion}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </section>

                        <button
                            type="button"
                            onClick={() =>
                                router.get(
                                    route('estudiante.evaluaciones'),
                                )
                            }
                            className="inline-flex items-center gap-2 rounded-xl border border-brand-border px-5 py-3 text-sm font-bold text-text-main hover:bg-brand-border/30"
                        >
                            <RefreshCw className="h-4 w-4" />
                            Volver a evaluaciones
                        </button>
                    </>
                )}

                {!evaluacionActiva && historial.length > 0 && (
                    <section className="rounded-2xl border border-brand-border bg-brand-card p-5 sm:p-6">
                        <h2 className="flex items-center gap-2 text-lg font-black text-text-main">
                            <History className="h-5 w-5 text-brand-secondary" />
                            Historial reciente
                        </h2>
                        <div className="mt-4 grid gap-3 md:grid-cols-2">
                            {historial.map((item) => (
                                <button
                                    key={item.id_eval_apl}
                                    type="button"
                                    onClick={() =>
                                        router.get(
                                            route('estudiante.evaluaciones'),
                                            {
                                                resultado:
                                                    item.id_eval_apl,
                                            },
                                        )
                                    }
                                    className="rounded-xl border border-brand-border bg-brand-bg p-4 text-left hover:border-brand-secondary/40"
                                >
                                    <p className="font-bold leading-snug text-text-main">
                                        {item.plantilla?.nombre_plan}
                                    </p>
                                    <p className="mt-2 text-xs text-text-muted">
                                        {formatDate(
                                            item.fecha_fin_eval_apl,
                                        )}{' '}
                                        ·{' '}
                                        {Number(
                                            item.porcentaje_eval_apl,
                                        ).toFixed(1)}
                                        %
                                    </p>
                                </button>
                            ))}
                        </div>
                    </section>
                )}
            </main>
        </AuthenticatedLayout>
    );
}
