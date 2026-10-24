import { createColumnHelper, tableFeatures } from '@tanstack/react-table';
import type { MeterStatRow } from './types';

const features = tableFeatures({});
const columnHelper = createColumnHelper<typeof features, MeterStatRow>();

export const columns = columnHelper.columns([
    columnHelper.accessor('balance_group', {
        header: 'Балансовая группа',
    }),
    columnHelper.accessor('meters_total', {
        header: 'Приборов на подстанции',
    }),
    columnHelper.accessor('meters_installed', {
        header: 'Установлено за месяц',
    }),
]);
