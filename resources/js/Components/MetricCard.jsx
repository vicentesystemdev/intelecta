import { Card, CardContent } from '@/Components/ui/card';
import { ArrowDownRight, ArrowUpRight, Minus } from 'lucide-react';

export default function MetricCard({
    title,
    value,
    detail,
    trend = 'neutral',
    icon: Icon,
    accent = 'indigo',
}) {
    const colors = {
        indigo: 'bg-indigo-50 text-indigo-600 ring-indigo-100 dark:bg-indigo-950 dark:text-indigo-300 dark:ring-indigo-900',
        blue: 'bg-blue-50 text-blue-600 ring-blue-100 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-900',
        cyan: 'bg-cyan-50 text-cyan-600 ring-cyan-100 dark:bg-cyan-950 dark:text-cyan-300 dark:ring-cyan-900',
        sky: 'bg-sky-50 text-sky-600 ring-sky-100 dark:bg-sky-950 dark:text-sky-300 dark:ring-sky-900',
        violet: 'bg-violet-50 text-violet-600 ring-violet-100 dark:bg-violet-950 dark:text-violet-300 dark:ring-violet-900',
        rose: 'bg-rose-50 text-rose-600 ring-rose-100 dark:bg-rose-950 dark:text-rose-300 dark:ring-rose-900',
    };

    const TrendIcon =
        trend === 'up' ? ArrowUpRight : trend === 'down' ? ArrowDownRight : Minus;
    const trendColor =
        trend === 'down'
            ? 'text-rose-600 dark:text-rose-400'
            : trend === 'up'
              ? 'text-emerald-600 dark:text-emerald-400'
              : 'text-slate-500 dark:text-slate-400';

    return (
        <Card className="border-0 bg-white py-0 shadow-sm ring-1 ring-slate-200/80 transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-900 dark:ring-slate-800">
            <CardContent className="p-5">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <p className="text-sm font-medium text-slate-500 dark:text-slate-400">{title}</p>
                        <p className="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            {value}
                        </p>
                    </div>
                    <span
                        className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 ${colors[accent]}`}
                    >
                        <Icon className="h-5 w-5" />
                    </span>
                </div>
                <div className={`mt-4 flex items-center gap-1.5 text-xs ${trendColor}`}>
                    <TrendIcon className="h-3.5 w-3.5" />
                    <span>{detail}</span>
                </div>
            </CardContent>
        </Card>
    );
}
