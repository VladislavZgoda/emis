import { Head } from '@inertiajs/react';
import { PeriodFilter } from '@/components/substations/period-filter';
import { MeterStatsTable } from '@/components/substations/meter-stats-table';
import type { MeterStatRow, Totals } from '@/components/substations/types';
import type { Period } from '@/types';
import { useEffect, useState } from 'react';

interface Props {
    substation: { id: number; name: string };
    meterStats: MeterStatRow[];
    totals: Totals;
    filters: Period;
    limits: Period;
}

export default function Show({
    substation,
    meterStats,
    totals,
    filters,
    limits,
}: Props) {
    const [isLoading, setIsLoading] = useState(false);
    const [showDim, setShowDim] = useState(false);

    useEffect(() => {
        if (!isLoading) {
            setShowDim(false);
            return;
        }

        const t = setTimeout(() => setShowDim(true), 150);

        return () => clearTimeout(t);
    }, [isLoading]);

    return (
        <>
            <Head title={substation.name} />
            <div className="space-y-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">{substation.name}</h1>
                    <PeriodFilter
                        substationId={substation.id}
                        filters={filters}
                        maxPeriod={limits}
                        isLoading={isLoading}
                        onLoadingChange={setIsLoading}
                    />
                </div>
                <MeterStatsTable
                    data={meterStats}
                    totals={totals}
                    isLoading={showDim}
                />
            </div>
        </>
    );
}
