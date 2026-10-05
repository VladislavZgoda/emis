import { SquareX, Trash2Icon } from 'lucide-react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogMedia,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { router } from '@inertiajs/react';
import { destroy } from '@/routes/substations';
import { SidebarMenuAction } from './ui/sidebar';
import { Tooltip, TooltipContent, TooltipTrigger } from './ui/tooltip';

export default function SubstationDeleteDialog({ id }: { id: number }) {
    return (
        <AlertDialog>
            <Tooltip>
                <TooltipTrigger
                    render={
                        <AlertDialogTrigger
                            render={
                                <SidebarMenuAction aria-label="Удалить ТП" />
                            }
                        >
                            <SquareX />
                        </AlertDialogTrigger>
                    }
                />
                <TooltipContent side="right">Удалить ТП</TooltipContent>
            </Tooltip>
            <AlertDialogContent size="sm">
                <AlertDialogHeader>
                    <AlertDialogMedia className="bg-destructive/10 text-destructive dark:bg-destructive/20 dark:text-destructive">
                        <Trash2Icon />
                    </AlertDialogMedia>
                    <AlertDialogTitle>Удалить подстанцию?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Это действие навсегда удалит данную подстанцию.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel variant="outline">
                        Отмена
                    </AlertDialogCancel>
                    <AlertDialogAction
                        variant="destructive"
                        onClick={() =>
                            router.delete(destroy(id), {
                                onSuccess: () =>
                                    router.reload({ only: ['substations'] }),
                            })
                        }
                    >
                        Удалить
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
