import InputError from '@/Components/InputError';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { CheckCircle2 } from 'lucide-react';

export default function AlternativasEditor({
    alternativas,
    onChange,
    tipo,
    errors = {},
}) {
    if (tipo === 'respuesta_corta') {
        return (
            <div className="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
                Las respuestas cortas no requieren alternativas. Su corrección se
                configurará al momento de aplicar la evaluación.
            </div>
        );
    }

    const update = (index, field, value) => {
        onChange(
            alternativas.map((alternativa, current) => ({
                ...alternativa,
                ...(current === index ? { [field]: value } : {}),
                ...(field === 'es_correcta_alt' && value && current !== index
                    ? { es_correcta_alt: false }
                    : {}),
            })),
        );
    };

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <div>
                    <Label>Alternativas de respuesta</Label>
                    <p className="mt-1 text-xs text-slate-500">
                        Seleccione una única alternativa correcta.
                    </p>
                </div>
                <span className="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    {alternativas.length} alternativas
                </span>
            </div>

            {alternativas.map((alternativa, index) => (
                <div
                    key={alternativa.letra_alt}
                    className={`grid gap-3 rounded-xl border p-3 sm:grid-cols-[42px_1fr_150px] sm:items-center ${
                        alternativa.es_correcta_alt
                            ? 'border-emerald-300 bg-emerald-50/60'
                            : 'border-slate-200 bg-white'
                    }`}
                >
                    <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 font-bold text-indigo-700">
                        {alternativa.letra_alt}
                    </span>
                    <div>
                        <Input
                            value={alternativa.texto_alt}
                            onChange={(event) =>
                                update(index, 'texto_alt', event.target.value)
                            }
                            aria-label={`Alternativa ${alternativa.letra_alt}`}
                        />
                        <InputError
                            className="mt-1"
                            message={errors[`alternativas.${index}.texto_alt`]}
                        />
                    </div>
                    <label className="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700">
                        <input
                            type="radio"
                            name="alternativa_correcta"
                            checked={Boolean(alternativa.es_correcta_alt)}
                            onChange={() =>
                                update(index, 'es_correcta_alt', true)
                            }
                            className="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                        Respuesta correcta
                    </label>
                </div>
            ))}
            <InputError message={errors.alternativas} />
        </div>
    );
}
