// Calendar components only: no Date constructor, UTC conversion or locale-dependent display.
function validDate(year, month, day) {
    const leap = year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0);
    const days = [31, leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    return year >= 1 && year <= 9999 && month >= 1 && month <= 12 && day >= 1 && day <= days[month - 1];
}

export function formatDateLatam(value) {
    const parts = typeof value === 'string' && /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(value);
    if (!parts || !validDate(+parts[1], +parts[2], +parts[3])) return '';
    return `${parts[3]}/${parts[2]}/${parts[1]}`;
}

export function parseDateLatam(value) {
    const parts = typeof value === 'string' && /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/.exec(value);
    if (!parts || !validDate(+parts[3], +parts[2], +parts[1])) return null;
    return `${parts[3]}-${parts[2]}-${parts[1]}`;
}
