import { Sun, Moon } from 'lucide-react';

/**
 * ThemeToggle
 *
 * Stateless button that calls `onToggle` when clicked.
 * The `isDark` prop drives the displayed icon and aria-label.
 */
export default function ThemeToggle({ isDark, onToggle }) {
    return (
        <button
            type="button"
            onClick={onToggle}
            aria-label="Cambiar tema"
            title={isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'}
            className={`
                inline-flex h-9 w-9 items-center justify-center rounded-xl
                border transition duration-200
                ${isDark
                    ? 'border-slate-700 bg-slate-800 text-amber-400 hover:bg-slate-700 hover:text-amber-300'
                    : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-900'}
            `}
        >
            {isDark
                ? <Sun  className="h-4 w-4" aria-hidden="true" />
                : <Moon className="h-4 w-4" aria-hidden="true" />
            }
        </button>
    );
}
