export const nextCivilDate = (date) => {
    if (!date) return '';

    const [year, month, day] = date.split('-').map(Number);
    const value = new Date(Date.UTC(year, month - 1, day));
    value.setUTCDate(value.getUTCDate() + 1);

    return value.toISOString().slice(0, 10);
};

export const clearInvalidEndDate = (start, end) =>
    start && end && end <= start ? '' : end;

export const nextMinute = (time) => {
    const match = typeof time === 'string' && /^([0-9]{2}):([0-9]{2})$/.exec(time);
    if (!match) return '';

    const total = Number(match[1]) * 60 + Number(match[2]) + 1;
    if (total >= 24 * 60) return '';

    return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
};
