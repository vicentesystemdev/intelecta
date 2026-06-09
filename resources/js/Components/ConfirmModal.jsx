import ModalInstitucional from '@/Components/ModalInstitucional';
import { Button } from '@/Components/ui/button';
import { AlertTriangle, CircleHelp } from 'lucide-react';

export default function ConfirmModal({
    open,
    onOpenChange,
    title,
    message,
    confirmLabel = 'Confirmar',
    cancelLabel = 'Cancelar',
    variant = 'normal',
    onConfirm,
    processing = false,
}) {
    const isDanger = variant === 'danger';

    return (
        <ModalInstitucional
            open={open}
            onOpenChange={onOpenChange}
            title={title}
            description={message}
            size="sm"
        >
            <div className="flex items-start gap-4">
                <span
                    className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ${
                        isDanger
                            ? 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                            : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                    }`}
                >
                    {isDanger ? (
                        <AlertTriangle className="h-5 w-5" />
                    ) : (
                        <CircleHelp className="h-5 w-5" />
                    )}
                </span>
                <p className="pt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Esta acción actualizará el estado institucional del registro.
                </p>
            </div>

            <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button
                    type="button"
                    variant="outline"
                    className="h-10"
                    disabled={processing}
                    onClick={() => onOpenChange(false)}
                >
                    {cancelLabel}
                </Button>
                <Button
                    type="button"
                    disabled={processing}
                    onClick={onConfirm}
                    className={`h-10 px-5 ${
                        isDanger
                            ? 'bg-rose-700 text-white hover:bg-rose-800'
                            : 'bg-indigo-700 text-white hover:bg-indigo-800'
                    }`}
                >
                    {processing ? 'Procesando...' : confirmLabel}
                </Button>
            </div>
        </ModalInstitucional>
    );
}
