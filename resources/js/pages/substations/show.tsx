import { Head } from '@inertiajs/react';
import { PeriodFilter } from '@/components/substations/period-filter';
import { MeterStatsTable } from '@/components/substations/meter-stats-table';
import type { MeterStatRow, Totals } from '@/components/substations/types';
import type { Period } from '@/types';

type Props = {
    substation: { id: number; name: string };
    meterStats: MeterStatRow[];
    totals: Totals;
    filters: Period;
};

export default function Show({
    substation,
    meterStats,
    totals,
    filters,
}: Props) {
    return (
        <>
            <Head title={substation.name} />
            <div className="space-y-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">{substation.name}</h1>
                    <PeriodFilter
                        substationId={substation.id}
                        filters={filters}
                    />
                </div>
                <MeterStatsTable data={meterStats} totals={totals} />
            </div>
        </>
    );
}
