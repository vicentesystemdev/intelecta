import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Progress } from '@/Components/ui/progress';
import { 
    Clock, 
    BookOpen, 
    CheckCircle2, 
    AlertTriangle, 
    ArrowRight, 
    Award,
    RefreshCw,
    GraduationCap,
    HelpCircle,
    ChevronRight,
    TrendingUp,
    Atom,
    FlaskConical,
    Calculator,
    BrainCircuit,
    Layers3
} from 'lucide-react';

const examQuestions = [
    {
        id: 1,
        area: 'Aritmética',
        question: 'Un estudiante del instituto preuniversitario resolvió el 60% de los reactivos de aritmética el lunes, y el martes resolvió el 30% del resto. Si aún le quedan 28 preguntas por resolver, ¿cuál era la cantidad original de preguntas de aritmética?',
        options: [
            { key: 'A', text: '80 preguntas' },
            { key: 'B', text: '100 preguntas' },
            { key: 'C', text: '120 preguntas' },
            { key: 'D', text: '90 preguntas' }
        ],
        correct: 'B',
        feedbackSuccess: '¡Correcto! El lunes resuelve el 60% (queda 40%). El martes el 30% del 40%, es decir, 12%. El total resuelto es 72%, dejando un 28% equivalente a las 28 preguntas restantes. Total original = 100.',
        feedbackFail: 'Reforzar porcentajes y proporciones. Recuerda calcular la fracción del resto restante.'
    },
    {
        id: 2,
        area: 'Álgebra',
        question: 'Resuelva el siguiente sistema de ecuaciones lineales: \n3x - y = 7 \nx + 2y = 7 \nCalcule el valor del producto xy.',
        options: [
            { key: 'A', text: '4' },
            { key: 'B', text: '5' },
            { key: 'C', text: '6' },
            { key: 'D', text: '8' }
        ],
        correct: 'C',
        feedbackSuccess: '¡Correcto! Multiplicando la primera ecuación por 2: 6x - 2y = 14. Sumando a la segunda: 7x = 21 => x = 3. Sustituyendo, y = 2. Por tanto, xy = 3 * 2 = 6.',
        feedbackFail: 'Practicar despeje de variables y métodos de resolución de sistemas lineales (reducción, sustitución).'
    },
    {
        id: 3,
        area: 'Razonamiento Lógico',
        question: 'Encuentre el término que continúa de manera lógica en la siguiente sucesión numérica: \n4,  9,  19,  39,  79,  ...',
        options: [
            { key: 'A', text: '149' },
            { key: 'B', text: '159' },
            { key: 'C', text: '169' },
            { key: 'D', text: '119' }
        ],
        correct: 'B',
        feedbackSuccess: '¡Correcto! La regla de formación es multiplicar el término por 2 y sumarle 1: (4*2)+1 = 9, (9*2)+1 = 19, (19*2)+1 = 39, (39*2)+1 = 79. El siguiente término es (79*2)+1 = 159.',
        feedbackFail: 'Trabajar sucesiones y patrones numéricos. Analiza las diferencias sucesivas entre términos.'
    }
];

const evaluationTypes = [
    {
        id: 'diagnostico',
        name: 'Diagnóstico General',
        description: 'Lectura inicial de Matemática, ciencias exactas y razonamiento académico.',
        icon: BrainCircuit,
    },
    {
        id: 'matematica',
        name: 'Matemática',
        description: 'Aritmética, álgebra, geometría, estadística y resolución cuantitativa.',
        icon: Calculator,
    },
    {
        id: 'fisica',
        name: 'Física',
        description: 'Magnitudes, movimiento, fuerzas, energía y electricidad básica.',
        icon: Atom,
    },
    {
        id: 'quimica',
        name: 'Química',
        description: 'Materia, estructura atómica, reacciones y estequiometría.',
        icon: FlaskConical,
    },
    {
        id: 'mixta',
        name: 'Mixta Ingeniería',
        description: 'Cobertura integrada de Matemática, Física y Química.',
        icon: Layers3,
    },
];

export default function StudentEvaluaciones() {
    // Etapas: 1 (Inicio), 2 (Resolución), 3 (Resultado preliminar)
    const [step, setStep] = useState(1);
    const [answers, setAnswers] = useState({});
    const [secondsLeft, setSecondsLeft] = useState(900); // 15 minutos
    const [isActive, setIsActive] = useState(false);
    const [selectedEvaluation, setSelectedEvaluation] = useState(evaluationTypes[0]);
    
    // Temporizador
    useEffect(() => {
        let interval = null;
        if (isActive && secondsLeft > 0) {
            interval = setInterval(() => {
                setSecondsLeft((prev) => prev - 1);
            }, 1000);
        } else if (secondsLeft === 0) {
            handleSubmit();
        }
        return () => clearInterval(interval);
    }, [isActive, secondsLeft]);

    const formatTime = (secs) => {
        const minutes = Math.floor(secs / 60);
        const remainingSeconds = secs % 60;
        return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
    };

    const scrollToTop = () => {
        window.requestAnimationFrame(() => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    };

    const handleStart = () => {
        setStep(2);
        setAnswers({});
        setSecondsLeft(900);
        setIsActive(true);
        scrollToTop();
    };

    const handleSelectOption = (questionId, optionKey) => {
        setAnswers(prev => ({
            ...prev,
            [questionId]: optionKey
        }));
    };

    const handleSubmit = () => {
        setIsActive(false);
        setStep(3);
        scrollToTop();
    };

    const handleReset = () => {
        setStep(1);
        setAnswers({});
        setSecondsLeft(900);
        scrollToTop();
    };

    // Calcular estadísticas de resultados
    const totalQuestions = examQuestions.length;
    const answeredCount = Object.keys(answers).length;
    let correctCount = 0;
    
    examQuestions.forEach(q => {
        if (answers[q.id] === q.correct) {
            correctCount++;
        }
    });

    const score = Math.round((correctCount / totalQuestions) * 100);

    return (
        <AuthenticatedLayout
            header={
                <h2 className="flex items-center gap-2 text-xl font-bold leading-tight text-slate-800 dark:text-slate-100">
                    <GraduationCap className="h-6 w-6 text-indigo-600" />
                    Centro de Evaluaciones Académicas
                </h2>
            }
        >
            <Head title="Evaluaciones de Postulante" />

            <div className="py-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                {/* ETAPA 1: INICIO */}
                {step === 1 && (
                    <div className="space-y-6">
                        <section className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-800 to-indigo-950 p-8 text-white shadow-xl shadow-indigo-900/10">
                            <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:20px_20px]" />
                            <div className="relative z-10 space-y-4">
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-cyan-500/20 px-3 py-1 text-xs font-semibold text-cyan-300 ring-1 ring-cyan-500/20">
                                    <Clock className="h-3.5 w-3.5" />
                                    Evaluación Activa
                                </span>
                                <h1 className="text-2xl sm:text-3xl font-black">
                                    {selectedEvaluation.name}
                                </h1>
                                <p className="text-sm text-indigo-100 max-w-2xl leading-relaxed">
                                    {selectedEvaluation.description} Selecciona el instrumento académico y realiza el recorrido guiado para obtener recomendaciones de nivelación.
                                </p>
                            </div>
                        </section>

                        <section>
                            <div className="mb-3">
                                <h2 className="text-sm font-bold text-slate-900 dark:text-slate-100">Evaluaciones disponibles</h2>
                                <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">Elige el enfoque académico que deseas revisar.</p>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                                {evaluationTypes.map((evaluation) => {
                                    const Icon = evaluation.icon;
                                    const selected = selectedEvaluation.id === evaluation.id;

                                    return (
                                        <button
                                            key={evaluation.id}
                                            type="button"
                                            onClick={() => setSelectedEvaluation(evaluation)}
                                            className={`rounded-2xl border p-4 text-left transition ${
                                                selected
                                                    ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500/15 dark:bg-indigo-950/40'
                                                    : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800'
                                            }`}
                                        >
                                            <span className={`mb-3 flex h-10 w-10 items-center justify-center rounded-xl ${
                                                selected
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                            }`}>
                                                <Icon className="h-5 w-5" />
                                            </span>
                                            <span className="block text-sm font-bold text-slate-900 dark:text-slate-100">{evaluation.name}</span>
                                            <span className="mt-1 block text-[11px] leading-4 text-slate-500 dark:text-slate-400">{evaluation.description}</span>
                                        </button>
                                    );
                                })}
                            </div>
                        </section>

                        <div className="grid gap-6 md:grid-cols-3">
                            <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Duración Sugerida
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-2xl font-black text-slate-900">15 Minutos</p>
                                    <p className="text-xs text-slate-500 mt-1">Con control de tiempo activo</p>
                                </CardContent>
                            </Card>

                            <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Estructura
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-2xl font-black text-slate-900">3 Reactivos</p>
                                    <p className="text-xs text-slate-500 mt-1">Nivel académico preuniversitario</p>
                                </CardContent>
                            </Card>

                            <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Áreas de Medición
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-base font-black text-slate-800">{selectedEvaluation.name}</p>
                                    <p className="text-xs text-slate-500 mt-1">Recorrido académico preliminar</p>
                                </CardContent>
                            </Card>
                        </div>

                        <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 p-8 text-center space-y-4">
                            <div className="max-w-md mx-auto space-y-2">
                                <h3 className="text-lg font-bold text-slate-900">¿Preparado para iniciar?</h3>
                                <p className="text-xs text-slate-500">
                                    Una vez que presiones el botón de inicio, se activará el conteo de tiempo y se mostrarán las preguntas. Asegúrate de estar en un ambiente tranquilo.
                                </p>
                            </div>
                            <button
                                onClick={handleStart}
                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-8 py-3.5 shadow-lg shadow-indigo-600/10 transition"
                            >
                                Iniciar evaluación guiada
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </Card>
                    </div>
                )}

                {/* ETAPA 2: RESOLUCIÓN */}
                {step === 2 && (
                    <div className="space-y-6">
                        {/* Cabecera del examen */}
                        <div className="sticky top-20 z-40 flex flex-col items-center justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 sm:flex-row sm:p-6">
                            <div className="flex items-center gap-3">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 font-bold">
                                    E2
                                </span>
                                <div>
                                    <h2 className="text-sm font-bold text-slate-900">{selectedEvaluation.name}</h2>
                                    <p className="text-xs text-slate-400">Responde de manera guiada cada reactivo.</p>
                                </div>
                            </div>

                            <div className="flex items-center gap-6">
                                <div className="text-right">
                                    <p className="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Progreso</p>
                                    <p className="text-xs font-bold text-slate-700">{answeredCount} de {totalQuestions} respondidos</p>
                                </div>
                                <div className="flex items-center gap-2 bg-rose-50 px-3.5 py-2 rounded-xl text-rose-600 font-black tracking-wider text-sm ring-1 ring-rose-600/10">
                                    <Clock className="h-4 w-4 animate-pulse" />
                                    {formatTime(secondsLeft)}
                                </div>
                            </div>
                        </div>

                        {/* Barra de progreso visual */}
                        <Progress 
                            value={(answeredCount / totalQuestions) * 100} 
                            className="h-2 bg-indigo-50 [&_[data-slot=progress-indicator]]:bg-indigo-600"
                        />

                        {/* Cuestionario de preguntas */}
                        <div className="space-y-6">
                            {examQuestions.map((q, idx) => (
                                <Card key={q.id} className="border-0 bg-white shadow-sm ring-1 ring-slate-200/85 overflow-hidden">
                                    <div className="border-l-4 border-indigo-600 h-full">
                                        <CardHeader className="pb-3">
                                            <div className="flex justify-between items-center text-xs">
                                                <span className="font-semibold text-indigo-600 uppercase tracking-widest">{q.area}</span>
                                                <span className="text-slate-400 font-semibold">Pregunta {idx + 1} de {totalQuestions}</span>
                                            </div>
                                            <CardTitle className="text-slate-900 text-base font-bold leading-relaxed whitespace-pre-line mt-2">
                                                {q.question}
                                            </CardTitle>
                                        </CardHeader>
                                        <CardContent className="space-y-3 pt-2">
                                            <div className="grid gap-2">
                                                {q.options.map((option) => {
                                                    const isSelected = answers[q.id] === option.key;
                                                    return (
                                                        <button
                                                            key={option.key}
                                                            type="button"
                                                            onClick={() => handleSelectOption(q.id, option.key)}
                                                            className={`flex items-center gap-3 w-full text-left rounded-xl p-3.5 text-sm transition font-medium ring-1 ${
                                                                isSelected 
                                                                    ? 'bg-indigo-50/60 ring-indigo-500 text-indigo-900' 
                                                                    : 'bg-slate-50/50 hover:bg-slate-50 ring-slate-200/60 text-slate-700 dark:bg-slate-800/60 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-800'
                                                            }`}
                                                        >
                                                            <span className={`flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-black transition ${
                                                                isSelected 
                                                                    ? 'bg-indigo-600 text-white shadow-md' 
                                                                    : 'border border-slate-200 bg-white text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'
                                                            }`}>
                                                                {option.key}
                                                            </span>
                                                            <span>{option.text}</span>
                                                        </button>
                                                    );
                                                })}
                                            </div>
                                        </CardContent>
                                    </div>
                                </Card>
                            ))}
                        </div>

                        {/* Botón de envío */}
                        <div className="flex justify-end pt-4">
                            <button
                                type="button"
                                onClick={handleSubmit}
                                disabled={answeredCount < totalQuestions}
                                className={`inline-flex items-center justify-center gap-2 rounded-2xl font-bold text-sm px-8 py-3.5 shadow-lg transition ${
                                    answeredCount === totalQuestions
                                        ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-600/10'
                                        : 'bg-slate-200 text-slate-450 cursor-not-allowed shadow-none'
                                }`}
                            >
                                Enviar respuestas y calificar
                                <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                )}

                {/* ETAPA 3: RESULTADO PRELIMINAR */}
                {step === 3 && (
                    <div className="space-y-6">
                        {/* Resumen del puntaje */}
                        <Card className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 p-8 text-center relative overflow-hidden">
                            <div className="absolute inset-0 bg-[linear-gradient(to_right,#slate-50_1px,transparent_1px)] opacity-10 pointer-events-none" />
                            
                            <div className="max-w-md mx-auto space-y-4">
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-700/10">
                                    <Award className="h-4 w-4" />
                                    Diagnóstico Académico Finalizado
                                </span>
                                
                                <div className="space-y-1">
                                    <h2 className="text-xl font-bold text-slate-900">Resultado preliminar de desempeño</h2>
                                    <p className="text-xs text-slate-400">Puntaje ponderado calculado a partir de tus respuestas.</p>
                                </div>

                                <div className="relative flex items-center justify-center my-6">
                                    <div className="text-center bg-indigo-50/60 ring-4 ring-indigo-600/5 h-28 w-28 rounded-full flex flex-col justify-center items-center">
                                        <span className="text-3xl font-black text-indigo-950 leading-none">{score}</span>
                                        <span className="text-[10px] font-bold text-indigo-600 mt-1 uppercase tracking-wider">de 100 Pts</span>
                                    </div>
                                </div>

                                <p className="text-xs leading-relaxed text-slate-500">
                                    Has respondido correctamente <span className="font-bold text-slate-900">{correctCount} de {totalQuestions}</span> reactivos evaluados en el examen.
                                </p>
                            </div>
                        </Card>

                        {/* Desglose de respuestas */}
                        <div className="space-y-4">
                            <h3 className="text-sm font-black uppercase tracking-wider text-slate-500">Desglose de competencias</h3>

                            <div className="space-y-3">
                                {examQuestions.map((q) => {
                                    const isCorrect = answers[q.id] === q.correct;
                                    return (
                                        <Card key={q.id} className="border-0 bg-white shadow-sm ring-1 ring-slate-200/80 p-4">
                                            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                                <div className="flex items-start gap-3">
                                                    <span className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs font-bold ${
                                                        isCorrect 
                                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                                    }`}>
                                                        {isCorrect ? <CheckCircle2 className="h-5 w-5" /> : <AlertTriangle className="h-5 w-5" />}
                                                    </span>
                                                    <div>
                                                        <p className="text-xs font-bold text-slate-800 uppercase tracking-widest">{q.area}</p>
                                                        <p className="text-xs text-slate-500 mt-0.5 leading-snug">{q.question.substring(0, 100)}...</p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3 shrink-0 text-xs">
                                                    <span className="text-slate-400">Tu respuesta: <span className="font-bold text-slate-700">{answers[q.id]}</span></span>
                                                    <span className="text-slate-400">Correcta: <span className="font-bold text-emerald-600">{q.correct}</span></span>
                                                </div>
                                            </div>

                                            {/* Retroalimentación */}
                                            <div className={`mt-3 p-3 rounded-xl border text-[11px] leading-relaxed ${
                                                isCorrect 
                                                    ? 'border-emerald-100 bg-emerald-50/20 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200'
                                                    : 'border-rose-100 bg-rose-50/20 text-rose-800 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200'
                                            }`}>
                                                <span className="font-bold block uppercase text-[9px] tracking-wider mb-1">Recomendación:</span>
                                                {isCorrect ? q.feedbackSuccess : q.feedbackFail}
                                            </div>
                                        </Card>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Recomendaciones Generales de Learning Analytics */}
                        <Card className="border-0 bg-gradient-to-br from-indigo-50/30 to-sky-50/20 shadow-sm ring-1 ring-slate-200/80 dark:from-indigo-950/60 dark:to-sky-950/30 dark:ring-slate-800">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-sm font-bold text-slate-900">
                                    <TrendingUp className="h-4.5 w-4.5 text-indigo-600" />
                                    Plan de nivelación sugerido
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <ul className="space-y-2 text-xs leading-relaxed text-slate-600">
                                    <li className="flex gap-2 items-start">
                                        <ChevronRight className="h-4 w-4 text-indigo-600 shrink-0 mt-0.5" />
                                        <span><strong>Competencia de Aritmética:</strong> {answers[1] === 'B' ? 'Excelente comprensión conceptual de porcentajes y balances proporcionales.' : 'Se sugiere priorizar la resolución guiada de problemas de porcentajes y fracciones sucesivas.'}</span>
                                    </li>
                                    <li className="flex gap-2 items-start">
                                        <ChevronRight className="h-4 w-4 text-indigo-600 shrink-0 mt-0.5" />
                                        <span><strong>Competencia de Álgebra:</strong> {answers[2] === 'C' ? 'Habilidad consolidada en resolución de sistemas lineales y simplificación algebraica.' : 'Es crítico practicar la igualación y eliminación en ecuaciones con dos incógnitas.'}</span>
                                    </li>
                                    <li className="flex gap-2 items-start">
                                        <ChevronRight className="h-4 w-4 text-indigo-600 shrink-0 mt-0.5" />
                                        <span><strong>Competencia de Razonamiento Lógico:</strong> {answers[3] === 'B' ? 'Destreza en identificación de reglas matemáticas recurrentes y secuencias.' : 'Se aconseja trabajar en la inducción de sucesiones mediante diferencias consecutivas.'}</span>
                                    </li>
                                </ul>
                            </CardContent>
                        </Card>

                        {/* Acciones */}
                        <div className="flex justify-between items-center pt-4">
                            <button
                                type="button"
                                onClick={handleReset}
                                className="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                <RefreshCw className="h-4 w-4" />
                                Volver a evaluaciones
                            </button>
                            
                            <Link href="/">
                                <button className="inline-flex h-10 items-center gap-2 rounded-xl bg-slate-900 px-5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                                    Finalizar y salir
                                </button>
                            </Link>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
