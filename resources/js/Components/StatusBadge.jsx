import { Badge } from '@/Components/ui/badge';

export default function StatusBadge({ status }) {
    const styles = {
        Activa: 'border-blue-200 bg-blue-50 text-blue-700',
        Cerrada: 'border-slate-200 bg-slate-100 text-slate-700',
        Borrador: 'border-amber-200 bg-amber-50 text-amber-700',
        Riesgo: 'border-rose-200 bg-rose-50 text-rose-700',
        Prioritario: 'border-rose-200 bg-rose-50 text-rose-700',
        Estable: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        Activo: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        Inactivo: 'border-slate-200 bg-slate-100 text-slate-600',
    };

    return (
        <Badge variant="outline" className={styles[status] || styles.Cerrada}>
            {status}
        </Badge>
    );
}
