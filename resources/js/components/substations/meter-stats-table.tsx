import { cn } from '@/lib/utils';
import { tableFeatures, useTable } from '@tanstack/react-table';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { columns } from './meter-stats-columns';
import type { MeterStatRow, Totals } from './types';

const features = tableFeatures({});

export function MeterStatsTable({
    data,
    totals,
    isLoading = false,
}: {
    data: MeterStatRow[];
    totals: Totals;
    isLoading?: boolean;
}) {
    const table = useTable({
        features,
        columns,
        data,
    });

    const alignClass = (align?: 'left' | 'right') =>
        align === 'right' ? 'text-right tabular-nums' : undefined;

    return (
        <div
            className={cn(
                'rounded-md border transition-opacity',
                isLoading && 'opacity-50',
            )}
        >
            <Table>
                <TableHeader>
                    {table.getHeaderGroups().map((hg) => (
                        <TableRow key={hg.id}>
                            {hg.headers.map((h) => (
                                <TableHead
                                    key={h.id}
                                    className={alignClass(
                                        h.column.columnDef.meta?.align,
                                    )}
                                >
                                    <table.FlexRender header={h} />
                                </TableHead>
                            ))}
                        </TableRow>
                    ))}
                </TableHeader>
                <TableBody>
                    {table.getRowModel().rows.map((row) => (
                        <TableRow key={row.id}>
                            {row.getAllCells().map((cell) => (
                                <TableCell
                                    key={cell.id}
                                    className={alignClass(
                                        cell.column.columnDef.meta?.align,
                                    )}
                                >
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
                    </TableRow>
                </TableFooter>
            </Table>
        </div>
    );
}
