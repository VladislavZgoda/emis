import { createContext, useContext } from 'react';
import { tableFeatures } from '@tanstack/react-table';
import type { ColumnDef } from '@tanstack/react-table';
import { Check, Pencil, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { MeterStatRow } from './types';

export const features = tableFeatures({});

export type Field = 'meters_total' | 'meters_installed';

export type Draft = Record<Field, string>;

export type EditState = {
    editingCode: string | null;
    draft: Draft;
    processing: boolean;
    disabled: boolean;
    startEdit: (row: MeterStatRow) => void;
    change: (field: Field, value: string) => void;
    cancel: () => void;
    save: () => void;
};

export const EditContext = createContext<EditState | null>(null);

function useEdit(): EditState {
    const ctx = useContext(EditContext);

    if (!ctx) {
        throw new Error('EditContext is missing');
    }

    return ctx;
}

const LABELS: Record<Field, string> = {
    meters_total: 'Приборов на подстанции',
    meters_installed: 'Установлено за месяц',
};

function NumberCell({ row, field }: { row: MeterStatRow; field: Field }) {
    const edit = useEdit();

    if (edit.editingCode !== row.code) {
        return (
            <span className="block text-right tabular-nums">{row[field]}</span>
        );
    }

    return (
        <Input
            type="number"
            inputMode="numeric"
            min={0}
            step={1}
            autoFocus={field === 'meters_total'}
            value={edit.draft[field]}
            readOnly={edit.processing}
            aria-label={`${LABELS[field]}: ${row.balance_group}`}
            onChange={(e) => edit.change(field, e.target.value)}
            onFocus={(e) => e.currentTarget.select()}
            onKeyDown={(e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    edit.save();
                }

                if (e.key === 'Escape') {
                    e.preventDefault();
                    edit.cancel();
                }
            }}
            className="ml-auto h-8 w-24 text-right tabular-nums"
            data-testid={`input-${field}-${row.code}`}
        />
    );
}

function ActionsCell({ row }: { row: MeterStatRow }) {
    const edit = useEdit();

    if (edit.editingCode === row.code) {
        return (
            <div className="flex justify-end gap-1">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    onClick={edit.cancel}
                    disabled={edit.processing}
                    aria-label="Отменить"
                    data-testid={`cancel-${row.code}`}
                >
                    <X className="size-4 text-red-600 dark:text-red-500" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    onClick={edit.save}
                    disabled={edit.processing}
                    aria-label="Сохранить"
                    data-testid={`save-${row.code}`}
                >
                    <Check className="size-4 text-green-600 dark:text-green-500" />
                </Button>
            </div>
        );
    }

    return (
        <div className="flex justify-end">
            <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                onClick={() => edit.startEdit(row)}
                disabled={
                    edit.disabled ||
                    edit.processing ||
                    edit.editingCode !== null
                }
                aria-label={`Редактировать: ${row.balance_group}`}
                data-testid={`edit-${row.code}`}
            >
                <Pencil className="size-4" />
            </Button>
        </div>
    );
}

export const columns: ColumnDef<typeof features, MeterStatRow>[] = [
    {
        accessorKey: 'balance_group',
        header: 'Балансовая группа',
    },
    {
        accessorKey: 'meters_total',
        header: LABELS.meters_total,
        cell: ({ row }) => (
            <NumberCell row={row.original} field="meters_total" />
        ),
    },
    {
        accessorKey: 'meters_installed',
        header: LABELS.meters_installed,
        cell: ({ row }) => (
            <NumberCell row={row.original} field="meters_installed" />
        ),
    },
    {
        id: 'actions',
        header: () => <span className="sr-only">Действия</span>,
        cell: ({ row }) => <ActionsCell row={row.original} />,
    },
];
