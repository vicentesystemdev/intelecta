import {
    EmptyInstitutional,
    Field,
    FlashMessage,
    InstitutionalBanner,
    InstitutionalStatus,
    MetricTile,
    SelectField,
    TextareaField,
    cardClass,
    primaryButtonClass,
    secondaryButtonClass,
} from '@/Components/Institucional/InstitutionalUi';
import ModalInstitucional from '@/Components/ModalInstitucional';
import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    BadgeDollarSign,
    Banknote,
    CalendarClock,
    CircleDollarSign,
    Gift,
    Pencil,
    Plus,
    ReceiptText,
    Search,
    ShieldAlert,
    WalletCards,
} from 'lucide-react';
import { useMemo, useState } from 'react';

const emptyMatricula = {
    id_insc: '',
    codigo_mat: '',
    fecha_matricula_mat: '',
    monto_matricula_mat: 0,
    estado_matricula_mat: 'activa',
    tipo_beneficio_mat: '',
    observacion_mat: '',
};

const emptyCuota = {
    id_mat: '',
    nro_cuota: '',
    concepto_cuota: '',
    monto_cuota: 0,
    fecha_vencimiento_cuota: '',
    fecha_pago_cuota: '',
    metodo_pago_cuota: '',
    estado_cuota: 'pendiente',
    observacion_cuota: '',
};

const formatMoney = (value) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    }).format(Number(value || 0));

const formatDate = (value) =>
    value
        ? new Date(`${String(value).slice(0, 10)}T00:00:00`).toLocaleDateString('es-BO')
        : 'Sin fecha';

const studentName = (postulante) =>
    `${postulante?.nombres_post || ''} ${postulante?.apellidos_post || ''}`.trim();

const localToday = () => {
    const date = new Date();
    return new Date(date.getTime() - date.getTimezoneOffset() * 60000)
        .toISOString()
        .slice(0, 10);
};

export default function Index({
    matriculas,
    metricas = {},
    inscripciones = [],
    programas = [],
    grupos = [],
    filtros = {},
}) {
    const { flash } = usePage().props;
    const [filters, setFilters] = useState({
        buscar: filtros.buscar || '',
        id_prog: filtros.id_prog || '',
        id_grupo: filtros.id_grupo || '',
        estado_matricula_mat: filtros.estado_matricula_mat || '',
        estado_cuota: filtros.estado_cuota || '',
    });
    const [matriculaModal, setMatriculaModal] = useState({
        open: false,
        matricula: null,
    });
    const [cuotaModal, setCuotaModal] = useState({
        open: false,
        cuota: null,
        matricula: null,
    });
    const matriculaForm = useForm(emptyMatricula);
    const cuotaForm = useForm(emptyCuota);

    const filteredGroups = useMemo(
        () =>
            filters.id_prog
                ? grupos.filter(
                      (grupo) => String(grupo.id_prog) === String(filters.id_prog),
                  )
                : grupos,
        [filters.id_prog, grupos],
    );

    const openMatricula = (matricula = null) => {
        matriculaForm.clearErrors();
        matriculaForm.setData(
            matricula
                ? {
                      ...emptyMatricula,
                      ...matricula,
                      id_insc: String(matricula.id_insc),
                      fecha_matricula_mat:
                          matricula.fecha_matricula_mat?.slice(0, 10) || '',
                  }
                : emptyMatricula,
        );
        setMatriculaModal({ open: true, matricula });
    };

    const openCuota = (matricula, cuota = null) => {
        cuotaForm.clearErrors();
        cuotaForm.setData(
            cuota
                ? {
                      ...emptyCuota,
                      ...cuota,
                      id_mat: String(cuota.id_mat),
                      fecha_vencimiento_cuota:
                          cuota.fecha_vencimiento_cuota?.slice(0, 10) || '',
                      fecha_pago_cuota: cuota.fecha_pago_cuota?.slice(0, 10) || '',
                  }
                : { ...emptyCuota, id_mat: String(matricula.id_mat) },
        );
        setCuotaModal({ open: true, cuota, matricula });
    };

    const submitMatricula = (event) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => setMatriculaModal({ open: false, matricula: null }),
        };

        matriculaModal.matricula
            ? matriculaForm.patch(
                  route(
                      'admin.institucional.matriculas-cuotas.update',
                      matriculaModal.matricula.id_mat,
                  ),
                  options,
              )
            : matriculaForm.post(
                  route('admin.institucional.matriculas-cuotas.store'),
                  options,
              );
    };

    const submitCuota = (event) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () =>
                setCuotaModal({ open: false, cuota: null, matricula: null }),
        };

        cuotaModal.cuota
            ? cuotaForm.patch(
                  route('admin.institucional.cuotas.update', cuotaModal.cuota.id_cuota),
                  options,
              )
            : cuotaForm.post(route('admin.institucional.cuotas.store'), options);
    };

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.institucional.matriculas-cuotas.index'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const summary = [
        ['Matrículas activas', metricas.matriculasActivas ?? 0, WalletCards, 'success'],
        ['Matrículas observadas', metricas.matriculasObservadas ?? 0, ShieldAlert, 'warning'],
        ['Cuotas pendientes', metricas.cuotasPendientes ?? 0, CalendarClock, 'warning'],
        ['Cuotas vencidas', metricas.cuotasVencidas ?? 0, CircleDollarSign, 'danger'],
        ['Becados / exentos', metricas.beneficiados ?? 0, Gift, 'accent'],
    ];

    return (
        <AdminLayout
            title="Matrículas y Cuotas"
            subtitle="Control administrativo interno de inscripciones, obligaciones y beneficios académicos."
            wide
        >
            <Head title="Matrículas y Cuotas" />
            <InstitutionalBanner
                eyebrow="Gestión administrativa académica"
                title="Matrículas y Cuotas"
                description="Registre el estado administrativo de cada inscripción sin incorporar facturación electrónica ni procesamiento bancario."
                icon={WalletCards}
                action={
                    <button className={primaryButtonClass} onClick={() => openMatricula()}>
                        <Plus className="h-4 w-4" />
                        Nueva matrícula
                    </button>
                }
            />
            <FlashMessage message={flash?.success} />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                {summary.map(([label, value, icon, tone]) => (
                    <MetricTile
                        key={label}
                        label={label}
                        value={value}
                        icon={icon}
                        tone={tone}
                    />
                ))}
            </div>

            <form
                onSubmit={applyFilters}
                className={`${cardClass} mb-5 grid gap-3 p-4 md:grid-cols-3 xl:grid-cols-6`}
            >
                <label className="relative md:col-span-2 xl:col-span-1">
                    <Search className="pointer-events-none absolute left-3 top-3 h-4 w-4 text-text-muted" />
                    <input
                        className="h-10 w-full rounded-xl border border-brand-border bg-brand-card pl-9 pr-3 text-sm text-text-main"
                        placeholder="Postulante o código"
                        value={filters.buscar}
                        onChange={(event) =>
                            setFilters({ ...filters, buscar: event.target.value })
                        }
                    />
                </label>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.id_prog}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            id_prog: event.target.value,
                            id_grupo: '',
                        })
                    }
                >
                    <option value="">Todos los programas</option>
                    {programas.map((programa) => (
                        <option key={programa.id_prog} value={programa.id_prog}>
                            {programa.nombre_prog}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.id_grupo}
                    onChange={(event) =>
                        setFilters({ ...filters, id_grupo: event.target.value })
                    }
                >
                    <option value="">Todos los grupos</option>
                    {filteredGroups.map((grupo) => (
                        <option key={grupo.id_grupo} value={grupo.id_grupo}>
                            {grupo.nombre_grupo}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.estado_matricula_mat}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            estado_matricula_mat: event.target.value,
                        })
                    }
                >
                    <option value="">Estado de matrícula</option>
                    {['activa', 'observada', 'inactiva', 'becada', 'exenta'].map(
                        (estado) => (
                            <option key={estado} value={estado}>
                                {estado}
                            </option>
                        ),
                    )}
                </select>
                <select
                    className="rounded-xl border-brand-border bg-brand-card text-sm text-text-main"
                    value={filters.estado_cuota}
                    onChange={(event) =>
                        setFilters({ ...filters, estado_cuota: event.target.value })
                    }
                >
                    <option value="">Estado de cuota</option>
                    {['pendiente', 'pagada', 'vencida', 'becada', 'exenta'].map(
                        (estado) => (
                            <option key={estado} value={estado}>
                                {estado}
                            </option>
                        ),
                    )}
                </select>
                <button className={primaryButtonClass}>Filtrar</button>
            </form>

            {matriculas.data.length ? (
                <div className="space-y-5">
                    {matriculas.data.map((matricula) => (
                        <article
                            key={matricula.id_mat}
                            className={`${cardClass} overflow-hidden`}
                        >
                            <div className="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(300px,0.8fr)]">
                                <div>
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p className="text-[10px] font-bold uppercase tracking-wider text-brand-secondary">
                                                {matricula.codigo_mat}
                                            </p>
                                            <h2 className="mt-2 text-lg font-black text-text-main">
                                                {studentName(matricula.postulante)}
                                            </h2>
                                            <p className="mt-1 text-xs text-text-muted">
                                                {matricula.programa?.nombre_prog} ·{' '}
                                                {matricula.grupo?.nombre_grupo ||
                                                    'Sin grupo asignado'}
                                            </p>
                                        </div>
                                        <InstitutionalStatus
                                            status={matricula.estado_matricula_mat}
                                        />
                                    </div>
                                    <div className="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                        {[
                                            ['Cuotas', matricula.cuotas_count],
                                            ['Pagadas', matricula.cuotas_pagadas_count],
                                            ['Pendientes', matricula.cuotas_pendientes_count],
                                            ['Vencidas', matricula.cuotas_vencidas_count],
                                        ].map(([label, value]) => (
                                            <div
                                                key={label}
                                                className="rounded-xl bg-brand-bg p-3"
                                            >
                                                <p className="text-xs text-text-muted">
                                                    {label}
                                                </p>
                                                <p className="mt-1 text-lg font-black text-text-main">
                                                    {value || 0}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                <div className="rounded-2xl border border-brand-border bg-brand-bg p-5">
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="text-xs font-bold uppercase tracking-wider text-text-muted">
                                                Saldo pendiente referencial
                                            </p>
                                            <p className="mt-2 text-2xl font-black text-text-main">
                                                {formatMoney(matricula.saldo_pendiente)}
                                            </p>
                                        </div>
                                        <Banknote className="h-6 w-6 text-brand-secondary" />
                                    </div>
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        <InstitutionalStatus
                                            status={
                                                matricula.habilitacion?.estado_hab ||
                                                'Sin habilitación'
                                            }
                                        />
                                        {matricula.tipo_beneficio_mat && (
                                            <span className="rounded-lg bg-brand-accent/15 px-2.5 py-1 text-xs font-bold text-brand-primary dark:text-brand-accent">
                                                {matricula.tipo_beneficio_mat}
                                            </span>
                                        )}
                                    </div>
                                    <div className="mt-5 flex flex-wrap gap-2">
                                        <button
                                            className={secondaryButtonClass}
                                            onClick={() => openMatricula(matricula)}
                                        >
                                            <Pencil className="h-4 w-4" />
                                            Editar matrícula
                                        </button>
                                        <button
                                            className={primaryButtonClass}
                                            onClick={() => openCuota(matricula)}
                                        >
                                            <Plus className="h-4 w-4" />
                                            Registrar cuota
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div className="border-t border-brand-border bg-brand-bg/50 p-5 sm:p-6">
                                <div className="mb-4 flex items-center gap-2">
                                    <ReceiptText className="h-4 w-4 text-brand-secondary" />
                                    <h3 className="text-sm font-black text-text-main">
                                        Cuotas académicas
                                    </h3>
                                </div>
                                {matricula.cuotas.length ? (
                                    <div className="grid gap-3 lg:grid-cols-2">
                                        {matricula.cuotas.map((cuota) => (
                                            <div
                                                key={cuota.id_cuota}
                                                className="flex flex-col gap-3 rounded-2xl border border-brand-border bg-brand-card p-4 sm:flex-row sm:items-center sm:justify-between"
                                            >
                                                <div className="min-w-0">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <p className="font-bold text-text-main">
                                                            {cuota.concepto_cuota ||
                                                                `Cuota ${cuota.nro_cuota || ''}`}
                                                        </p>
                                                        <InstitutionalStatus
                                                            status={cuota.estado_cuota}
                                                        />
                                                    </div>
                                                    <p className="mt-1 text-xs text-text-muted">
                                                        Vence: {formatDate(
                                                            cuota.fecha_vencimiento_cuota,
                                                        )}
                                                        {cuota.fecha_pago_cuota
                                                            ? ` · Pagada: ${formatDate(cuota.fecha_pago_cuota)}`
                                                            : ''}
                                                    </p>
                                                </div>
                                                <div className="flex items-center justify-between gap-3 sm:justify-end">
                                                    <span className="font-black text-text-main">
                                                        {formatMoney(cuota.monto_cuota)}
                                                    </span>
                                                    <button
                                                        className="rounded-lg p-2 text-text-muted hover:bg-brand-border/30"
                                                        onClick={() =>
                                                            openCuota(matricula, cuota)
                                                        }
                                                        aria-label="Editar cuota"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="rounded-xl border border-dashed border-brand-border p-4 text-center text-xs text-text-muted">
                                        Esta matrícula aún no tiene cuotas registradas.
                                    </p>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            ) : (
                <EmptyInstitutional
                    title="No hay matrículas para los filtros seleccionados"
                    description="Registre una matrícula desde una inscripción académica existente."
                />
            )}
            <Pagination links={matriculas.links} />

            <ModalInstitucional
                open={matriculaModal.open}
                onOpenChange={(open) =>
                    setMatriculaModal((current) => ({ ...current, open }))
                }
                title={
                    matriculaModal.matricula
                        ? 'Editar matrícula académica'
                        : 'Nueva matrícula académica'
                }
                description="La información del postulante, programa y grupo se obtiene automáticamente desde la inscripción."
                size="lg"
            >
                <form onSubmit={submitMatricula} className="grid gap-4 sm:grid-cols-2">
                    <SelectField
                        label="Inscripción académica"
                        value={matriculaForm.data.id_insc}
                        onChange={(event) =>
                            matriculaForm.setData('id_insc', event.target.value)
                        }
                        error={matriculaForm.errors.id_insc}
                        disabled={Boolean(matriculaModal.matricula)}
                        className="sm:col-span-2"
                    >
                        <option value="">Seleccione una inscripción</option>
                        {inscripciones.map((inscripcion) => (
                            <option key={inscripcion.id_insc} value={inscripcion.id_insc}>
                                {studentName(inscripcion.postulante)} ·{' '}
                                {inscripcion.programa?.nombre_prog}
                                {inscripcion.grupo
                                    ? ` · ${inscripcion.grupo.nombre_grupo}`
                                    : ''}
                            </option>
                        ))}
                    </SelectField>
                    <Field
                        label="Código de matrícula"
                        placeholder="Se genera automáticamente"
                        value={matriculaForm.data.codigo_mat}
                        onChange={(event) =>
                            matriculaForm.setData('codigo_mat', event.target.value)
                        }
                        error={matriculaForm.errors.codigo_mat}
                    />
                    <Field
                        type="date"
                        label="Fecha de matrícula"
                        max={localToday()}
                        value={matriculaForm.data.fecha_matricula_mat}
                        onChange={(event) =>
                            matriculaForm.setData(
                                'fecha_matricula_mat',
                                event.target.value,
                            )
                        }
                        error={matriculaForm.errors.fecha_matricula_mat}
                    />
                    <Field
                        type="number"
                        min="0"
                        step="0.01"
                        label="Monto de matrícula"
                        value={matriculaForm.data.monto_matricula_mat}
                        onChange={(event) =>
                            matriculaForm.setData(
                                'monto_matricula_mat',
                                event.target.value,
                            )
                        }
                        error={matriculaForm.errors.monto_matricula_mat}
                    />
                    <SelectField
                        label="Estado"
                        value={matriculaForm.data.estado_matricula_mat}
                        onChange={(event) =>
                            matriculaForm.setData(
                                'estado_matricula_mat',
                                event.target.value,
                            )
                        }
                        error={matriculaForm.errors.estado_matricula_mat}
                    >
                        {['activa', 'observada', 'inactiva', 'becada', 'exenta'].map(
                            (estado) => (
                                <option key={estado} value={estado}>
                                    {estado}
                                </option>
                            ),
                        )}
                    </SelectField>
                    <Field
                        label="Tipo de beneficio"
                        placeholder="Beca, convenio o exención"
                        value={matriculaForm.data.tipo_beneficio_mat}
                        onChange={(event) =>
                            matriculaForm.setData(
                                'tipo_beneficio_mat',
                                event.target.value,
                            )
                        }
                        error={matriculaForm.errors.tipo_beneficio_mat}
                        className="sm:col-span-2"
                    />
                    <TextareaField
                        label="Observación administrativa"
                        value={matriculaForm.data.observacion_mat}
                        onChange={(event) =>
                            matriculaForm.setData('observacion_mat', event.target.value)
                        }
                        error={matriculaForm.errors.observacion_mat}
                        className="sm:col-span-2"
                    />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button
                            type="button"
                            className={secondaryButtonClass}
                            onClick={() =>
                                setMatriculaModal({ open: false, matricula: null })
                            }
                        >
                            Cancelar
                        </button>
                        <button
                            className={primaryButtonClass}
                            disabled={matriculaForm.processing}
                        >
                            <WalletCards className="h-4 w-4" />
                            Guardar matrícula
                        </button>
                    </div>
                </form>
            </ModalInstitucional>

            <ModalInstitucional
                open={cuotaModal.open}
                onOpenChange={(open) =>
                    setCuotaModal((current) => ({ ...current, open }))
                }
                title={cuotaModal.cuota ? 'Editar cuota académica' : 'Registrar cuota'}
                description={
                    cuotaModal.matricula
                        ? `${studentName(cuotaModal.matricula.postulante)} · ${cuotaModal.matricula.codigo_mat}`
                        : 'Control administrativo interno'
                }
                size="lg"
            >
                <form onSubmit={submitCuota} className="grid gap-4 sm:grid-cols-2">
                    <Field
                        type="number"
                        min="1"
                        label="Número de cuota"
                        value={cuotaForm.data.nro_cuota}
                        onChange={(event) =>
                            cuotaForm.setData('nro_cuota', event.target.value)
                        }
                        error={cuotaForm.errors.nro_cuota}
                    />
                    <Field
                        label="Concepto"
                        value={cuotaForm.data.concepto_cuota}
                        onChange={(event) =>
                            cuotaForm.setData('concepto_cuota', event.target.value)
                        }
                        error={cuotaForm.errors.concepto_cuota}
                    />
                    <Field
                        type="number"
                        min="0"
                        step="0.01"
                        label="Monto"
                        value={cuotaForm.data.monto_cuota}
                        onChange={(event) =>
                            cuotaForm.setData('monto_cuota', event.target.value)
                        }
                        error={cuotaForm.errors.monto_cuota}
                    />
                    <SelectField
                        label="Estado"
                        value={cuotaForm.data.estado_cuota}
                        onChange={(event) =>
                            cuotaForm.setData('estado_cuota', event.target.value)
                        }
                        error={cuotaForm.errors.estado_cuota}
                    >
                        {['pendiente', 'pagada', 'vencida', 'becada', 'exenta'].map(
                            (estado) => (
                                <option key={estado} value={estado}>
                                    {estado}
                                </option>
                            ),
                        )}
                    </SelectField>
                    <Field
                        type="date"
                        label="Fecha de vencimiento"
                        value={cuotaForm.data.fecha_vencimiento_cuota}
                        onChange={(event) =>
                            cuotaForm.setData(
                                'fecha_vencimiento_cuota',
                                event.target.value,
                            )
                        }
                        error={cuotaForm.errors.fecha_vencimiento_cuota}
                    />
                    <Field
                        type="date"
                        label="Fecha de pago"
                        max={localToday()}
                        value={cuotaForm.data.fecha_pago_cuota}
                        onChange={(event) =>
                            cuotaForm.setData('fecha_pago_cuota', event.target.value)
                        }
                        error={cuotaForm.errors.fecha_pago_cuota}
                    />
                    <Field
                        label="Método referencial"
                        placeholder="Efectivo, transferencia u otro"
                        value={cuotaForm.data.metodo_pago_cuota}
                        onChange={(event) =>
                            cuotaForm.setData(
                                'metodo_pago_cuota',
                                event.target.value,
                            )
                        }
                        error={cuotaForm.errors.metodo_pago_cuota}
                        className="sm:col-span-2"
                    />
                    <TextareaField
                        label="Observación"
                        value={cuotaForm.data.observacion_cuota}
                        onChange={(event) =>
                            cuotaForm.setData(
                                'observacion_cuota',
                                event.target.value,
                            )
                        }
                        error={cuotaForm.errors.observacion_cuota}
                        className="sm:col-span-2"
                    />
                    <div className="flex justify-end gap-3 border-t border-brand-border pt-4 sm:col-span-2">
                        <button
                            type="button"
                            className={secondaryButtonClass}
                            onClick={() =>
                                setCuotaModal({
                                    open: false,
                                    cuota: null,
                                    matricula: null,
                                })
                            }
                        >
                            Cancelar
                        </button>
                        <button
                            className={primaryButtonClass}
                            disabled={cuotaForm.processing}
                        >
                            <BadgeDollarSign className="h-4 w-4" />
                            Guardar cuota
                        </button>
                    </div>
                </form>
            </ModalInstitucional>
        </AdminLayout>
    );
}
