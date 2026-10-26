import '@inertiajs/core';
import type { CellData, RowData, TableFeatures } from '@tanstack/react-table';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            sidebarOpen: boolean;
            [key: string]: unknown;
            substations: { id: number; name: string }[];
        };
        flashDataType: {
            message?: string;
        };
    }
}

declare module '@tanstack/react-table' {
    interface ColumnMeta<
        TFeatures extends TableFeatures,
        TData extends RowData,
        TValue extends CellData = CellData,
    > {
        align?: 'left' | 'right';
    }
}
