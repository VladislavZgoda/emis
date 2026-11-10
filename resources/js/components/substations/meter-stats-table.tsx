import { useState } from 'react';
import { useTable } from '@tanstack/react-table';
import { router } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';
import { update } from '@/routes/substations/meter-stats';
import {
    columns,
    EditContext,
    features,
    type Draft,
    type EditState,
    type Field,
} from './meter-stats-columns';
import type { MeterStatRow, Totals } from './types';

type Props = {
    substationId: number;
    year: number;
    month: number;
    data: MeterStatRow[];
    totals: Totals;
    isLoading?: boolean;
};

const EMPTY_DRAFT: Draft = { meters_total: '', meters_installed: '' };

const parseCount = (text: string): number | null => {
    const trimmed = text.trim();
    const value = Number(trimmed);

    return trimmed !== '' && Number.isInteger(value) && value >= 0
        ? value
        : null;
};

export function MeterStatsTable({
    substationId,
    year,
    month,
    data,
    totals,
    isLoading = false,
}: Props) {
    const [editingCode, setEditingCode] = useState<string | null>(null);
    const [draft, setDraft] = useState<Draft>(EMPTY_DRAFT);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const periodKey = `${substationId}:${year}:${month}`;
    const [prevPeriodKey, setPrevPeriodKey] = useState(periodKey);

    if (prevPeriodKey !== periodKey) {
        setPrevPeriodKey(periodKey);
        setEditingCode(null);
        setError(null);
    }

    const close = () => {
        setEditingCode(null);
        setError(null);
    };

    const startEdit = (row: MeterStatRow) => {
        setDraft({
            meters_total: String(row.meters_total),
            meters_installed: String(row.meters_installed),
        });
        setError(null);
        setEditingCode(row.code);
    };

    const change = (field: Field, value: string) => {
        setDraft((d) => ({ ...d, [field]: value }));
    };

    const save = () => {
        if (processing || editingCode === null) {
            return;
        }

        const metersTotal = parseCount(draft.meters_total);
        const metersInstalled = parseCount(draft.meters_installed);

        if (metersTotal === null || metersInstalled === null) {
            setError('Введите целые неотрицательные числа.');

            return;
        }

        if (metersInstalled > metersTotal) {
            setError('Установлено не может быть больше общего количества.');

            return;
        }

        const current = data.find((r) => r.code === editingCode);

        if (
            current &&
            current.meters_total === metersTotal &&
            current.meters_installed === metersInstalled
        ) {
            close();

            return;
        }

        setProcessing(true);
        setError(null);

        router.put(
            update(substationId).url,
            {
                balance_group: editingCode,
                year,
                month,
                meters_total: metersTotal,
                meters_installed: metersInstalled,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: close,
                onError: (errs) =>
                    setError(
                        errs.meters_total ??
                            errs.meters_installed ??
                            errs.month ??
                            errs.year ??
                            errs.balance_group ??
                            'Не удалось сохранить изменения.',
                    ),
                onFinish: () => setProcessing(false),
            },
        );
    };

    const editState: EditState = {
        editingCode,
        draft,
        processing,
        disabled: isLoading,
        startEdit,
        change,
        cancel: close,
        save,
    };

    const table = useTable({ features, columns, data });

    return (
        <EditContext.Provider value={editState}>
            <div className="space-y-2">
                <div
                    className={cn(
                        'rounded-md border transition-opacity',
                        isLoading && 'opacity-50',
                    )}
                    aria-busy={isLoading}
                >
                    <Table>
                        <TableHeader>
                            {table.getHeaderGroups().map((hg) => (
                                <TableRow key={hg.id}>
                                    {hg.headers.map((h) => (
                                        <TableHead
                                            key={h.id}
                                            className={
                                                h.column.id === 'balance_group'
                                                    ? undefined
                                                    : 'text-right'
                                            }
                                        >
                                            <table.FlexRender header={h} />
                                        </TableHead>
                                    ))}
                                </TableRow>
                            ))}
                        </TableHeader>
                        <TableBody>
                            {table.getRowModel().rows.map((row) => (
                                <TableRow
                                    key={row.id}
                                    data-editing={
                                        editingCode === row.original.code ||
                                        undefined
                                    }
                                    className="data-editing:bg-muted/30"
                                >
                                    {row.getAllCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            <table.FlexRender cell={cell} />
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))}
                        </TableBody>
                        <TableFooter>
                            <TableRow className="bg-muted/50 font-semibold">
                                <TableCell>Итого</TableCell>
                                <TableCell className="text-right tabular-nums">
                                    {totals.meters_total}
                                </TableCell>
                                <TableCell className="text-right tabular-nums">
                                    {totals.meters_installed}
                                </TableCell>
                                <TableCell />
                            </TableRow>
                        </TableFooter>
                    </Table>
                </div>

                {error && (
                    <p
                        className="text-destructive text-sm"
                        role="alert"
                        data-testid="edit-error"
                    >
                        {error}
                    </p>
                )}
            </div>
        </EditContext.Provider>
    );
}
