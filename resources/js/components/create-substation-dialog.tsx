import { Plus } from 'lucide-react';
import { Form, router } from '@inertiajs/react';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useState } from 'react';
import { store } from '@/routes/substations';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Tooltip, TooltipContent, TooltipTrigger } from './ui/tooltip';

export function CreateSubstationDialog() {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <Tooltip>
                <TooltipTrigger
                    render={
                        <DialogTrigger
                            render={
                                <Button
                                    variant="secondary"
                                    size="icon"
                                    aria-label="Добавить ТП"
                                />
                            }
                        >
                            <Plus />
                        </DialogTrigger>
                    }
                />
                <TooltipContent side="right">Добавить ТП</TooltipContent>
            </Tooltip>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Добавить подстанцию</DialogTitle>
                </DialogHeader>
                <Form
                    action={store()}
                    resetOnSuccess
                    onSuccess={() => {
                        setOpen(false);
                        router.reload({ only: ['substations'] });
                    }}
                >
                    {({ errors, processing }) => (
                        <>
                            <Field data-invalid={errors.name ? true : false}>
                                <FieldLabel htmlFor="name">
                                    Наименование
                                </FieldLabel>
                                <Input
                                    id="name"
                                    name="name"
                                    autoFocus
                                    maxLength={15}
                                />
                                {errors.name && (
                                    <FieldError>{errors.name}</FieldError>
                                )}
                            </Field>
                            <DialogFooter className="mt-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Создание...' : 'Создать'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
