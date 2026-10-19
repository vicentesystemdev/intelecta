import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import StudentTrackingNav from '@/Components/Estudiante/StudentTrackingNav';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { crearRelojCat } from '@/Utils/relojCat.mjs';

export default function CatDemo({ disponible, acceso, instrumentos = [], historial = [], intento }) {
    const { errors } = usePage().props;
    const [seleccion, setSeleccion] = useState(null);
    const [ocupado, setOcupado] = useState(false);
    const [segundos, setSegundos] = useState(intento?.segundos_restantes ?? 0);
    useEffect(() => {
        setSeleccion(null);
        setSegundos(intento?.segundos_restantes ?? 0);
    }, [intento?.id_intento_cat, intento?.revision, intento?.segundos_restantes]);
    useEffect(() => {
        if (intento?.estado !== 'en_progreso') return undefined;
        const restante = crearRelojCat(intento.vence_en, intento.servidor_ahora);
        setSegundos(restante());
        const timer = setInterval(() => setSegundos(restante()), 250);
        return () => clearInterval(timer);
    }, [intento?.id_intento_cat, intento?.revision, intento?.vence_en, intento?.servidor_ahora, intento?.estado]);
    const enviar = (url, data = {}) => router.post(url, data, {
        preserveScroll: true, onStart: () => setOcupado(true), onFinish: () => setOcupado(false),
    });
    const boton = 'rounded-xl bg-brand-secondary px-5 py-3 font-bold text-white disabled:opacity-50';
    const card = 'rounded-2xl border border-brand-border bg-brand-card p-6';

    return <AuthenticatedLayout header={<h2 className="text-xl font-bold">Matemática CAT DEMO</h2>}>
        <Head title="Matemática CAT DEMO" />
        <main className="mx-auto max-w-4xl space-y-5 px-4 py-8 text-text-main">
            <StudentTrackingNav />
            <p className="rounded-xl bg-brand-warning/10 p-4 text-sm">Evaluación experimental con parámetros simulados. Sus resultados no afectan tus notas ni promedios académicos.</p>
            {!disponible && <p className={card}>El instrumento todavía no está habilitado en este entorno.</p>}
            {!acceso?.permitido && <p role="alert" className={card}>{acceso?.motivo}</p>}
            {Object.values(errors || {}).map((error, i) => <p key={i} role="alert" className="text-brand-danger">{error}</p>)}
            {!intento && instrumentos.map(instrumento => <section key={instrumento.id_instrumento_cat} className={card}>
                <h1 className="text-xl font-bold">{instrumento.nombre}</h1>
                <p className="my-4">Entre 8 y 10 preguntas · {instrumento.duracion_minutos} minutos. Cada respuesta confirmada se conserva y no puede cambiarse.</p>
                <button className={boton} disabled={ocupado || !acceso?.permitido} onClick={() => enviar(route('estudiante.cat.iniciar', instrumento.id_instrumento_cat))}>Iniciar o continuar</button>
            </section>)}
            {intento && <section className={card}>
                <h1 className="text-xl font-bold">{intento.nombre}</h1>
                <p className="my-3">Respuestas guardadas: {intento.revision} / {intento.maximum} · Estado: {intento.estado}</p>
                {intento.estado === 'en_progreso' && <p className="mb-4 font-bold" aria-live="off">Tiempo restante: {Math.floor(segundos / 60)}:{String(segundos % 60).padStart(2, '0')}</p>}
                {intento.estado === 'en_progreso' && segundos === 0 && <p role="alert">El plazo terminó. Recarga para consultar el estado del intento.</p>}
                {intento.estado === 'en_progreso' && intento.calculo_pendiente && <div role="status">
                    <p className="mb-4">El cálculo del siguiente paso está pendiente. Tu progreso está guardado; podrás continuar cuando el motor esté disponible.</p>
                    <button className={boton} disabled={ocupado || !acceso?.permitido || segundos === 0} onClick={() => enviar(route('estudiante.cat.recuperar', intento.id_intento_cat))}>Reintentar cálculo</button>
                </div>}
                {intento.pregunta && <div>
                    <p className="text-sm text-text-muted">{intento.pregunta.tema}</p>
                    <h2 className="my-4 whitespace-pre-line text-lg font-semibold">{intento.pregunta.enunciado}</h2>
                    <fieldset disabled={ocupado || segundos === 0 || !acceso?.permitido} className="space-y-3">
                        <legend className="sr-only">Selecciona una alternativa</legend>
                        {intento.pregunta.alternativas.map(a => <label key={a.id_alt} className="flex cursor-pointer gap-3 rounded-xl border border-brand-border p-4">
                            <input type="radio" name="alternativa-cat" value={a.id_alt} checked={seleccion === a.id_alt} onChange={() => setSeleccion(a.id_alt)} />
                            <span>{a.letra}. {a.texto}</span>
                        </label>)}
                    </fieldset>
                    <button className={`${boton} mt-5`} disabled={ocupado || seleccion === null || segundos === 0 || !acceso?.permitido} onClick={() => enviar(route('estudiante.cat.responder', intento.id_intento_cat), {
                        id_preg: intento.pregunta.id_preg, id_alt: seleccion, revision: intento.revision,
                    })}>Confirmar respuesta</button>
                </div>}
                {intento.estado === 'vencida' && <p>El intento venció. Se conservaron las {intento.revision} respuestas aceptadas antes del plazo. No se alcanzó una finalización CAT acreditada.</p>}
                {intento.resultado_cat && <div className="space-y-2">
                    <h2 className="text-lg font-bold">Estimación CAT experimental</h2>
                    <p>Theta: {Number(intento.resultado_cat.theta).toFixed(3)} · Incertidumbre: {Number(intento.resultado_cat.sd).toFixed(3)}</p>
                    <p>Intervalo: {intento.resultado_cat.intervalo.map(n => Number(n).toFixed(3)).join(' a ')}</p>
                    <p>Finalización: {intento.resultado_cat.motivo}</p>
                    <p>Aciertos observados: {intento.aciertos} de {intento.revision}. No equivalen a una nota convencional.</p>
                </div>}
                <Link href={route('estudiante.cat.index')} className="mt-6 inline-block underline">Volver a mis intentos CAT</Link>
            </section>}
            {!intento && historial.length > 0 && <section className={card}>
                <h2 className="mb-3 text-lg font-bold">Mis intentos CAT</h2>
                {historial.map(i => <Link key={i.id_intento_cat} href={route('estudiante.cat.show', i.id_intento_cat)} className="block border-b border-brand-border py-3">Intento {i.id_intento_cat} · {i.estado} · {i.revision} respuestas guardadas</Link>)}
            </section>}
        </main>
    </AuthenticatedLayout>;
}
