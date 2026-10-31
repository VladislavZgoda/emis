import { router } from '@inertiajs/react';
import { show } from '@/routes/substations';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Period } from '@/types';

const months = [
    'Январь',
    'Февраль',
    'Март',
    'Апрель',
    'Май',
    'Июнь',
    'Июль',
    'Август',
    'Сентябрь',
    'Октябрь',
    'Ноябрь',
    'Декабрь',
];

const monthItems = months.map((label, i) => ({
    value: String(i + 1),
    label,
}));

export function PeriodFilter({
    substationId,
    filters,
    isLoading = false,
    onLoadingChange,
}: {
    substationId: number;
    filters: Period;
    isLoading?: boolean;
    onLoadingChange?: (loading: boolean) => void;
}) {
    const update = (key: keyof Period, value: string) => {
        router.get(
            show(substationId),
            { ...filters, [key]: Number(value) },
            {
                only: ['meterStats', 'totals', 'filters'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => onLoadingChange?.(true),
                onFinish: () => onLoadingChange?.(false),
            },
        );
    };

    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 3 }, (_, i) => currentYear - i);

    return (
        <div className="flex gap-2">
            <Select
                value={String(filters.month)}
                onValueChange={(v) => {
                    if (v === null) return;
                    update('month', v);
                }}
                items={monthItems}
                disabled={isLoading}
            >
                <SelectTrigger className="w-40" data-testid="month-select">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent alignItemWithTrigger={false} side="bottom">
                    {monthItems.map((m) => (
                        <SelectItem
                            key={m.value}
                            value={m.value}
                            data-testid={`month-option-${m.value}`}
                        >
                            {m.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <Select
                value={String(filters.year)}
                onValueChange={(v) => {
                    if (v === null) return;
                    update('year', v);
                }}
                disabled={isLoading}
            >
                <SelectTrigger className="w-28" data-testid="year-select">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent
                    alignItemWithTrigger={false}
                    side="bottom"
                    className="min-w-0"
                >
                    {years.map((y) => (
                        <SelectItem key={y} value={String(y)}>
                            {y}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
