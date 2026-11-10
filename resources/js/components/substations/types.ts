export interface MeterStatRow {
    code: string;
    balance_group: string;
    meters_total: number;
    meters_installed: number;
}

export interface Totals {
    meters_total: number;
    meters_installed: number;
}

export interface BalanceGroupOption {
    code: string;
    label: string;
}
