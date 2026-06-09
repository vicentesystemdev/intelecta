import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { cn } from '@/lib/utils';

/**
 * ESTÁNDAR DE CRUDs INSTITUCIONALES EN INTELECTA:
 * 
 * Para mantener una experiencia de usuario premium, homogénea y consistente:
 * 1. Listado Principal: Se presenta en una página completa (Index.jsx) estructurada.
 * 2. Crear Registro: Se abre en una ventana modal utilizando `ModalInstitucional`.
 * 3. Editar Registro: Se abre en una ventana modal utilizando `ModalInstitucional`.
 * 4. Ver Detalle: Se abre en una ventana modal utilizando `ModalInstitucional`.
 * 5. Confirmación de Acciones (Activar/Inactivar/Eliminar): Se realiza mediante `ConfirmModal`.
 * 6. Formularios Largos: Deben usar `size="xl"` o `size="full"` y la prop `scrollable={true}` 
 *    para habilitar el scroll interno de contenido, asegurando que el Header y el Footer 
 *    permanezcan estáticos (sticky) en la pantalla.
 */

const sizeClasses = {
    sm: 'sm:max-w-md',
    md: 'sm:max-w-xl',
    lg: 'sm:max-w-3xl',
    xl: 'sm:max-w-5xl',
    full: 'sm:max-w-[95vw] sm:w-[95vw]',
};

export default function ModalInstitucional({
    open,
    onOpenChange,
    title,
    description,
    children,
    footer = null,
    size = 'md',
    scrollable = true,
    className,
}) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                className={cn(
                    'max-h-[90vh] w-[95vw] sm:w-full gap-0 overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl ring-0 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 flex flex-col',
                    sizeClasses[size] || sizeClasses.md,
                    className,
                )}
            >
                <DialogHeader className="shrink-0 border-b border-slate-200 px-6 py-5 pr-14 text-left dark:border-slate-800">
                    <DialogTitle className="text-lg font-bold text-slate-900 dark:text-slate-100">
                        {title}
                    </DialogTitle>
                    {description && (
                        <DialogDescription className="leading-5 text-slate-500 dark:text-slate-400">
                            {description}
                        </DialogDescription>
                    )}
                </DialogHeader>

                <div 
                    className={cn(
                        'px-6 py-5 flex-1 min-h-0',
                        scrollable ? 'overflow-y-auto' : 'overflow-hidden'
                    )}
                >
                    {children}
                </div>

                {footer && (
                    <div className="shrink-0 border-t border-slate-200 px-6 py-4 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        {footer}
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}

