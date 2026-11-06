export type MeterStatRow = {
    balance_group: string;
    meters_total: number;
    meters_installed: number;
};

export type Totals = { meters_total: number; meters_installed: number };

export interface BalanceGroupOption {
    code: string;
    label: string;
}
