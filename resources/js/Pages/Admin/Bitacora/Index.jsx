import Pagination from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronUp,
    Download,
    Eye,
    Filter,
    ShieldCheck,
    X,
} from 'lucide-react';
import { useMemo, useState } from 'react';

const actionLabels = {
    crear: 'Creación de registro',
    editar: 'Edición de registro',
    actualizar: 'Actualización de registro',
    cambiar_estado: 'Cambio de estado',
    eliminar: 'Eliminación lógica',
    exportar_pdf: 'Exportación de reporte PDF',
    exportar_excel: 'Exportación de reporte Excel',
    exportar_bitacora: 'Exportación de bitácora',
    iniciar_evaluacion: 'Inicio de evaluación',
    finalizar_evaluacion: 'Finalización de evaluación',
    actualizar_permisos: 'Actualización de roles y permisos',
    crear_usuario: 'Creación de usuario',
    editar_usuario: 'Edición de usuario',
    actualizar_habilitacion: 'Actualización de habilitación académica',
    crear_matricula: 'Creación de matrícula',
    editar_matricula: 'Edición de matrícula',
    crear_cuota: 'Registro de cuota',
    editar_cuota: 'Actualización de cuota',
    login_exitoso: 'Inicio de sesión exitoso',
    logout: 'Cierre de sesión',
    login_fallido: 'Intento fallido de inicio de sesión',
    login_bloqueado: 'Bloqueo por intentos fallidos',
};

const moduleLabels = {
    'Reportes Académicos': 'Reportes Académicos',
    Postulantes: 'Gestión de Postulantes',
    Evaluaciones: 'Gestión Evaluativa',
    'Evaluaciones Aplicadas': 'Evaluaciones Aplicadas',
    Seguridad: 'Seguridad y Accesos',
    'Seguridad y Accesos': 'Seguridad y Accesos',
    Usuarios: 'Usuarios y Accesos',
    'Roles y Permisos': 'Roles y Permisos',
    'Matrículas y Cuotas': 'Matrículas y Cuotas',
    'Habilitación Académica': 'Habilitación Académica',
    Bitácora: 'Bitácora del Sistema',
    'Bitácora del Sistema': 'Bitácora del Sistema',
};

const routeLabels = {
    'reportes.pdf': 'Reportes Académicos / PDF',
    'reportes.excel': 'Reportes Académicos / Excel',
    'admin.sistema.bitacora.exportar': 'Bitácora / Exportación',
    'admin.sistema.roles-permisos.update': 'Roles y Permisos / Actualización',
    'estudiante.evaluaciones.iniciar': 'Portal Postulante / Inicio de evaluación',
    'estudiante.evaluaciones.enviar': 'Portal Postulante / Envío de evaluación',
    login: 'Autenticación / Inicio de sesión',
    logout: 'Autenticación / Cierre de sesión',
};

const severityLabels = {
    info: 'Informativo',
    aviso: 'Advertencia',
    critico: 'Crítico',
    seguridad: 'Seguridad',
};

const severidadClasses = {
    info: 'border-brand-info/20 bg-brand-info/10 text-brand-info',
    aviso: 'border-brand-warning/20 bg-brand-warning/10 text-brand-warning',
    critico: 'border-brand-danger/20 bg-brand-danger/10 text-brand-danger',
    seguridad: 'border-brand-accent/20 bg-brand-accent/10 text-brand-accent',
};

const reportLabels = {
    rendimiento: 'Rendimiento Académico',
    evaluaciones: 'Evaluaciones Aplicadas',
    asistencia: 'Asistencia Académica',
    habilitacion: 'Habilitación Académica',
};

const entityLabels = {
    postulantes: 'Postulante',
    users: 'Usuario del sistema',
    autenticacion: 'Proceso de autenticación',
    roles: 'Rol institucional',
    evaluaciones_aplicadas: 'Evaluación aplicada',
    matriculas_academicas: 'Matrícula académica',
    cuotas_academicas: 'Cuota académica',
    habilitaciones_academicas: 'Habilitación académica',
    bitacora_sistema: 'Bitácora del Sistema',
};

const fieldLabels = {
    tipo: 'Tipo de reporte',
    formato: 'Formato',
    filas: 'Registros procesados',
    eventos_exportados: 'Eventos exportados',
    id_post: 'Código de postulante',
    nombres_post: 'Nombres',
    apellidos_post: 'Apellidos',
    email_post: 'Correo electrónico',
    estado_post: 'Estado del postulante',
    estado_hab: 'Estado de habilitación',
    estado_mat: 'Estado de matrícula',
    estado_matricula_mat: 'Estado de matrícula',
    estado_cuota: 'Estado de cuota',
    puntaje_total_eval_apl: 'Puntaje total',
    puntaje_maximo_eval_apl: 'Puntaje máximo',
    porcentaje_eval_apl: 'Porcentaje',
    estado_eval_apl: 'Estado de evaluación',
    id_eval_apl: 'Código de evaluación aplicada',
    id_plantilla: 'Código de plantilla',
    id_col: 'Código de colegio',
    id_car: 'Código de carrera',
    permissions: 'Permisos asignados',
    roles: 'Roles asignados',
    filtros: 'Filtros aplicados',
    name: 'Nombre',
    email: 'Correo electrónico',
    role: 'Rol',
    correo: 'Correo electrónico',
    correo_intentado: 'Correo intentado',
    nombre: 'Nombre del usuario',
    rol: 'Rol',
    guard: 'Guard de autenticación',
    motivo: 'Motivo',
    ip: 'Dirección IP',
};

const sensitiveKeys = new Set([
    'password',
    'password_confirmation',
    'current_password',
    'token',
    'remember_token',
    'access_token',
    'refresh_token',
    'secret',
    'api_key',
    'private_key',
]);

const emptyFilters = {
    buscar: '',
    accion: '',
    modulo: '',
    severidad: '',
    fecha_desde: '',
    fecha_hasta: '',
};

const humanizeWords = (value) => {
    if (!value) return '';

    const words = String(value).replaceAll('_', ' ').replaceAll('-', ' ');

    return words.charAt(0).toUpperCase() + words.slice(1);
};

const formatearFecha = (value) => {
    if (!value) return 'Sin fecha registrada';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return 'Sin fecha registrada';

    return new Intl.DateTimeFormat('es-BO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone: 'America/La_Paz',
    }).format(date);
};

const formatearAccion = (accion) =>
    actionLabels[accion] || humanizeWords(accion) || 'Acción no registrada';

const formatearModulo = (modulo) =>
    moduleLabels[modulo] || humanizeWords(modulo) || 'Módulo general';

const formatearRuta = (ruta) =>
    routeLabels[ruta] || (ruta ? ruta.split('.').map(humanizeWords).join(' / ') : 'Sin ruta registrada');

const formatearEntidad = (entidad, entidadId) => {
    if (entidad === 'reporte' && reportLabels[entidadId]) {
        return `Reporte de ${reportLabels[entidadId]}`;
    }

    const label = entityLabels[entidad] || humanizeWords(entidad) || 'Sin entidad registrada';

    return entidadId ? `${label} #${entidadId}` : label;
};

const formatearSeveridad = (severidad) =>
    severityLabels[severidad] || humanizeWords(severidad) || 'Informativo';

const sanitizeTechnicalData = (value) => {
    if (Array.isArray(value)) return value.map(sanitizeTechnicalData);
    if (!value || typeof value !== 'object') return value;

    return Object.fromEntries(
        Object.entries(value).map(([key, item]) => [
            key,
            sensitiveKeys.has(key.toLowerCase())
                ? '[dato protegido]'
                : sanitizeTechnicalData(item),
        ]),
    );
};

const formatearValor = (key, value) => {
    if (value === null || value === undefined || value === '') return 'Sin registro';
    if (key === 'tipo' && reportLabels[value]) return reportLabels[value];
    if (key === 'formato') return String(value).toUpperCase();
    if (typeof value === 'boolean') return value ? 'Sí' : 'No';
    if (Array.isArray(value)) return value.map((item) => humanizeWords(item)).join(', ');

    return String(value);
};

function HumanizedJsonBlock({ title, value }) {
    const [showTechnical, setShowTechnical] = useState(false);
    const safeValue = sanitizeTechnicalData(value);
    const entries =
        safeValue && typeof safeValue === 'object' && !Array.isArray(safeValue)
            ? Object.entries(safeValue).filter(([key]) => !sensitiveKeys.has(key.toLowerCase()))
            : [];

    return (
        <section className="rounded-2xl border border-brand-border p-4">
            <h3 className="text-xs font-black uppercase tracking-wider text-text-muted">
                {title}
            </h3>

            {!safeValue || entries.length === 0 ? (
                <p className="mt-3 text-sm text-text-muted">
                    Sin información registrada.
                </p>
            ) : (
                <dl className="mt-3 divide-y divide-brand-border">
                    {entries.map(([key, item]) => (
                        <div
                            key={key}
                            className="grid gap-1 py-2.5 sm:grid-cols-[180px_1fr] sm:gap-4"
                        >
                            <dt className="text-sm font-bold text-text-main">
                                {fieldLabels[key] || humanizeWords(key)}
                            </dt>
                            <dd className="break-words text-sm text-text-muted">
                                {item && typeof item === 'object' && !Array.isArray(item)
                                    ? Object.entries(item)
                                          .map(([nestedKey, nestedValue]) => `${fieldLabels[nestedKey] || humanizeWords(nestedKey)}: ${formatearValor(nestedKey, nestedValue)}`)
                                          .join(' · ')
                                    : formatearValor(key, item)}
                            </dd>
                        </div>
                    ))}
                </dl>
            )}

            {safeValue && (
                <div className="mt-3 border-t border-brand-border pt-3">
                    <button
                        type="button"
                        onClick={() => setShowTechnical((current) => !current)}
                        className="inline-flex items-center text-xs font-bold text-brand-secondary"
                    >
                        {showTechnical ? (
                            <ChevronUp className="mr-1.5 h-4 w-4" />
                        ) : (
                            <ChevronDown className="mr-1.5 h-4 w-4" />
                        )}
                        {showTechnical ? 'Ocultar datos técnicos' : 'Ver datos técnicos'}
                    </button>
                    {showTechnical && (
                        <pre className="mt-3 max-h-72 overflow-auto rounded-xl bg-brand-bg p-3 text-xs leading-5 text-text-main">
                            {JSON.stringify(safeValue, null, 2)}
                        </pre>
                    )}
                </div>
            )}
        </section>
    );
}

export default function Index({
    eventos,
    filtros = {},
    resumen = {},
    opciones = {},
    permisos = {},
    estructuraDisponible = true,
}) {
    const [filters, setFilters] = useState({ ...emptyFilters, ...filtros });
    const [selectedEvent, setSelectedEvent] = useState(null);

    const exportUrl = useMemo(() => {
        const params = new URLSearchParams();

        Object.entries(filters).forEach(([key, value]) => {
            if (value) params.set(key, value);
        });

        const query = params.toString();

        return `${route('admin.sistema.bitacora.exportar')}${query ? `?${query}` : ''}`;
    }, [filters]);

    const submitFilters = (event) => {
        event.preventDefault();

        router.get(
            route('admin.sistema.bitacora.index'),
            Object.fromEntries(
                Object.entries(filters).filter(([, value]) => Boolean(value)),
            ),
            { preserveState: true, replace: true },
        );
    };

    const updateFilter = (key, value) => {
        setFilters((current) => ({ ...current, [key]: value }));
    };

    const cards = [
        { label: 'Total de eventos', value: resumen.total ?? 0 },
        { label: 'Eventos críticos', value: resumen.criticos ?? 0 },
        { label: 'Eventos de seguridad', value: resumen.seguridad ?? 0 },
        { label: 'Eventos de hoy', value: resumen.hoy ?? 0 },
    ];

    return (
        <AdminLayout
            title="Bitácora del Sistema"
            subtitle="Registro institucional de acciones críticas realizadas por usuarios autorizados."
            wide
        >
            <Head title="Bitácora del Sistema" />

            <div className="space-y-6">
                <section className="rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-6 text-white shadow-sm">
                    <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <Badge className="border border-white/20 bg-white/10 text-white hover:bg-white/15">
                                <ShieldCheck className="mr-1 h-3.5 w-3.5" />
                                Auditoría institucional
                            </Badge>
                            <h2 className="mt-4 text-2xl font-black">
                                Bitácora y logs institucionales
                            </h2>
                            <p className="mt-2 max-w-3xl text-sm leading-6 text-white/75">
                                Consulta eventos relevantes de seguridad, gestión académica,
                                evaluaciones, reportes y administración del sistema.
                            </p>
                        </div>
                        {permisos.exportar && (
                            <a
                                href={exportUrl}
                                className="inline-flex h-11 items-center justify-center rounded-xl bg-white px-4 text-sm font-bold text-brand-primary shadow-md transition hover:bg-white/90"
                            >
                                <Download className="mr-2 h-4 w-4" />
                                Exportar XLSX
                            </a>
                        )}
                    </div>
                </section>

                {!estructuraDisponible && (
                    <div className="rounded-2xl border border-brand-warning/20 bg-brand-warning/10 p-4 text-sm font-semibold text-brand-warning">
                        La tabla de bitácora aún no está migrada. Ejecuta las migraciones
                        pendientes para habilitar el registro institucional.
                    </div>
                )}

                <section className="grid gap-4 md:grid-cols-4">
                    {cards.map((card) => (
                        <Card key={card.label} className="border border-brand-border bg-brand-card p-5 shadow-sm">
                            <p className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                {card.label}
                            </p>
                            <p className="mt-3 text-3xl font-black text-text-main">
                                {card.value}
                            </p>
                        </Card>
                    ))}
                </section>

                <Card className="border border-brand-border bg-brand-card shadow-sm">
                    <CardContent className="p-5 sm:p-6">
                        <form onSubmit={submitFilters} className="grid gap-4 lg:grid-cols-6">
                            <label className="lg:col-span-2">
                                <span className="text-xs font-bold uppercase tracking-wider text-text-muted">Buscar</span>
                                <input
                                    type="text"
                                    value={filters.buscar}
                                    onChange={(event) => updateFilter('buscar', event.target.value)}
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    placeholder="Usuario, correo, descripción..."
                                />
                            </label>
                            <label>
                                <span className="text-xs font-bold uppercase tracking-wider text-text-muted">Acción</span>
                                <select
                                    value={filters.accion}
                                    onChange={(event) => updateFilter('accion', event.target.value)}
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                >
                                    <option value="">Todas</option>
                                    {(opciones.acciones || []).map((accion) => (
                                        <option key={accion} value={accion}>
                                            {formatearAccion(accion)}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label>
                                <span className="text-xs font-bold uppercase tracking-wider text-text-muted">Módulo</span>
                                <select
                                    value={filters.modulo}
                                    onChange={(event) => updateFilter('modulo', event.target.value)}
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                >
                                    <option value="">Todos</option>
                                    {(opciones.modulos || []).map((modulo) => (
                                        <option key={modulo} value={modulo}>
                                            {formatearModulo(modulo)}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label>
                                <span className="text-xs font-bold uppercase tracking-wider text-text-muted">Severidad</span>
                                <select
                                    value={filters.severidad}
                                    onChange={(event) => updateFilter('severidad', event.target.value)}
                                    className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                >
                                    <option value="">Todas</option>
                                    {(opciones.severidades || []).map((severidad) => (
                                        <option key={severidad} value={severidad}>
                                            {formatearSeveridad(severidad)}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <div className="grid gap-3 sm:grid-cols-2 lg:col-span-6">
                                <label>
                                    <span className="text-xs font-bold uppercase tracking-wider text-text-muted">Desde</span>
                                    <input
                                        type="date"
                                        value={filters.fecha_desde}
                                        onChange={(event) => updateFilter('fecha_desde', event.target.value)}
                                        className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    />
                                </label>
                                <label>
                                    <span className="text-xs font-bold uppercase tracking-wider text-text-muted">Hasta</span>
                                    <input
                                        type="date"
                                        value={filters.fecha_hasta}
                                        onChange={(event) => updateFilter('fecha_hasta', event.target.value)}
                                        className="mt-1 h-11 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main focus:border-brand-secondary focus:outline-none focus:ring-2 focus:ring-brand-secondary/20"
                                    />
                                </label>
                            </div>
                            <div className="lg:col-span-6">
                                <button
                                    type="submit"
                                    className="inline-flex h-11 items-center justify-center rounded-xl bg-brand-secondary px-4 text-sm font-bold text-white shadow-md transition-all hover:bg-brand-secondary/90 active:scale-95"
                                >
                                    <Filter className="mr-2 h-4 w-4" />
                                    Aplicar filtros
                                </button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card className="border border-brand-border bg-brand-card shadow-sm">
                    <CardContent className="p-0">
                        <div className="hidden overflow-hidden rounded-2xl lg:block">
                            <table className="w-full table-fixed">
                                <thead className="bg-brand-bg">
                                    <tr className="text-left text-xs font-black uppercase tracking-wider text-text-muted">
                                        <th className="w-[13%] px-4 py-3">Fecha</th>
                                        <th className="w-[18%] px-4 py-3">Usuario</th>
                                        <th className="w-[10%] px-4 py-3">Rol</th>
                                        <th className="w-[14%] px-4 py-3">Acción</th>
                                        <th className="w-[12%] px-4 py-3">Módulo</th>
                                        <th className="w-[15%] px-4 py-3">Entidad</th>
                                        <th className="w-[10%] px-4 py-3">Severidad</th>
                                        <th className="w-[8%] px-4 py-3 text-right">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {eventos.data.map((evento) => (
                                        <tr key={evento.id_bitacora} className="border-t border-brand-border text-sm">
                                            <td className="whitespace-nowrap px-4 py-3 text-xs text-text-muted">
                                                {formatearFecha(evento.created_at)}
                                            </td>
                                            <td className="whitespace-normal break-words px-4 py-3">
                                                <p className="text-sm font-bold text-text-main">{evento.nombre_usuario || 'Sistema'}</p>
                                                <p className="break-all text-xs text-text-muted">{evento.correo_usuario || 'Sin correo'}</p>
                                            </td>
                                            <td className="whitespace-normal break-words px-4 py-3 text-text-muted">{evento.rol_usuario || 'Sin rol'}</td>
                                            <td className="whitespace-normal break-words px-4 py-3 font-semibold leading-snug text-text-main">{formatearAccion(evento.accion)}</td>
                                            <td className="whitespace-normal break-words px-4 py-3 text-text-muted">{formatearModulo(evento.modulo)}</td>
                                            <td className="whitespace-normal break-words px-4 py-3 text-text-muted">{formatearEntidad(evento.entidad, evento.entidad_id)}</td>
                                            <td className="px-4 py-3">
                                                <span className={`inline-flex rounded-full border px-2.5 py-1 text-xs font-bold ${severidadClasses[evento.severidad] || severidadClasses.info}`}>
                                                    {formatearSeveridad(evento.severidad)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => setSelectedEvent(evento)}
                                                    aria-label="Ver detalle del evento"
                                                    className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-brand-border text-text-muted transition hover:bg-brand-border/30 hover:text-brand-secondary"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="grid gap-3 p-4 lg:hidden">
                            {eventos.data.map((evento) => (
                                <article key={evento.id_bitacora} className="rounded-2xl border border-brand-border p-4">
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="text-sm font-black text-text-main">{formatearAccion(evento.accion)}</p>
                                            <p className="text-xs text-text-muted">{formatearFecha(evento.created_at)}</p>
                                        </div>
                                        <span className={`inline-flex rounded-full border px-2.5 py-1 text-xs font-bold ${severidadClasses[evento.severidad] || severidadClasses.info}`}>
                                            {formatearSeveridad(evento.severidad)}
                                        </span>
                                    </div>
                                    <p className="mt-3 text-sm text-text-muted">{evento.descripcion || 'Sin descripción registrada.'}</p>
                                    <p className="mt-2 text-xs font-semibold text-text-muted">{formatearEntidad(evento.entidad, evento.entidad_id)}</p>
                                    <button
                                        type="button"
                                        onClick={() => setSelectedEvent(evento)}
                                        className="mt-4 inline-flex h-9 items-center justify-center rounded-xl border border-brand-border px-3 text-xs font-bold text-text-main transition hover:bg-brand-border/30"
                                    >
                                        <Eye className="mr-2 h-4 w-4" />
                                        Ver detalle
                                    </button>
                                </article>
                            ))}
                        </div>

                        {eventos.data.length === 0 && (
                            <div className="p-10 text-center text-sm font-semibold text-text-muted">
                                No existen eventos de bitácora con los filtros aplicados.
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Pagination links={eventos.links} />
            </div>

            {selectedEvent && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
                    <div className="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-2xl border border-brand-border bg-brand-card p-5 shadow-2xl sm:p-6">
                        <div className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2 className="text-lg font-black text-text-main">Detalle de evento</h2>
                                <p className="mt-1 text-sm text-text-muted">
                                    {selectedEvent.descripcion || 'Sin descripción registrada.'}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setSelectedEvent(null)}
                                className="rounded-xl border border-brand-border p-2 text-text-muted transition hover:bg-brand-border/30 hover:text-text-main"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        <section className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            {[
                                ['Fecha', formatearFecha(selectedEvent.created_at)],
                                ['Usuario', selectedEvent.nombre_usuario || 'Sistema'],
                                ['Rol', selectedEvent.rol_usuario || 'Sin rol'],
                                ['Severidad', formatearSeveridad(selectedEvent.severidad)],
                                ['Módulo', formatearModulo(selectedEvent.modulo)],
                                ['Acción', formatearAccion(selectedEvent.accion)],
                                ['Entidad', formatearEntidad(selectedEvent.entidad, selectedEvent.entidad_id)],
                                ['IP', selectedEvent.ip || 'Sin IP registrada'],
                            ].map(([label, value]) => (
                                <div key={label} className="rounded-2xl border border-brand-border p-4">
                                    <p className="text-xs font-bold uppercase tracking-wider text-text-muted">{label}</p>
                                    <p className="mt-2 break-words text-sm font-semibold leading-snug text-text-main">{value}</p>
                                </div>
                            ))}
                        </section>

                        <section className="mb-4 rounded-2xl border border-brand-border p-4">
                            <h3 className="text-xs font-black uppercase tracking-wider text-text-muted">Contexto de la solicitud</h3>
                            <dl className="mt-3 grid gap-3 text-sm md:grid-cols-2">
                                <div><dt className="font-bold text-text-main">Ruta</dt><dd className="break-words text-text-muted">{formatearRuta(selectedEvent.ruta)}</dd></div>
                                <div><dt className="font-bold text-text-main">Método HTTP</dt><dd className="text-text-muted">{selectedEvent.metodo_http || 'Sin método registrado'}</dd></div>
                                <div className="md:col-span-2"><dt className="font-bold text-text-main">URL</dt><dd className="break-all text-text-muted">{selectedEvent.url || 'Sin URL registrada'}</dd></div>
                                <div className="md:col-span-2"><dt className="font-bold text-text-main">Agente de usuario</dt><dd className="mt-1 max-h-24 overflow-auto break-words rounded-xl bg-brand-bg p-3 text-xs leading-5 text-text-muted">{selectedEvent.user_agent || 'Sin registro'}</dd></div>
                            </dl>
                        </section>

                        <div className="grid gap-4 md:grid-cols-2">
                            <HumanizedJsonBlock
                                title="Valores anteriores"
                                value={selectedEvent.valores_anteriores}
                            />
                            <HumanizedJsonBlock
                                title="Valores nuevos"
                                value={selectedEvent.valores_nuevos}
                            />
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
