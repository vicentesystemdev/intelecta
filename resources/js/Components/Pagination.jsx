import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    return (
        <nav className="mt-6 flex flex-wrap items-center justify-center gap-1.5" aria-label="Navegación de páginas">
            {links.map((link, index) => {
                const isPrevious = 
                    link.label.includes('previous') || 
                    link.label.includes('Previous') || 
                    link.label.includes('&laquo;') || 
                    link.label.includes('laquo');
                
                const isNext = 
                    link.label.includes('next') || 
                    link.label.includes('Next') || 
                    link.label.includes('&raquo;') || 
                    link.label.includes('raquo');

                const ariaLabel = isPrevious 
                    ? 'Página anterior' 
                    : isNext 
                    ? 'Página siguiente' 
                    : `Página ${link.label}`;

                const baseClass = "inline-flex h-9 min-w-9 items-center justify-center rounded-lg border text-sm font-semibold transition-colors duration-200";
                
                const activeClass = link.active
                    ? "bg-indigo-700 border-indigo-700 text-white dark:bg-indigo-600 dark:border-indigo-600"
                    : "bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800";
                
                const disabledClass = !link.url
                    ? "opacity-40 cursor-not-allowed"
                    : "";

                const buttonClass = `${baseClass} ${activeClass} ${disabledClass}`;

                const content = isPrevious ? (
                    <ChevronLeft className="h-4.5 w-4.5" />
                ) : isNext ? (
                    <ChevronRight className="h-4.5 w-4.5" />
                ) : (
                    <span dangerouslySetInnerHTML={{ __html: link.label }} />
                );

                if (!link.url) {
                    return (
                        <span
                            key={`pagination-disabled-${index}`}
                            className={buttonClass}
                            aria-disabled="true"
                            aria-label={ariaLabel}
                        >
                            {content}
                        </span>
                    );
                }

                return (
                    <Link
                        key={`pagination-link-${index}`}
                        href={link.url}
                        preserveScroll
                        className={buttonClass}
                        aria-label={ariaLabel}
                    >
                        {content}
                    </Link>
                );
            })}
        </nav>
    );
}
