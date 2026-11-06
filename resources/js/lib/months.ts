import type { Period } from '@/types';

const capitalize = (s: string) =>
    s.charAt(0).toLocaleUpperCase('ru') + s.slice(1);

const formatter = new Intl.DateTimeFormat('ru', { month: 'long' });

export const monthItems = Array.from({ length: 12 }, (_, i) => ({
    value: String(i + 1),
    label: capitalize(formatter.format(new Date(2000, i, 1))),
}));

/** Список годов: от максимального вниз. */
export const getYears = (maxYear: number, count = 3): number[] =>
    Array.from({ length: count }, (_, i) => maxYear - i);

export const isFutureMonth = (
    year: number,
    month: number,
    max: Period,
): boolean => year === max.year && month > max.month;
