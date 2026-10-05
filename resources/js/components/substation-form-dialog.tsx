import { Plus, SquarePen } from 'lucide-react';
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
import { store, update } from '@/routes/substations';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Tooltip, TooltipContent, TooltipTrigger } from './ui/tooltip';

type Substation = { id: number; name: string };

export default function SubstationFormDialog({
    substation,
    trigger,
}: {
    substation?: Substation;
    trigger?: React.ReactElement;
}) {
    const [open, setOpen] = useState(false);
    const isEditing = !!substation;

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <Tooltip>
                <TooltipTrigger
                    render={
                        <DialogTrigger
                            render={
                                trigger ?? (
                                    <Button
                                        variant="secondary"
                                        size="icon"
                                        aria-label="Добавить ТП"
                                    />
                                )
                            }
                        >
                            {isEditing ? <SquarePen /> : <Plus />}
                        </DialogTrigger>
                    }
                />
                <TooltipContent side={isEditing ? 'top' : 'right'}>
                    {isEditing ? 'Переименовать ТП' : 'Добавить ТП'}
                </TooltipContent>
            </Tooltip>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {isEditing
                            ? 'Переименовать подстанцию'
                            : 'Добавить подстанцию'}
                    </DialogTitle>
                </DialogHeader>
                <Form
                    action={isEditing ? update(substation.id) : store()}
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
                                    defaultValue={substation?.name}
                                    autoFocus
                                    maxLength={15}
                                />
                                {errors.name && (
                                    <FieldError>{errors.name}</FieldError>
                                )}
                            </Field>
                            <DialogFooter className="mt-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Сохранение...'
                                        : isEditing
                                          ? 'Сохранить'
                                          : 'Создать'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
