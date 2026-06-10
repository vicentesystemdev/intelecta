import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Progress } from '@/Components/ui/progress';
import useTheme from '@/Hooks/useTheme';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import {
    Activity,
    ArrowRight,
    BarChart3,
    BellRing,
    BookOpenCheck,
    BrainCircuit,
    ChartNoAxesCombined,
    CheckCircle2,
    ClipboardCheck,
    FlaskConical,
    Gauge,
    GraduationCap,
    Lightbulb,
    LineChart,
    Microscope,
    ShieldAlert,
    Sparkles,
    Target,
    TrendingUp,
    Users,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

const kpis = [
    ['Postulantes analizados', '186', '84% de la cohorte activa', Users, 'secondary'],
    ['Evaluaciones procesadas', '742', '4 cortes académicos consolidados', ClipboardCheck, 'info'],
    ['Promedio institucional', '68,4', 'Escala referencial sobre 100', TrendingUp, 'accent'],
    ['Riesgo académico alto', '14%', '26 postulantes requieren atención', ShieldAlert, 'danger'],
    ['Seguimiento prioritario', '39', 'Casos con plan de refuerzo sugerido', BellRing, 'warning'],
    ['Cobertura evaluada', '82%', 'Materias y áreas curriculares base', Target, 'secondary'],
].map(([title, value, note, icon, tone]) => ({ title, value, note, icon, tone }));

const institutionalStatus = [
    {
        title: 'Estado de preparación matemática',
        status: 'En seguimiento',
        statusClass: 'border-brand-warning/25 bg-brand-warning/10 text-brand-warning',
        icon: Gauge,
        iconClass: 'bg-brand-warning/10 text-brand-warning',
        interpretation: 'El promedio se mantiene en nivel intermedio, con brechas recurrentes en álgebra y resolución de problemas.',
        recommendation: 'Organizar sesiones de nivelación diferenciadas antes del siguiente simulacro.',
    },
    {
        title: 'Rendimiento en ciencias exactas',
        status: 'Favorable',
        statusClass: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
        icon: FlaskConical,
        iconClass: 'bg-brand-success/10 text-brand-success',
        interpretation: 'Física y Química presentan avance sostenido, especialmente en postulantes orientados a Ingeniería.',
        recommendation: 'Mantener práctica aplicada y reforzar la interpretación de enunciados científicos.',
    },
    {
        title: 'Nivel de alerta preuniversitaria',
        status: 'Medio',
        statusClass: 'border-brand-info/25 bg-brand-info/10 text-brand-info',
        icon: Activity,
        iconClass: 'bg-brand-info/10 text-brand-info',
        interpretation: 'La mayoría de la cohorte progresa, aunque existe un grupo reducido con dificultades acumuladas.',
        recommendation: 'Priorizar tutorías breves y verificar avances mediante evaluaciones focalizadas.',
    },
];

const subjectPerformance = [
    { materia: 'Matemática', promedio: 64 },
    { materia: 'Física', promedio: 69 },
    { materia: 'Química', promedio: 72 },
    { materia: 'Razonamiento', promedio: 75 },
];

const performanceDistribution = [
    { materia: 'Matemática', alto: 24, medio: 49, bajo: 27 },
    { materia: 'Física', alto: 31, medio: 48, bajo: 21 },
    { materia: 'Química', alto: 35, medio: 47, bajo: 18 },
    { materia: 'Razonamiento', alto: 39, medio: 46, bajo: 15 },
];

const performanceTrend = [
    { corte: 'Corte 1', promedio: 61 },
    { corte: 'Corte 2', promedio: 65 },
    { corte: 'Corte 3', promedio: 69 },
    { corte: 'Simulacro final', promedio: 73 },
];

const riskDistribution = [
    { name: 'Bajo riesgo', value: 96, color: '#10b981' },
    { name: 'Riesgo medio', value: 64, color: '#f59e0b' },
    { name: 'Riesgo alto', value: 26, color: '#f43f5e' },
];

const reinforcementAreas = [
    { area: 'Álgebra', necesidad: 78 },
    { area: 'Interpretación de problemas', necesidad: 72 },
    { area: 'Trigonometría', necesidad: 66 },
    { area: 'Cinemática', necesidad: 58 },
    { area: 'Razonamiento lógico', necesidad: 51 },
    { area: 'Estequiometría', necesidad: 45 },
];

const applicants = [
    {
        id: 1, name: 'Camila Quispe Mamani', age: 18, school: 'U.E. San Andrés', university: 'UMSA',
        career: 'Ingeniería Civil', requirement: 'Alta', average: 58, risk: 'Alto', criticalSubject: 'Matemática',
        weakness: 'Álgebra y planteamiento de problemas',
        recommendation: 'Asignar nivelación de álgebra básica y seguimiento semanal con ejercicios contextualizados.',
        strengths: ['Razonamiento verbal', 'Constancia en evaluaciones'], weaknesses: ['Álgebra', 'Cinemática'],
        scores: [{ materia: 'Matemática', puntaje: 48 }, { materia: 'Física', puntaje: 55 }, { materia: 'Química', puntaje: 63 }, { materia: 'Razonamiento', puntaje: 68 }],
    },
    {
        id: 2, name: 'Diego Choque Condori', age: 19, school: 'Colegio Don Bosco', university: 'EMI',
        career: 'Ingeniería de Sistemas', requirement: 'Alta', average: 62, risk: 'Alto', criticalSubject: 'Física',
        weakness: 'Cinemática y análisis de unidades',
        recommendation: 'Reforzar resolución gráfica de problemas físicos y control de procedimientos.',
        strengths: ['Lógica computacional', 'Álgebra básica'], weaknesses: ['Cinemática', 'Comprensión de enunciados'],
        scores: [{ materia: 'Matemática', puntaje: 66 }, { materia: 'Física', puntaje: 51 }, { materia: 'Química', puntaje: 61 }, { materia: 'Razonamiento', puntaje: 70 }],
    },
    {
        id: 3, name: 'Valeria Flores Apaza', age: 18, school: 'U.E. Santa Rosa', university: 'UCB',
        career: 'Economía', requirement: 'Media-alta', average: 65, risk: 'Medio', criticalSubject: 'Matemática',
        weakness: 'Funciones y lectura de gráficos',
        recommendation: 'Aplicar una secuencia corta de funciones, porcentajes y análisis gráfico.',
        strengths: ['Razonamiento académico', 'Interpretación de textos'], weaknesses: ['Funciones', 'Trigonometría'],
        scores: [{ materia: 'Matemática', puntaje: 57 }, { materia: 'Física', puntaje: 63 }, { materia: 'Química', puntaje: 66 }, { materia: 'Razonamiento', puntaje: 74 }],
    },
    {
        id: 4, name: 'Luis Fernando Huanca', age: 20, school: 'Colegio Nacional Ayacucho', university: 'UMSA',
        career: 'Ingeniería de Sistemas', requirement: 'Alta', average: 67, risk: 'Medio', criticalSubject: 'Química',
        weakness: 'Estequiometría y conversión de unidades',
        recommendation: 'Programar práctica guiada en relaciones molares y ejercicios progresivos.',
        strengths: ['Álgebra', 'Razonamiento lógico'], weaknesses: ['Estequiometría', 'Manejo del tiempo'],
        scores: [{ materia: 'Matemática', puntaje: 72 }, { materia: 'Física', puntaje: 68 }, { materia: 'Química', puntaje: 54 }, { materia: 'Razonamiento', puntaje: 73 }],
    },
    {
        id: 5, name: 'Andrea Nina Callisaya', age: 17, school: 'U.E. República de Francia', university: 'UNIFRANZ',
        career: 'Ingeniería Comercial', requirement: 'Media-alta', average: 69, risk: 'Medio', criticalSubject: 'Matemática',
        weakness: 'Trigonometría y rapidez operativa',
        recommendation: 'Reforzar identidades básicas y establecer ejercicios con tiempo controlado.',
        strengths: ['Química', 'Interpretación de problemas'], weaknesses: ['Trigonometría', 'Rapidez de cálculo'],
        scores: [{ materia: 'Matemática', puntaje: 60 }, { materia: 'Física', puntaje: 67 }, { materia: 'Química', puntaje: 75 }, { materia: 'Razonamiento', puntaje: 74 }],
    },
    {
        id: 6, name: 'Marcelo Ticona Yujra', age: 19, school: 'U.E. Franz Tamayo', university: 'UPEA',
        career: 'Ingeniería Electrónica', requirement: 'Media', average: 57, risk: 'Alto', criticalSubject: 'Razonamiento',
        weakness: 'Secuencias y comprensión lógica',
        recommendation: 'Incorporar ejercicios breves de patrones, analogías y razonamiento secuencial.',
        strengths: ['Química básica', 'Participación en clase'], weaknesses: ['Secuencias lógicas', 'Álgebra'],
        scores: [{ materia: 'Matemática', puntaje: 52 }, { materia: 'Física', puntaje: 59 }, { materia: 'Química', puntaje: 65 }, { materia: 'Razonamiento', puntaje: 50 }],
    },
    {
        id: 7, name: 'Natalia Pari Chura', age: 18, school: 'Colegio La Salle', university: 'UMSA',
        career: 'Bioquímica', requirement: 'Alta', average: 63, risk: 'Medio', criticalSubject: 'Química',
        weakness: 'Balanceo químico y estequiometría',
        recommendation: 'Asignar laboratorio conceptual y práctica de balanceo por niveles.',
        strengths: ['Biología aplicada', 'Lectura científica'], weaknesses: ['Estequiometría', 'Cálculo mental'],
        scores: [{ materia: 'Matemática', puntaje: 61 }, { materia: 'Física', puntaje: 58 }, { materia: 'Química', puntaje: 55 }, { materia: 'Razonamiento', puntaje: 76 }],
    },
];

const teacherInsights = [
    {
        title: 'Hallazgo principal institucional', icon: BrainCircuit, tone: 'secondary',
        interpretation: 'La cohorte avanza de forma sostenida, pero Matemática concentra la mayor dispersión entre grupos.',
        recommendation: 'Aplicar una evaluación focalizada de álgebra antes de reorganizar los grupos de apoyo.',
    },
    {
        title: 'Grupo que requiere nivelación', icon: Users, tone: 'danger',
        interpretation: 'Los postulantes a carreras de alta exigencia con promedio menor a 60 requieren seguimiento cercano.',
        recommendation: 'Crear una ruta intensiva de dos semanas con metas verificables por materia.',
    },
    {
        title: 'Área curricular prioritaria', icon: BookOpenCheck, tone: 'warning',
        interpretation: 'Álgebra e interpretación de problemas aparecen como brechas transversales en la cohorte.',
        recommendation: 'Integrar ejercicios contextualizados en Matemática, Física y Razonamiento Académico.',
    },
    {
        title: 'Acción recomendada inmediata', icon: Lightbulb, tone: 'info',
        interpretation: 'El siguiente corte puede emplearse para comprobar si las intervenciones reducen las brechas observadas.',
        recommendation: 'Registrar resultados por tema y comparar la evolución de cada grupo de seguimiento.',
    },
];

const analyticalLayers = [
    {
        title: 'Ingestión y captura de datos',
        subtitle: 'Módulo web de registro académico',
        description:
            'Captura los datos generados por los postulantes durante evaluaciones y actividades académicas. Esta capa convierte la interacción del estudiante en variables estructuradas para análisis posterior.',
        items: [
            'Calificaciones de simulacros',
            'Respuestas por pregunta',
            'Puntaje por materia',
            'Tiempo de resolución',
            'Intentos realizados',
            'Asistencia',
            'Datos sociodemográficos básicos',
            'Carrera objetivo',
        ],
        icon: ClipboardCheck,
        status: 'Base implementada parcialmente',
        tone: 'secondary',
        listTitle: 'Datos capturados',
    },
    {
        title: 'Procesamiento psicométrico y comportamental',
        subtitle: 'Backend analítico y procesos en segundo plano',
        description:
            'Procesa las respuestas ítem por ítem y transforma los resultados brutos en variables académicas enriquecidas. Esta capa prepara los datos para modelos de clasificación y predicción.',
        modules: [
            {
                title: 'Motor IRT',
                description:
                    'Relaciona la dificultad de cada pregunta con la habilidad estimada del postulante mediante el parámetro Theta (θ), reduciendo el sesgo entre exámenes con distinta dificultad.',
            },
            {
                title: 'Motor de clustering',
                description:
                    'Agrupa postulantes con patrones similares de rendimiento, velocidad, errores recurrentes y riesgo académico.',
            },
        ],
        items: [
            'Habilidad estimada θ',
            'Dificultad calibrada del ítem',
            'Perfiles de rendimiento',
            'Grupos de riesgo',
            'Patrones de error',
        ],
        icon: Activity,
        status: 'Fase psicométrica proyectada',
        tone: 'info',
        listTitle: 'Variables generadas',
    },
    {
        title: 'Inteligencia predictiva',
        subtitle: 'Modelos de Machine Learning',
        description:
            'Utiliza variables limpias y enriquecidas para estimar riesgo académico y rendimiento esperado. Esta capa requiere datos históricos reales para validarse metodológicamente.',
        modules: [
            {
                title: 'Regresión logística',
                description:
                    'Modelo base para clasificar al postulante en estados como “en seguimiento” o “preparación favorable”, con pesos interpretables para el tutor.',
            },
            {
                title: 'Random Forest Regressor',
                description:
                    'Modelo de ensamble proyectado para estimar una nota continua esperada, considerando variables académicas y comportamentales.',
            },
        ],
        items: [
            'Nivel de riesgo académico',
            'Rendimiento estimado',
            'Variables influyentes',
            'Tendencia de preparación',
            'Confianza del modelo',
        ],
        icon: BrainCircuit,
        status: 'Fase predictiva en validación',
        tone: 'warning',
        listTitle: 'Salidas esperadas',
    },
    {
        title: 'Visualización y acción',
        subtitle: 'Dashboard académico para tutores',
        description:
            'Presenta los resultados analíticos en una interfaz comprensible para tutores y coordinación académica, permitiendo priorizar apoyo y tomar decisiones de nivelación.',
        items: [
            'Semáforos de riesgo',
            'Alertas tempranas',
            'Reportes por materia',
            'Perfil académico del postulante',
            'Recomendaciones de nivelación',
            'Priorización de tutoría',
        ],
        beneficiaries: [
            'Tutor académico',
            'Coordinación académica',
            'Administración del instituto',
            'Postulante',
        ],
        icon: BellRing,
        status: 'Vista institucional referencial',
        tone: 'success',
        listTitle: 'Elementos visuales',
    },
];

const layerConnectors = [
    'Datos capturados',
    'Variables enriquecidas',
    'Modelos predictivos',
    'Decisiones académicas',
];

const roadmapToneStyles = {
    primary: {
        icon: 'bg-brand-primary/10 text-brand-primary ring-brand-primary/20 dark:bg-brand-primary/25 dark:text-white',
        accent: 'bg-brand-primary',
        bullet: 'bg-brand-primary',
    },
    secondary: {
        icon: 'bg-brand-secondary/10 text-brand-secondary ring-brand-secondary/20 dark:bg-brand-secondary/25 dark:text-white',
        accent: 'bg-brand-secondary',
        bullet: 'bg-brand-secondary',
    },
    info: {
        icon: 'bg-brand-info/10 text-brand-info ring-brand-info/20 dark:bg-brand-info/25 dark:text-white',
        accent: 'bg-brand-info',
        bullet: 'bg-brand-info',
    },
    accent: {
        icon: 'bg-brand-accent/10 text-brand-accent ring-brand-accent/20 dark:bg-brand-accent/25 dark:text-white',
        accent: 'bg-brand-accent',
        bullet: 'bg-brand-accent',
    },
    success: {
        icon: 'bg-brand-success/10 text-brand-success ring-brand-success/20 dark:bg-brand-success/25 dark:text-white',
        accent: 'bg-brand-success',
        bullet: 'bg-brand-success',
    },
    warning: {
        icon: 'bg-brand-warning/10 text-brand-warning ring-brand-warning/20 dark:bg-brand-warning/25 dark:text-white',
        accent: 'bg-brand-warning',
        bullet: 'bg-brand-warning',
    },
    danger: {
        icon: 'bg-brand-danger/10 text-brand-danger ring-brand-danger/20 dark:bg-brand-danger/25 dark:text-white',
        accent: 'bg-brand-danger',
        bullet: 'bg-brand-danger',
    },
};

const roadmapStatusStyles = {
    'Base implementada parcialmente':
        'border-brand-success/20 bg-brand-success/10 text-brand-success dark:border-brand-success/30 dark:bg-brand-success/20',
    'Fase psicométrica proyectada':
        'border-brand-info/20 bg-brand-info/10 text-brand-info dark:border-brand-info/30 dark:bg-brand-info/20',
    'Fase predictiva en validación':
        'border-brand-warning/20 bg-brand-warning/10 text-brand-warning dark:border-brand-warning/30 dark:bg-brand-warning/20',
    'Vista institucional referencial':
        'border-brand-secondary/20 bg-brand-secondary/10 text-brand-secondary dark:border-brand-secondary/30 dark:bg-brand-secondary/20',
};

const toneStyles = {
    primary: { icon: 'bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/25', progress: '[&_[data-slot=progress-indicator]]:bg-brand-primary' },
    secondary: { icon: 'bg-brand-secondary/10 text-brand-secondary dark:bg-brand-secondary/25', progress: '[&_[data-slot=progress-indicator]]:bg-brand-secondary' },
    accent: { icon: 'bg-brand-accent/10 text-brand-accent dark:bg-brand-accent/25 dark:text-brand-accent', progress: '[&_[data-slot=progress-indicator]]:bg-brand-accent' },
    info: { icon: 'bg-brand-info/10 text-brand-info dark:bg-brand-info/25', progress: '[&_[data-slot=progress-indicator]]:bg-brand-info' },
    success: { icon: 'bg-brand-success/10 text-brand-success dark:bg-brand-success/25', progress: '[&_[data-slot=progress-indicator]]:bg-brand-success' },
    warning: { icon: 'bg-brand-warning/10 text-brand-warning dark:bg-brand-warning/25', progress: '[&_[data-slot=progress-indicator]]:bg-brand-warning' },
    danger: { icon: 'bg-brand-danger/10 text-brand-danger dark:bg-brand-danger/25', progress: '[&_[data-slot=progress-indicator]]:bg-brand-danger' },
};

const riskStyles = {
    Alto: 'border-brand-danger/20 bg-brand-danger/10 text-brand-danger dark:border-brand-danger/30 dark:bg-brand-danger/20',
    Medio: 'border-brand-warning/20 bg-brand-warning/10 text-brand-warning dark:border-brand-warning/30 dark:bg-brand-warning/20',
    Bajo: 'border-brand-success/20 bg-brand-success/10 text-brand-success dark:border-brand-success/30 dark:bg-brand-success/20',
};

function SectionHeading({ eyebrow, title, description, icon: Icon }) {
    return (
        <div className="mb-5 flex items-start gap-3">
            <span className="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-secondary/15 text-brand-secondary">
                <Icon className="h-5 w-5" />
            </span>
            <div className="min-w-0">
                <p className="text-xs font-semibold uppercase tracking-[0.16em] text-brand-secondary">{eyebrow}</p>
                <h2 className="mt-1 text-xl font-bold leading-snug text-text-main">{title}</h2>
                <p className="mt-1 max-w-3xl text-sm leading-6 text-text-muted">{description}</p>
            </div>
        </div>
    );
}

function ChartCard({ title, description, icon: Icon, children, className = '' }) {
    return (
        <Card className={`bg-brand-card border border-brand-border rounded-2xl shadow-sm ${className}`}>
            <CardHeader className="px-5 pb-2 pt-5 sm:px-6 sm:pt-6">
                <CardTitle className="flex items-start gap-2 text-base font-semibold leading-snug text-text-main">
                    <Icon className="mt-0.5 h-4.5 w-4.5 shrink-0 text-brand-secondary" />
                    <span>{title}</span>
                </CardTitle>
                <p className="text-xs leading-5 text-text-muted">{description}</p>
            </CardHeader>
            <CardContent className="px-3 pb-5 pt-2 sm:px-5 sm:pb-6">{children}</CardContent>
        </Card>
    );
}

function RiskBadge({ risk }) {
    return <Badge variant="outline" className={`shrink-0 whitespace-nowrap ${riskStyles[risk] || riskStyles.Medio}`}>{risk}</Badge>;
}

export default function LearningAnalytics() {
    const { isDark } = useTheme();
    const [selectedApplicantId, setSelectedApplicantId] = useState(applicants[0].id);
    const selectedApplicant = useMemo(
        () => applicants.find((applicant) => applicant.id === selectedApplicantId) || applicants[0],
        [selectedApplicantId],
    );
    const gridColor = isDark ? '#1F2937' : '#E5E7EB';
    const axisColor = isDark ? '#9CA3AF' : '#6B7280';
    const tooltipStyle = {
        backgroundColor: isDark ? '#101827' : '#FFFFFF',
        border: `1px solid ${isDark ? '#1F2937' : '#E5E7EB'}`,
        borderRadius: 12,
        color: isDark ? '#F9FAFB' : '#111827',
        fontSize: 12,
        boxShadow: '0 12px 28px rgba(0, 0, 0, 0.12)',
    };

    const activeColors = {
        primary: '#16213E',
        secondary: isDark ? '#14B8A6' : '#0F766E',
        accent: isDark ? '#FBBF24' : '#E0B84C',
        success: isDark ? '#22C55E' : '#15803D',
        warning: isDark ? '#F59E0B' : '#D97706',
        danger: isDark ? '#EF4444' : '#DC2626',
        info: isDark ? '#3B82F6' : '#2563EB',
    };

    const riskDistributionData = useMemo(() => [
        { name: 'Bajo riesgo', value: 96, color: activeColors.success },
        { name: 'Riesgo medio', value: 64, color: activeColors.warning },
        { name: 'Riesgo alto', value: 26, color: activeColors.danger },
    ], [activeColors]);

    return (
        <AdminLayout title="Learning Analytics" subtitle="Lectura analítica del desempeño académico preuniversitario." wide>
            <Head title="Learning Analytics" />
            <div className="space-y-8">
                <section className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-6 text-white shadow-xl shadow-brand-primary/20 sm:p-8 lg:p-10">
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:28px_28px]" />
                    <div className="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-brand-accent/10 blur-3xl" />
                    <div className="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-4xl">
                            <div className="mb-4 flex flex-wrap items-center gap-2">
                                <Badge className="border border-brand-accent/20 bg-brand-accent/10 text-brand-accent"><Sparkles className="h-3.5 w-3.5" />Simulación analítica institucional</Badge>
                                <Badge className="border border-white/15 bg-white/10 text-brand-accent">Base para seguimiento institucional</Badge>
                            </div>
                            <h1 className="text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl text-white">Learning Analytics</h1>
                            <p className="mt-3 text-base font-medium text-brand-accent sm:text-lg">Lectura analítica del desempeño académico preuniversitario</p>
                            <p className="mt-4 max-w-3xl text-sm leading-6 text-slate-200 sm:text-base sm:leading-7">
                                Esta vista presenta cómo INTELECTA consolidará hallazgos institucionales, alertas de seguimiento y perfiles académicos a partir de evaluaciones aplicadas.
                            </p>
                        </div>
                        <div className="w-full rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm sm:w-auto sm:min-w-64">
                            <div className="flex items-center gap-3">
                                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-accent text-brand-primary"><BrainCircuit className="h-6 w-6" /></span>
                                <div><p className="text-xs font-semibold uppercase tracking-wider text-brand-accent">Estado del módulo</p><p className="mt-1 text-sm font-semibold text-white">Vista institucional referencial</p></div>
                            </div>
                            <p className="mt-3 text-xs leading-5 text-slate-200 font-medium">Resultados referenciales para exposición. No corresponden a inferencia predictiva operativa.</p>
                        </div>
                    </div>
                </section>

                <section>
                    <SectionHeading eyebrow="Vista ejecutiva" title="Indicadores analíticos principales" description="Resumen referencial de cobertura, desempeño y seguimiento para la coordinación académica." icon={ChartNoAxesCombined} />
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                        {kpis.map(({ title, value, note, icon: Icon, tone }) => (
                            <Card key={title} className="bg-brand-card border border-brand-border rounded-2xl p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:scale-[1.01]">
                                <CardContent className="p-0 space-y-4">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0"><p className="text-xs font-semibold leading-snug text-text-muted">{title}</p><p className="mt-2 text-3xl font-extrabold tracking-tight text-text-main">{value}</p></div>
                                        <span className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-brand-border ${toneStyles[tone].icon}`}><Icon className="h-5 w-5" /></span>
                                    </div>
                                    <p className="border-t border-brand-border pt-3 text-[11px] leading-4 text-text-muted">{note}</p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>

                <section>
                    <SectionHeading eyebrow="Lectura institucional" title="Estado general del instituto" description="Interpretación preliminar para orientar decisiones docentes y acciones de nivelación." icon={Microscope} />
                    <div className="grid gap-5 lg:grid-cols-3">
                        {institutionalStatus.map((item) => {
                            const Icon = item.icon;
                            return (
                                <Card key={item.title} className="bg-brand-card border border-brand-border rounded-2xl p-5 sm:p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:scale-[1.01]">
                                    <CardContent className="p-0 flex flex-col justify-between h-full">
                                        <div>
                                            <div className="flex items-start justify-between gap-4"><span className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-brand-border ${item.iconClass}`}><Icon className="h-5 w-5" /></span><Badge variant="outline" className={`border ${item.statusClass}`}>{item.status}</Badge></div>
                                            <h3 className="mt-5 text-base font-bold leading-snug text-text-main">{item.title}</h3>
                                            <p className="mt-3 text-sm leading-6 text-text-muted">{item.interpretation}</p>
                                        </div>
                                        <div className="mt-5 rounded-xl border border-brand-border bg-brand-primary/5 p-4">
                                            <p className="text-[11px] font-bold uppercase tracking-wider text-brand-secondary">Orientación académica</p>
                                            <p className="mt-1.5 text-xs leading-5 text-text-muted">{item.recommendation}</p>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                </section>

                <section>
                    <SectionHeading eyebrow="Comportamiento académico" title="Visualizaciones institucionales" description="Resultados referenciales para observar distribución, evolución y áreas de refuerzo de la cohorte." icon={BarChart3} />
                    <div className="grid gap-5 xl:grid-cols-2">
                        <ChartCard title="Desempeño promedio por materia" description="Puntaje promedio referencial sobre 100." icon={BarChart3}>
                            <div className="h-72 w-full"><ResponsiveContainer width="100%" height="100%"><BarChart data={subjectPerformance} margin={{ top: 16, right: 12, left: -12, bottom: 8 }}><CartesianGrid stroke={gridColor} strokeDasharray="3 3" vertical={false} /><XAxis dataKey="materia" tick={{ fill: axisColor, fontSize: 11 }} axisLine={false} tickLine={false} interval={0} /><YAxis domain={[0, 100]} tick={{ fill: axisColor, fontSize: 11 }} axisLine={false} tickLine={false} /><Tooltip contentStyle={tooltipStyle} cursor={{ fill: isDark ? 'rgba(31, 41, 55, 0.4)' : 'rgba(229, 231, 235, 0.4)' }} formatter={(value) => [`${value} puntos`, 'Promedio']} /><Bar dataKey="promedio" fill={activeColors.secondary} radius={[8, 8, 0, 0]} maxBarSize={54} /></BarChart></ResponsiveContainer></div>
                        </ChartCard>
                        <ChartCard title="Distribución por nivel de desempeño" description="Proporción porcentual por materia: alto, medio y bajo." icon={Activity}>
                            <div className="h-72 w-full"><ResponsiveContainer width="100%" height="100%"><BarChart data={performanceDistribution} margin={{ top: 16, right: 12, left: -12, bottom: 8 }}><CartesianGrid stroke={gridColor} strokeDasharray="3 3" vertical={false} /><XAxis dataKey="materia" tick={{ fill: axisColor, fontSize: 11 }} axisLine={false} tickLine={false} interval={0} /><YAxis domain={[0, 100]} tick={{ fill: axisColor, fontSize: 11 }} axisLine={false} tickLine={false} /><Tooltip contentStyle={tooltipStyle} formatter={(value) => `${value}%`} /><Legend wrapperStyle={{ fontSize: 11, color: axisColor }} /><Bar dataKey="alto" name="Alto" stackId="nivel" fill={activeColors.success} radius={[0, 0, 3, 3]} /><Bar dataKey="medio" name="Medio" stackId="nivel" fill={activeColors.warning} /><Bar dataKey="bajo" name="Bajo" stackId="nivel" fill={activeColors.danger} radius={[6, 6, 0, 0]} /></BarChart></ResponsiveContainer></div>
                        </ChartCard>
                        <ChartCard title="Tendencia de rendimiento por cortes evaluativos" description="Evolución general de la cohorte durante el ciclo de preparación." icon={LineChart}>
                            <div className="h-72 w-full"><ResponsiveContainer width="100%" height="100%"><AreaChart data={performanceTrend} margin={{ top: 16, right: 18, left: -12, bottom: 8 }}><defs><linearGradient id="analyticsArea" x1="0" y1="0" x2="0" y2="1"><stop offset="5%" stopColor={activeColors.info} stopOpacity={0.42} /><stop offset="95%" stopColor={activeColors.info} stopOpacity={0.03} /></linearGradient></defs><CartesianGrid stroke={gridColor} strokeDasharray="3 3" vertical={false} /><XAxis dataKey="corte" tick={{ fill: axisColor, fontSize: 11 }} axisLine={false} tickLine={false} /><YAxis domain={[40, 100]} tick={{ fill: axisColor, fontSize: 11 }} axisLine={false} tickLine={false} /><Tooltip contentStyle={tooltipStyle} formatter={(value) => [`${value} puntos`, 'Promedio']} /><Area type="monotone" dataKey="promedio" stroke={activeColors.info} strokeWidth={3} fill="url(#analyticsArea)" activeDot={{ r: 6, fill: activeColors.secondary }} /></AreaChart></ResponsiveContainer></div>
                        </ChartCard>
                        <ChartCard title="Postulantes según nivel de riesgo académico" description="Distribución referencial para priorizar el seguimiento." icon={ShieldAlert}>
                            <div className="relative h-72 w-full"><ResponsiveContainer width="100%" height="100%"><PieChart><Pie data={riskDistributionData} dataKey="value" nameKey="name" innerRadius={68} outerRadius={102} paddingAngle={3} stroke="transparent">{riskDistributionData.map((item) => <Cell key={item.name} fill={item.color} />)}</Pie><Tooltip contentStyle={tooltipStyle} formatter={(value) => [`${value} postulantes`, 'Total']} /><Legend verticalAlign="bottom" wrapperStyle={{ fontSize: 11, color: axisColor }} /></PieChart></ResponsiveContainer><div className="pointer-events-none absolute inset-x-0 top-[43%] text-center"><p className="text-2xl font-black text-text-main">186</p><p className="text-[10px] font-semibold uppercase tracking-wider text-text-muted">perfiles</p></div></div>
                        </ChartCard>
                        <ChartCard title="Áreas con mayor necesidad de refuerzo" description="Índice referencial de prioridad académica por contenido." icon={Target} className="xl:col-span-2">
                            <div className="h-80 w-full"><ResponsiveContainer width="100%" height="100%"><BarChart data={reinforcementAreas} layout="vertical" margin={{ top: 8, right: 24, left: 22, bottom: 8 }}><CartesianGrid stroke={gridColor} strokeDasharray="3 3" horizontal={false} /><XAxis type="number" domain={[0, 100]} tick={{ fill: axisColor, fontSize: 11 }} axisLine={false} tickLine={false} /><YAxis type="category" dataKey="area" width={145} tick={{ fill: axisColor, fontSize: 11 }} axisLine={false} tickLine={false} /><Tooltip contentStyle={tooltipStyle} cursor={{ fill: isDark ? 'rgba(31, 41, 55, 0.4)' : 'rgba(229, 231, 235, 0.4)' }} formatter={(value) => [`${value}%`, 'Prioridad de refuerzo']} /><Bar dataKey="necesidad" fill={activeColors.secondary} radius={[0, 8, 8, 0]} maxBarSize={26} /></BarChart></ResponsiveContainer></div>
                        </ChartCard>
                    </div>
                </section>

                <section>
                    <SectionHeading eyebrow="Seguimiento sugerido" title="Mapa de alertas académicas" description="Casos referenciales que muestran cómo la coordinación podría priorizar acompañamiento y refuerzo." icon={BellRing} />
                    <Card className="bg-brand-card border border-brand-border rounded-2xl shadow-sm overflow-hidden">
                        <CardContent className="p-0">
                            <div className="hidden overflow-hidden lg:block">
                                <table className="w-full table-fixed">
                                    <thead>
                                        <tr>
                                            {['Postulante', 'Carrera objetivo', 'Riesgo', 'Principal debilidad', 'Acción sugerida'].map((label, index) => (
                                                <th key={label} className={`${['w-[22%]', 'w-[18%]', 'w-[12%]', 'w-[22%]', 'w-[26%]'][index]} bg-brand-primary/5 dark:bg-brand-card border-b border-brand-border text-left text-[10px] font-extrabold text-text-muted uppercase tracking-wider p-4`}>{label}</th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {applicants.map((applicant) => (
                                            <tr key={applicant.id} className="bg-brand-card border-b border-brand-border/60 hover:bg-brand-primary/[0.02] dark:hover:bg-brand-border/20 transition-colors">
                                                <td className="p-4 text-xs font-semibold text-text-main whitespace-normal break-words leading-snug"><p className="font-semibold text-text-main">{applicant.name}</p><p className="mt-1 text-[10px] text-text-muted">{applicant.school}</p></td>
                                                <td className="p-4 text-xs font-medium text-text-main whitespace-normal break-words leading-snug"><p className="font-medium text-text-main">{applicant.career}</p><p className="mt-1 text-[10px] text-text-muted">{applicant.university}</p></td>
                                                <td className="p-4 text-xs font-medium text-text-main"><RiskBadge risk={applicant.risk} /></td>
                                                <td className="p-4 text-xs font-medium text-text-main whitespace-normal break-words leading-snug"><p className="text-text-main font-medium">{applicant.weakness}</p><p className="mt-1 text-[10px] font-semibold text-brand-danger">{applicant.criticalSubject}</p></td>
                                                <td className="p-4 text-xs font-medium text-text-muted whitespace-normal break-words leading-snug">{applicant.recommendation}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            <div className="grid gap-4 p-4 sm:grid-cols-2 lg:hidden">
                                {applicants.map((applicant) => (
                                    <article key={applicant.id} className="rounded-xl border border-brand-border p-4 bg-brand-card">
                                        <div className="flex items-start justify-between gap-3"><div className="min-w-0"><h3 className="break-words text-sm font-bold leading-snug text-text-main">{applicant.name}</h3><p className="mt-1 text-xs text-text-muted">{applicant.career} · {applicant.university}</p></div><RiskBadge risk={applicant.risk} /></div>
                                        <div className="mt-4 space-y-3 text-xs"><div><p className="font-semibold text-text-muted">Principal debilidad</p><p className="mt-1 leading-5 text-text-main">{applicant.weakness}</p></div><div><p className="font-semibold text-text-muted">Acción sugerida</p><p className="mt-1 leading-5 text-text-muted">{applicant.recommendation}</p></div></div>
                                    </article>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </section>

                <section>
                    <SectionHeading eyebrow="Lectura individual" title="Perfil analítico por postulante" description="Ejemplo de cómo se presentaría un perfil académico preliminar con fortalezas, brechas y orientación personalizada." icon={GraduationCap} />
                    <div className="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
                        <Card className="bg-brand-card border border-brand-border rounded-2xl shadow-sm">
                            <CardHeader className="px-5 pb-3 pt-5"><CardTitle className="text-sm font-bold text-text-main">Seleccionar perfil</CardTitle><p className="text-xs leading-5 text-text-muted">La selección funciona localmente para esta vista.</p></CardHeader>
                            <CardContent className="space-y-2 px-3 pb-4">
                                {applicants.map((applicant) => (
                                    <button key={applicant.id} type="button" onClick={() => setSelectedApplicantId(applicant.id)} className={`flex w-full items-center justify-between gap-3 rounded-xl px-3 py-3 text-left transition ${selectedApplicantId === applicant.id ? 'bg-brand-secondary/10 border border-brand-secondary/20' : 'hover:bg-brand-primary/5'}`}>
                                        <span className="min-w-0"><span className="block break-words text-xs font-semibold leading-snug text-text-main">{applicant.name}</span><span className="mt-1 block text-[11px] text-text-muted">{applicant.university} · {applicant.career}</span></span><ArrowRight className={`h-4 w-4 shrink-0 ${selectedApplicantId === applicant.id ? 'text-brand-secondary' : 'text-text-muted'}`} />
                                    </button>
                                ))}
                            </CardContent>
                        </Card>
                        <Card className="bg-brand-card border border-brand-border rounded-2xl shadow-sm">
                            <CardContent className="p-5 sm:p-6">
                                <div className="flex flex-col gap-5 border-b border-brand-border pb-6 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="flex min-w-0 items-start gap-4">
                                        <span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary text-lg font-black text-white shadow-lg shadow-brand-secondary/20">{selectedApplicant.name.split(' ').slice(0, 2).map((name) => name[0]).join('')}</span>
                                        <div className="min-w-0"><div className="flex flex-wrap items-center gap-2"><h3 className="break-words text-xl font-black leading-snug text-text-main">{selectedApplicant.name}</h3><RiskBadge risk={selectedApplicant.risk} /></div><p className="mt-1 text-sm text-text-muted">{selectedApplicant.age} años · {selectedApplicant.school}</p><p className="mt-2 text-sm font-semibold text-brand-secondary">{selectedApplicant.career} · {selectedApplicant.university}</p></div>
                                    </div>
                                    <div className="rounded-2xl bg-brand-primary/5 border border-brand-border px-5 py-3 text-center"><p className="text-[10px] font-bold uppercase tracking-wider text-text-muted">Promedio general</p><p className="mt-1 text-3xl font-black text-text-main">{selectedApplicant.average}</p><p className="text-[10px] text-text-muted">sobre 100</p></div>
                                </div>
                                <div className="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(260px,0.7fr)]">
                                    <div>
                                        <div className="mb-4 flex items-center justify-between gap-4"><div><h4 className="text-sm font-bold text-text-main">Rendimiento por materia</h4><p className="mt-1 text-xs text-text-muted">Lectura preliminar del perfil seleccionado.</p></div><Badge variant="outline" className="border-brand-secondary/20 bg-brand-secondary/10 text-brand-secondary">Exigencia {selectedApplicant.requirement}</Badge></div>
                                        <div className="space-y-4">{selectedApplicant.scores.map((score, index) => { const tone = ['secondary', 'info', 'accent', 'secondary'][index]; return <div key={score.materia}><div className="mb-2 flex items-center justify-between gap-4 text-xs"><span className="font-semibold text-text-muted">{score.materia}</span><span className="font-bold text-text-main">{score.puntaje}%</span></div><Progress value={score.puntaje} className={`h-2 bg-brand-border/40 ${toneStyles[tone].progress}`} /></div>; })}</div>
                                    </div>
                                    <div className="space-y-4">
                                        <div className="rounded-xl border border-brand-success/20 bg-brand-success/5 p-4"><p className="flex items-center gap-2 text-xs font-bold text-brand-success"><CheckCircle2 className="h-4 w-4" />Fortalezas observadas</p><ul className="mt-3 space-y-2">{selectedApplicant.strengths.map((strength) => <li key={strength} className="flex items-start gap-2 text-xs leading-5 text-text-muted"><span className="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-success" />{strength}</li>)}</ul></div>
                                        <div className="rounded-xl border border-brand-danger/20 bg-brand-danger/5 p-4"><p className="flex items-center gap-2 text-xs font-bold text-brand-danger"><ShieldAlert className="h-4 w-4" />Brechas de aprendizaje</p><ul className="mt-3 space-y-2">{selectedApplicant.weaknesses.map((weakness) => <li key={weakness} className="flex items-start gap-2 text-xs leading-5 text-text-muted"><span className="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-danger" />{weakness}</li>)}</ul></div>
                                    </div>
                                </div>
                                <div className="mt-6 rounded-2xl border border-brand-secondary/20 bg-brand-primary/5 p-5"><p className="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-brand-secondary"><Lightbulb className="h-4 w-4" />Recomendación personalizada</p><p className="mt-2 text-sm leading-6 text-text-muted">{selectedApplicant.recommendation}</p></div>
                            </CardContent>
                        </Card>
                    </div>
                </section>

                <section>
                    <SectionHeading eyebrow="Apoyo a la coordinación" title="Interpretación docente" description="Conclusiones automáticas simuladas para ilustrar cómo el módulo podría orientar intervenciones académicas." icon={Lightbulb} />
                    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        {teacherInsights.map((insight) => { const Icon = insight.icon; return (
                            <Card key={insight.title} className="bg-brand-card border border-brand-border rounded-2xl p-5 sm:p-6 shadow-sm"><CardContent className="p-0"><span className={`flex h-11 w-11 items-center justify-center rounded-xl border border-brand-border ${toneStyles[insight.tone].icon}`}><Icon className="h-5 w-5" /></span><h3 className="mt-5 text-sm font-bold leading-snug text-text-main">{insight.title}</h3><p className="mt-3 text-xs leading-5 text-text-muted">{insight.interpretation}</p><div className="mt-4 border-t border-brand-border pt-4"><p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary">Recomendación</p><p className="mt-1.5 text-xs leading-5 text-text-muted">{insight.recommendation}</p></div></CardContent></Card>
                        ); })}
                    </div>
                </section>

                <section className="overflow-hidden rounded-3xl border border-brand-border bg-brand-card p-5 shadow-sm sm:p-8">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div className="max-w-4xl">
                            <p className="text-xs font-bold uppercase tracking-[0.16em] text-brand-secondary">
                                Arquitectura analítica
                            </p>
                            <h2 className="mt-2 text-2xl font-black leading-tight text-text-main sm:text-3xl">
                                Funcionamiento proyectado de Learning Analytics
                            </h2>
                            <p className="mt-3 text-sm leading-6 text-text-muted sm:text-base sm:leading-7">
                                Flujo conceptual que transforma datos de evaluaciones,
                                asistencia y comportamiento académico en perfiles de
                                riesgo, estimaciones de rendimiento y alertas para
                                tutores.
                            </p>
                        </div>
                        <Badge
                            variant="outline"
                            className="w-fit shrink-0 border-brand-border bg-brand-card px-3 py-1 text-text-muted shadow-sm"
                        >
                            Diseño conceptual
                        </Badge>
                    </div>

                    <div className="mt-8">
                        {analyticalLayers.map((layer, index) => {
                            const Icon = layer.icon;
                            const styles = roadmapToneStyles[layer.tone];

                            return (
                                <div key={layer.title}>
                                    <article className="relative min-w-0 overflow-hidden rounded-2xl border border-brand-border bg-brand-card p-5 shadow-sm sm:p-6">
                                        <span
                                            className={`absolute inset-y-0 left-0 w-1 ${styles.accent}`}
                                        />

                                        <div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                            <div className="flex min-w-0 items-start gap-4">
                                                <span
                                                    className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-brand-border ${styles.icon}`}
                                                >
                                                    <Icon className="h-6 w-6" />
                                                </span>
                                                <div className="min-w-0">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <span className="text-[10px] font-black uppercase tracking-[0.16em] text-text-muted">
                                                            Capa {index + 1}
                                                        </span>
                                                        <Badge
                                                            variant="outline"
                                                            className={roadmapStatusStyles[layer.status]}
                                                        >
                                                            {layer.status}
                                                        </Badge>
                                                    </div>
                                                    <h3 className="mt-2 break-words text-lg font-black leading-snug text-text-main sm:text-xl">
                                                        {layer.title}
                                                    </h3>
                                                    <p className="mt-1 text-xs font-semibold text-brand-secondary">
                                                        {layer.subtitle}
                                                    </p>
                                                </div>
                                            </div>
                                            <span className="shrink-0 text-4xl font-black tracking-tight text-brand-primary/10">
                                                {String(index + 1).padStart(2, '0')}
                                            </span>
                                        </div>

                                        <p className="mt-5 max-w-5xl break-words text-sm leading-6 text-text-muted">
                                            {layer.description}
                                        </p>

                                        {layer.modules && (
                                            <div className="mt-5 grid gap-4 md:grid-cols-2">
                                                {layer.modules.map((module) => (
                                                    <div
                                                        key={module.title}
                                                        className="rounded-xl border border-brand-border bg-brand-primary/5 p-4"
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <span
                                                                className={`h-2 w-2 shrink-0 rounded-full ${styles.bullet}`}
                                                            />
                                                            <h4 className="text-sm font-bold text-text-main">
                                                                {module.title}
                                                            </h4>
                                                        </div>
                                                        <p className="mt-2 text-xs leading-5 text-text-muted">
                                                            {module.description}
                                                        </p>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        <div
                                            className={`mt-5 grid gap-4 ${
                                                layer.beneficiaries
                                                    ? 'lg:grid-cols-2'
                                                    : ''
                                            }`}
                                        >
                                            <div className="rounded-xl border border-brand-border bg-brand-primary/[0.02] p-4">
                                                <p className="text-[10px] font-bold uppercase tracking-[0.14em] text-text-muted">
                                                    {layer.listTitle}
                                                </p>
                                                <ul className="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                                    {layer.items.map((item) => (
                                                        <li
                                                            key={item}
                                                            className="flex min-w-0 items-start gap-2 text-xs leading-5 text-text-main"
                                                        >
                                                            <span
                                                                className={`mt-2 h-1.5 w-1.5 shrink-0 rounded-full ${styles.bullet}`}
                                                            />
                                                            <span className="break-words">
                                                                {item}
                                                            </span>
                                                        </li>
                                                    ))}
                                                </ul>
                                            </div>

                                            {layer.beneficiaries && (
                                                <div className="rounded-xl border border-brand-secondary/20 bg-brand-secondary/5 p-4">
                                                    <p className="text-[10px] font-bold uppercase tracking-[0.14em] text-brand-secondary">
                                                        Usuarios beneficiados
                                                    </p>
                                                    <ul className="mt-3 grid gap-2 sm:grid-cols-2">
                                                        {layer.beneficiaries.map((user) => (
                                                            <li
                                                                key={user}
                                                                className="flex items-start gap-2 text-xs leading-5 text-text-main"
                                                            >
                                                                <span className="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-secondary" />
                                                                {user}
                                                            </li>
                                                        ))}
                                                    </ul>
                                                </div>
                                            )}
                                        </div>
                                    </article>

                                    <div className="flex flex-col items-center py-3">
                                        <span className="h-5 w-px bg-brand-border" />
                                        <span className="rounded-full border border-brand-border bg-brand-card px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-text-muted shadow-sm">
                                            {layerConnectors[index]}
                                        </span>
                                        {index < analyticalLayers.length - 1 && (
                                            <>
                                                <span className="h-5 w-px bg-brand-border" />
                                                <ArrowRight className="h-5 w-5 rotate-90 rounded-full bg-brand-secondary p-1 text-white" />
                                            </>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    <div className="mt-6 flex items-start gap-3 rounded-2xl border border-brand-warning/20 bg-brand-warning/10 p-4 sm:p-5">
                        <ShieldAlert className="mt-0.5 h-5 w-5 shrink-0 text-brand-warning" />
                        <div>
                            <p className="text-xs font-bold uppercase tracking-wider text-brand-warning">
                                Validación institucional requerida
                            </p>
                            <p className="mt-1.5 text-xs leading-5 text-text-muted sm:text-sm sm:leading-6">
                                Esta arquitectura representa el funcionamiento proyectado
                                del módulo Learning Analytics. Los modelos predictivos
                                deberán entrenarse y validarse con resultados históricos
                                reales antes de utilizarse como apoyo formal para
                                decisiones académicas.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}

