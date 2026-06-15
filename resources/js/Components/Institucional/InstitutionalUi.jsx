import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import { CheckCircle2, Info, Layers3 } from 'lucide-react';

export const cardClass =
    'rounded-2xl border border-brand-border bg-brand-card shadow-sm dark:shadow-black/20';

export const primaryButtonClass =
    'inline-flex items-center justify-center gap-2 rounded-xl bg-brand-secondary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-secondary/90 disabled:cursor-not-allowed disabled:opacity-60';

export const secondaryButtonClass =
    'inline-flex items-center justify-center gap-2 rounded-xl border border-brand-border bg-transparent px-4 py-2.5 text-sm font-bold text-text-main transition hover:bg-brand-border/30';

export const inputClass =
    'h-10 w-full rounded-xl border border-brand-border bg-brand-card px-3 text-sm text-text-main outline-none transition focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary/15';

const statusStyles = {
    activo: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    activa: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    aplicado: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    finalizada: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    aprobado: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    habilitado: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    pagada: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    'al día': 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    becada: 'border-brand-accent/35 bg-brand-accent/10 text-brand-primary dark:text-brand-accent',
    becado: 'border-brand-accent/35 bg-brand-accent/10 text-brand-primary dark:text-brand-accent',
    exenta: 'border-brand-accent/35 bg-brand-accent/10 text-brand-primary dark:text-brand-accent',
    exento: 'border-brand-accent/35 bg-brand-accent/10 text-brand-primary dark:text-brand-accent',
    inactivo: 'border-brand-border bg-brand-border/30 text-text-muted',
    inactiva: 'border-brand-border bg-brand-border/30 text-text-muted',
    programado: 'border-brand-info/25 bg-brand-info/10 text-brand-info',
    'en preparación':
        'border-brand-warning/25 bg-brand-warning/10 text-brand-warning',
    cerrado: 'border-brand-border bg-brand-border/30 text-text-muted',
    pendiente: 'border-brand-warning/30 bg-brand-warning/10 text-brand-warning',
    en_progreso: 'border-brand-warning/30 bg-brand-warning/10 text-brand-warning',
    observada: 'border-brand-warning/30 bg-brand-warning/10 text-brand-warning',
    observado: 'border-brand-warning/30 bg-brand-warning/10 text-brand-warning',
    temporal: 'border-brand-info/25 bg-brand-info/10 text-brand-info',
    vencida: 'border-brand-danger/25 bg-brand-danger/10 text-brand-danger',
    vencido: 'border-brand-danger/25 bg-brand-danger/10 text-brand-danger',
    restringido: 'border-brand-danger/25 bg-brand-danger/10 text-brand-danger',
    presente: 'border-brand-success/25 bg-brand-success/10 text-brand-success',
    retraso: 'border-brand-warning/30 bg-brand-warning/10 text-brand-warning',
    justificado: 'border-brand-info/25 bg-brand-info/10 text-brand-info',
    ausente: 'border-brand-danger/25 bg-brand-danger/10 text-brand-danger',
    anulada: 'border-brand-danger/25 bg-brand-danger/10 text-brand-danger',
    'Alto rendimiento':
        'border-brand-accent/35 bg-brand-accent/10 text-brand-primary dark:text-brand-accent',
    'Seguimiento regular':
        'border-brand-info/25 bg-brand-info/10 text-brand-info',
    'Atención prioritaria':
        'border-brand-warning/30 bg-brand-warning/10 text-brand-warning',
};

export function InstitutionalStatus({ status }) {
    return (
        <Badge
            variant="outline"
            className={`whitespace-nowrap ${statusStyles[status] || statusStyles.inactivo}`}
        >
            {status ? String(status).replaceAll('_', ' ') : 'Sin estado'}
        </Badge>
    );
}

export function InstitutionalBanner({ eyebrow, title, description, icon: Icon = Layers3, action }) {
    return (
        <section className="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-primary via-brand-primary to-brand-secondary p-6 text-white shadow-lg shadow-brand-primary/20 sm:p-8">
            <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full border-[40px] border-white/5" />
            <div className="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div className="max-w-3xl">
                    <p className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-brand-accent">
                        <Icon className="h-4 w-4" />
                        {eyebrow}
                    </p>
                    <h2 className="mt-3 text-2xl font-black leading-tight sm:text-3xl">
                        {title}
                    </h2>
                    <p className="mt-3 text-sm leading-6 text-slate-200 sm:text-base">
                        {description}
                    </p>
                </div>
                {action}
            </div>
        </section>
    );
}

export function MetricTile({ label, value, note, icon: Icon, tone = 'primary' }) {
    const tones = {
        primary: 'bg-brand-primary/10 text-brand-primary dark:text-slate-200',
        secondary: 'bg-brand-secondary/10 text-brand-secondary',
        accent: 'bg-brand-accent/15 text-brand-primary dark:text-brand-accent',
        info: 'bg-brand-info/10 text-brand-info',
        warning: 'bg-brand-warning/10 text-brand-warning',
        success: 'bg-brand-success/10 text-brand-success',
        danger: 'bg-brand-danger/10 text-brand-danger',
    };

    return (
        <Card className={`${cardClass} py-0`}>
            <CardContent className="flex items-start justify-between gap-4 p-5">
                <div className="min-w-0">
                    <p className="text-xs font-bold uppercase tracking-wider text-text-muted">
                        {label}
                    </p>
                    <p className="mt-2 break-words text-2xl font-black text-text-main">
                        {value}
                    </p>
                    {note && (
                        <p className="mt-1 text-xs leading-5 text-text-muted">{note}</p>
                    )}
                </div>
                <span
                    className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ${tones[tone]}`}
                >
                    <Icon className="h-5 w-5" />
                </span>
            </CardContent>
        </Card>
    );
}

export function FlashMessage({ message }) {
    if (!message) return null;

    return (
        <div className="mb-5 flex items-start gap-3 rounded-2xl border border-brand-success/25 bg-brand-success/10 p-4 text-sm text-brand-success">
            <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0" />
            <span>{message}</span>
        </div>
    );
}

export function Field({ label, error, className = '', ...props }) {
    return (
        <label className={`block ${className}`}>
            <span className="mb-1.5 block text-xs font-bold text-text-main">{label}</span>
            <input className={`${inputClass} ${error ? 'border-brand-danger focus:border-brand-danger focus:ring-brand-danger/15' : ''}`} {...props} />
            {error && <span className="mt-1 block text-xs text-brand-danger">{error}</span>}
        </label>
    );
}

export function SelectField({ label, error, children, className = '', ...props }) {
    return (
        <label className={`block ${className}`}>
            <span className="mb-1.5 block text-xs font-bold text-text-main">{label}</span>
            <select className={`${inputClass} ${error ? 'border-brand-danger focus:border-brand-danger focus:ring-brand-danger/15' : ''}`} {...props}>
                {children}
            </select>
            {error && <span className="mt-1 block text-xs text-brand-danger">{error}</span>}
        </label>
    );
}

export function TextareaField({ label, error, className = '', ...props }) {
    return (
        <label className={`block ${className}`}>
            <span className="mb-1.5 block text-xs font-bold text-text-main">{label}</span>
            <textarea
                className={`min-h-24 w-full rounded-xl border bg-brand-card px-3 py-2.5 text-sm text-text-main outline-none transition focus:ring-2 ${
                    error
                        ? 'border-brand-danger focus:border-brand-danger focus:ring-brand-danger/15'
                        : 'border-brand-border focus:border-brand-secondary focus:ring-brand-secondary/15'
                }`}
                {...props}
            />
            {error && <span className="mt-1 block text-xs text-brand-danger">{error}</span>}
        </label>
    );
}

export function EmptyInstitutional({ title, description }) {
    return (
        <div className={`${cardClass} flex min-h-52 items-center justify-center p-6 text-center`}>
            <div>
                <Info className="mx-auto h-8 w-8 text-brand-secondary" />
                <h3 className="mt-3 text-sm font-bold text-text-main">{title}</h3>
                <p className="mt-1 max-w-md text-xs leading-5 text-text-muted">
                    {description}
                </p>
            </div>
        </div>
    );
}
