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
}: {
    data: MeterStatRow[];
    totals: Totals;
}) {
    const table = useTable({
        features,
        columns,
        data,
    });

    return (
        <div className="rounded-md border">
            <Table>
                <TableHeader>
                    {table.getHeaderGroups().map((hg) => (
                        <TableRow key={hg.id}>
                            {hg.headers.map((h) => (
                                <TableHead key={h.id}>
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
                        <TableCell>{totals.meters_total}</TableCell>
                        <TableCell>{totals.meters_installed}</TableCell>
                    </TableRow>
                </TableFooter>
            </Table>
        </div>
    );
}
