import useTheme from '@/Hooks/useTheme';
import { Moon, Sun } from 'lucide-react';

/**
 * ThemeToggle
 *
 * Stateless button that calls `onToggle` when clicked.
 * The `isDark` prop drives the displayed icon and aria-label.
 */
export default function ThemeToggle({
    isDark: controlledIsDark,
    onToggle,
    className = '',
}) {
    const theme = useTheme();
    const isDark = controlledIsDark ?? theme.isDark;
    const handleToggle = onToggle ?? theme.toggleTheme;

    return (
        <button
            type="button"
            onClick={handleToggle}
            aria-label="Cambiar tema"
            title={isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'}
            className={`inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-200 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-amber-400 dark:hover:bg-slate-700 dark:hover:text-amber-300 ${className}`}
        >
            {isDark ? (
                <Sun className="h-4 w-4" aria-hidden="true" />
            ) : (
                <Moon className="h-4 w-4" aria-hidden="true" />
            )}
        </button>
    );
}
