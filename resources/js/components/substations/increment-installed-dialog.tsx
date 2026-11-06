import { useState } from 'react';
import { Form } from '@inertiajs/react';
import { store } from '@/routes/substations/meter-stats';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Field, FieldError } from '@/components/ui/field';
import { getYears, isFutureMonth, monthItems } from '@/lib/months';
import type { BalanceGroupOption } from '@/components/substations/types';
import type { Period } from '@/types';

interface Props {
    substationId: number;
    balanceGroups: BalanceGroupOption[];
    defaultPeriod: Period;
    maxPeriod: Period;
    disabled?: boolean;
}

export function IncrementInstalledDialog({
    substationId,
    balanceGroups,
    defaultPeriod,
    maxPeriod,
    disabled = false,
}: Props) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger
                render={<Button variant="secondary" disabled={disabled} />}
            >
                Установить приборы
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Установка приборов учёта</DialogTitle>
                </DialogHeader>

                <IncrementInstalledForm
                    substationId={substationId}
                    balanceGroups={balanceGroups}
                    defaultPeriod={defaultPeriod}
                    maxPeriod={maxPeriod}
                    disabled={disabled}
                    onSuccess={() => setOpen(false)}
                />
            </DialogContent>
        </Dialog>
    );
}

function IncrementInstalledForm({
    substationId,
    balanceGroups,
    defaultPeriod,
    maxPeriod,
    disabled,
    onSuccess,
}: Props & { onSuccess: () => void }) {
    const [year, setYear] = useState(defaultPeriod.year);
    const [month, setMonth] = useState(defaultPeriod.month);

    const groupItems = balanceGroups.map((group) => ({
        value: group.code,
        label: group.label,
    }));

    const changeYear = (value: string) => {
        const next = Number(value);

        setYear(next);

        if (isFutureMonth(next, month, maxPeriod)) {
            setMonth(maxPeriod.month);
        }
    };

    return (
        <Form
            action={store(substationId)}
            options={{ preserveScroll: true }}
            onSuccess={onSuccess}
            className="space-y-4"
        >
            {({ errors, processing }) => (
                <>
                    <Field data-invalid={!!errors.balance_group}>
                        <Label htmlFor="balance_group">Балансовая группа</Label>
                        <Select
                            name="balance_group"
                            defaultValue={balanceGroups[0]?.code}
                            items={groupItems}
                        >
                            <SelectTrigger
                                id="balance_group"
                                className="w-full"
                                aria-invalid={!!errors.balance_group}
                                data-testid="group-select"
                            >
                                <SelectValue placeholder="Выберите группу" />
                            </SelectTrigger>
                            <SelectContent alignItemWithTrigger={false}>
                                {groupItems.map((group) => (
                                    <SelectItem
                                        key={group.value}
                                        value={group.value}
                                    >
                                        {group.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.balance_group && (
                            <FieldError>{errors.balance_group}</FieldError>
                        )}
                    </Field>

                    <div className="grid grid-cols-2 gap-2">
                        <Field data-invalid={!!errors.month}>
                            <Label htmlFor="month">Месяц</Label>
                            <Select
                                name="month"
                                value={String(month)}
                                onValueChange={(value) =>
                                    value !== null && setMonth(Number(value))
                                }
                                items={monthItems}
                            >
                                <SelectTrigger
                                    id="month"
                                    className="w-full"
                                    aria-invalid={!!errors.month}
                                    data-testid="dialog-month-select"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent alignItemWithTrigger={false}>
                                    {monthItems.map((month) => (
                                        <SelectItem
                                            key={month.value}
                                            value={month.value}
                                            disabled={isFutureMonth(
                                                year,
                                                Number(month.value),
                                                maxPeriod,
                                            )}
                                            data-testid={`dialog-month-option-${month.value}`}
                                        >
                                            {month.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.month && (
                                <FieldError>{errors.month}</FieldError>
                            )}
                        </Field>

                        <Field data-invalid={!!errors.year}>
                            <Label htmlFor="year">Год</Label>
                            <Select
                                name="year"
                                value={String(year)}
                                onValueChange={(value) =>
                                    value !== null && changeYear(value)
                                }
                            >
                                <SelectTrigger
                                    id="year"
                                    className="w-full"
                                    aria-invalid={!!errors.year}
                                    data-testid="dialog-year-select"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent alignItemWithTrigger={false}>
                                    {getYears(maxPeriod.year).map((year) => (
                                        <SelectItem
                                            key={year}
                                            value={String(year)}
                                        >
                                            {year}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.year && (
                                <FieldError>{errors.year}</FieldError>
                            )}
                        </Field>
                    </div>

                    <Field data-invalid={!!errors.amount}>
                        <Label htmlFor="amount">
                            Количество установленных приборов
                        </Label>
                        <Input
                            id="amount"
                            name="amount"
                            type="number"
                            inputMode="numeric"
                            min={1}
                            defaultValue={1}
                            aria-invalid={!!errors.amount}
                        />
                        {errors.amount && (
                            <FieldError>{errors.amount}</FieldError>
                        )}
                    </Field>

                    <DialogFooter>
                        <Button
                            type="submit"
                            disabled={processing || disabled}
                            data-testid="dialog-submit"
                        >
                            {processing ? 'Сохранение...' : 'Применить'}
                        </Button>
                    </DialogFooter>
                </>
            )}
        </Form>
    );
}
