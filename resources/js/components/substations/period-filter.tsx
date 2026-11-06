import { router } from '@inertiajs/react';
import { show } from '@/routes/substations';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { getYears, isFutureMonth, monthItems } from '@/lib/months';
import type { Period } from '@/types';

interface Props {
    substationId: number;
    filters: Period;
    isLoading?: boolean;
    maxPeriod: Period;
    onLoadingChange?: (loading: boolean) => void;
}

export function PeriodFilter({
    substationId,
    filters,
    isLoading = false,
    maxPeriod,
    onLoadingChange,
}: Props) {
    const update = (key: keyof Period, value: string) => {
        const nextPeriod = { ...filters, [key]: Number(value) };

        if (isFutureMonth(nextPeriod.year, nextPeriod.month, maxPeriod)) {
            nextPeriod.month = maxPeriod.month;
        }

        router.get(show(substationId), nextPeriod, {
            only: ['meterStats', 'totals', 'filters'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => onLoadingChange?.(true),
            onFinish: () => onLoadingChange?.(false),
        });
    };

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
                            disabled={isFutureMonth(
                                filters.year,
                                Number(m.value),
                                maxPeriod,
                            )}
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
                    {getYears(maxPeriod.year).map((y) => (
                        <SelectItem key={y} value={String(y)}>
                            {y}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
