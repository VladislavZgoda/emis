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
}: {
    substationId: number;
    filters: Period;
}) {
    const update = (key: keyof Period, value: string) => {
        router.get(
            show(substationId),
            { ...filters, [key]: Number(value) },
            { preserveState: true, preserveScroll: true, replace: true },
        );
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
            >
                <SelectTrigger className="w-40">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent alignItemWithTrigger={false} side="bottom">
                    {monthItems.map((m) => (
                        <SelectItem key={m.value} value={m.value}>
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
            >
                <SelectTrigger className="w-28">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent
                    alignItemWithTrigger={false}
                    side="bottom"
                    className="min-w-0"
                >
                    {[filters.year - 1, filters.year, filters.year + 1].map(
                        (y) => (
                            <SelectItem key={y} value={String(y)}>
                                {y}
                            </SelectItem>
                        ),
                    )}
                </SelectContent>
            </Select>
        </div>
    );
}
