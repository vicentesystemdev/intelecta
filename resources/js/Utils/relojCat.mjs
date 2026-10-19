// Ambos valores son instantes ISO con zona explícita enviados por Laravel.
// performance.now evita que la zona o los ajustes del reloj del cliente alteren el plazo.
export function crearRelojCat(venceEn, servidorAhora, monotono = () => performance.now()) {
    const conZona = value => typeof value === 'string' && /(?:Z|[+-]\d{2}:\d{2})$/i.test(value);
    const restante = conZona(venceEn) && conZona(servidorAhora)
        ? Date.parse(venceEn) - Date.parse(servidorAhora) : 0;
    const duracion = Number.isFinite(restante) ? Math.max(0, restante) : 0;
    const inicio = monotono();

    return () => Math.max(0, Math.floor((duracion - Math.max(0, monotono() - inicio)) / 1000));
}
